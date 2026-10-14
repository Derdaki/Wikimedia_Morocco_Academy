<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
use App\Core\Database;
use App\Core\Env;
use App\Models\User;
use App\Models\Event;
use App\Models\Content;

if (!str_ends_with((string) Env::get('DB_DATABASE'), '_test')) {
    fwrite(STDERR, "Refusing to run: DB_DATABASE must end in _test. Use a dedicated migrated test database.\n");
    exit(1);
}
$base = rtrim((string) (getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8080'), '/');
$pdo = Database::pdo();
$settingsSnapshot = \App\Models\Setting::all();
$prefix = 'verify-' . bin2hex(random_bytes(4));
$password = bin2hex(random_bytes(16));
$adminId = User::create('Verification admin', $prefix . '@example.test', $password, 'super_admin');
$editorId = User::create('Verification editor', $prefix . '-editor@example.test', $password, 'editor');
$jars = [];
$uploaded = [];
$passed = 0;
$failed = 0;

function request(string $path, ?array $data = null, string $client = 'public'): array {
    global $base, $jars;
    $jars[$client] ??= tempnam(sys_get_temp_dir(), 'wmma-cookie-');
    $ch = curl_init($base . $path);
    $headers = [];
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 20, CURLOPT_COOKIEFILE => $jars[$client], CURLOPT_COOKIEJAR => $jars[$client],
        CURLOPT_HEADERFUNCTION => static function ($ch, $line) use (&$headers) {
            if (str_contains($line, ':')) { [$key, $value] = explode(':', $line, 2); $headers[strtolower($key)] = trim($value); }
            return strlen($line);
        }]);
    if ($data !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, isset($data['file']) || isset($data['cover_upload']) ? $data : http_build_query($data)); }
    $body = curl_exec($ch);
    if ($body === false) throw new RuntimeException(curl_error($ch));
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => $body, 'headers' => $headers];
}
function token(string $path, string $client = 'admin'): string {
    $response = request($path, null, $client);
    if (!preg_match('/name="_token" value="([a-f0-9]+)"/', $response['body'], $match)) throw new RuntimeException('Missing CSRF token on ' . $path . ' (HTTP ' . $response['status'] . ')');
    return $match[1];
}
function check(bool $condition, string $name): void {
    global $passed, $failed;
    $condition ? $passed++ : $failed++;
    echo ($condition ? 'PASS ' : 'FAIL ') . $name . "\n";
}
function countSlug(string $table, string $slug): int {
    $stmt = Database::pdo()->prepare("SELECT COUNT(*) FROM {$table} WHERE slug = ?");
    $stmt->execute([$slug]); return (int) $stmt->fetchColumn();
}
try {
    check(!(bool) $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES), 'Native PDO prepared statements');
    foreach (['/', '/evenements', '/nos-projets', '/actualites', '/a-propos', '/notre-equipe', '/ressources', '/contact', '/rejoindre-la-communaute', '/sitemap.xml', '/robots.txt', '/assets/css/app.css', '/assets/js/app.js'] as $path) {
        check(request($path)['status'] === 200, 'Public route ' . $path);
    }
    check(request('/unknown-page')['status'] === 404, 'Unknown route returns 404');
    check(request('/sitemapXxml')['status'] === 404, 'Literal route punctuation');
    check(request('/admin/events/1/delete')['status'] === 405, 'Delete route rejects GET');
    check(request('/evenements?q%5B%5D=x')['status'] === 400, 'Malformed query input rejected');
    check(request('/admin/events', null, 'anonymous')['status'] === 302, 'Anonymous admin route protected');
    check(request('/admin/login', ['email' => $prefix . '@example.test', 'password' => $password])['status'] === 403, 'Missing CSRF rejected');
    $login = request('/admin/login', ['_token' => token('/admin/login'), 'email' => $prefix . '@example.test', 'password' => 'invalid'], 'admin');
    check($login['status'] === 302 && str_contains(request('/admin/login', null, 'admin')['body'], 'invalides'), 'Invalid login rejected');
    $login = request('/admin/login', ['_token' => token('/admin/login'), 'email' => $prefix . '@example.test', 'password' => $password], 'admin');
    check($login['status'] === 302 && request('/admin', null, 'admin')['status'] === 200, 'Administrator login and dashboard');
    check(str_contains(strtolower($login['headers']['set-cookie'] ?? ''), 'httponly') && str_contains(strtolower($login['headers']['set-cookie'] ?? ''), 'samesite=lax'), 'Session cookie flags');
    foreach (['events', 'projects', 'news', 'team', 'resources', 'media', 'contact-messages', 'users', 'settings'] as $section) {
        check(request('/admin/' . $section, null, 'admin')['status'] === 200, 'Admin route ' . $section);
    }
    $csrf = token('/admin/events/create');
    $event = ['_token' => $csrf, 'title_fr' => '<script>alert(1)</script> ' . $prefix, 'title_ar' => 'فعالية اختبار', 'slug' => $prefix,
        'short_description_fr' => 'Test résumé', 'description_fr' => 'Test description', 'start_at' => date('Y-m-d', strtotime('+1 day')) . 'T10:00', 'end_at' => date('Y-m-d', strtotime('+1 day')) . 'T13:00',
        'location' => 'Rabat', 'organizer' => 'Verification', 'category_id' => '1', 'status' => 'draft'];
    request('/admin/events', $event, 'admin');
    $stmt = $pdo->prepare('SELECT id FROM events WHERE slug = ?'); $stmt->execute([$prefix]); $eventId = (int) $stmt->fetchColumn();
    check($eventId > 0 && request('/evenements/' . $prefix)['status'] === 404, 'Event create and draft privacy');
    $event['status'] = 'published'; request('/admin/events/' . $eventId, $event, 'admin');
    $detail = request('/evenements/' . $prefix);
    check($detail['status'] === 200 && str_contains($detail['body'], '&lt;script&gt;') && !str_contains($detail['body'], '<script>alert'), 'Event update, related query and XSS escaping');
    check(str_contains(request('/')['body'], $prefix), 'Published event appears on homepage');
    check(str_contains(request('/evenements?q=' . $prefix . '&category_id=1&location=Rabat&period=upcoming')['body'], '/evenements/' . $prefix . '"'), 'Event search and combined filters');
    check(request('/evenements?q=' . urlencode("' OR 1=1 --") . '&period=')['status'] === 200, 'SQL injection search payload safely handled');
    $invalid = $event; $invalid['slug'] .= '-invalid'; $invalid['end_at'] = '2020-01-01T00:00';
    request('/admin/events', $invalid, 'admin');
    check(countSlug('events', $invalid['slug']) === 0 && str_contains(request('/admin/events/create', null, 'admin')['body'], 'fin'), 'Invalid event date range rejected with feedback');
    $invalid['end_at'] = $event['end_at']; $invalid['registration_url'] = 'javascript:alert(1)'; request('/admin/events', $invalid, 'admin');
    check(countSlug('events', $invalid['slug']) === 0, 'Unsafe registration URL rejected');
    for ($i = 0; $i < 14; $i++) { $row = $event; unset($row['_token']); $row['slug'] = $prefix . '-page-' . $i; Event::save($row); }
    check(str_contains(request('/admin/events?q=' . $prefix, null, 'admin')['body'], 'page=2'), 'Event admin pagination rendered');
    check(str_contains(request('/evenements?q=' . $prefix . '&page=2')['body'], 'class="pagination"'), 'Public event pagination');
    request('/admin/events/' . $eventId . '/archive', ['_token' => $csrf], 'admin');
    check(request('/evenements/' . $prefix)['status'] === 404 && str_contains(request('/admin/events?status=archived&q=' . $prefix, null, 'admin')['body'], $prefix), 'Event archive hidden publicly and visible in admin filter');
    request('/admin/events/' . $eventId . '/delete', ['_token' => $csrf], 'admin'); check(countSlug('events', $prefix) === 0, 'Event delete');

    foreach (['projects' => '/nos-projets/', 'news' => '/actualites/', 'team' => '/notre-equipe', 'resources' => '/ressources'] as $type => $publicPath) {
        $data = ['_token' => token('/admin/' . $type . '/create'), 'title_fr' => $prefix . ' ' . $type, 'title_ar' => 'محتوى اختبار', 'name' => $prefix,
            'slug' => $prefix . '-' . $type, 'role_fr' => 'Coordination', 'status' => 'draft', 'category_id' => in_array($type, ['projects', 'news'], true) ? '1' : '', 'project_status' => 'active', 'sort_order' => '-100',
            'is_featured' => '1', 'resource_type' => 'link', 'file_url' => 'https://example.org/resource', 'published_at' => '2020-01-01T10:00'];
        request('/admin/' . $type, $data, 'admin');
        $table = $type === 'team' ? 'team_members' : $type;
        $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE slug = ?"); $stmt->execute([$data['slug']]); $id = (int) $stmt->fetchColumn();
        check($id > 0, $type . ' create');
        $publicUrl = $publicPath . (in_array($type, ['projects', 'news'], true) ? $data['slug'] : '');
        $privateResponse = request($publicUrl);
        check(in_array($type, ['projects', 'news'], true) ? $privateResponse['status'] === 404 : !str_contains($privateResponse['body'], $prefix), $type . ' draft or hidden privacy');
        $data['status'] = 'published'; $data['is_visible'] = '1'; request('/admin/' . $type . '/' . $id, $data, 'admin');
        check(str_contains(request($publicUrl)['body'], $prefix), $type . ' update appears publicly');
        if ($type === 'projects') check(str_contains(request('/')['body'], $prefix), 'Featured project appears on homepage');
        check(request('/admin/' . $type . '?q=' . $prefix, null, 'admin')['status'] === 200, $type . ' management search');
        if (in_array($type, ['projects','news'], true)) check(str_contains(request(rtrim($publicPath, '/') . '?q=' . $prefix . '&category_id=1')['body'], $prefix . ' ' . $type), $type . ' public search and category query');
        if ($type === 'news') { $data['published_at'] = '2099-01-01T10:00'; request('/admin/news/' . $id, $data, 'admin'); check(request($publicUrl)['status'] === 404 && !str_contains(request('/sitemap.xml')['body'], $data['slug']), 'Scheduled news hidden from pages and sitemap'); }
        request('/admin/' . $type . '/' . $id . '/delete', ['_token' => $data['_token']], 'admin'); check(countSlug($table, $data['slug']) === 0, $type . ' delete');
    }

    $contact = ['_token' => token('/contact', 'public'), 'name' => $prefix, 'email' => 'test@example.test', 'subject' => 'Verification', 'message' => str_repeat('Message ', 30)];
    request('/contact', $contact); $stmt = $pdo->prepare('SELECT id FROM contact_messages WHERE name = ?'); $stmt->execute([$prefix]); $messageId = (int) $stmt->fetchColumn();
    check($messageId > 0 && str_contains(request('/admin/contact-messages', null, 'admin')['body'], $prefix), 'Contact message stored and visible in administration');
    request('/admin/contact-messages/' . $messageId . '/read', ['_token' => $csrf], 'admin');
    check((bool) $pdo->query('SELECT read_at FROM contact_messages WHERE id = ' . $messageId)->fetchColumn(), 'Contact message marked read');
    request('/admin/contact-messages/' . $messageId . '/delete', ['_token' => $csrf], 'admin');
    $contact['website'] = 'spam'; request('/contact', $contact); $stmt->execute([$prefix]); check(!$stmt->fetchColumn(), 'Contact honeypot skips storage');

    $png = tempnam(sys_get_temp_dir(), 'wmma-upload-');
    file_put_contents($png, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    $uploaded[] = $png;
    $mediaCsrf = token('/admin/media');
    request('/admin/media', ['_token' => $mediaCsrf, 'file' => new CURLFile($png, 'image/png', $prefix . '.png'), 'alt_text' => $prefix], 'admin');
    $stmt = $pdo->prepare('SELECT * FROM media WHERE original_name = ?'); $stmt->execute([$prefix . '.png']); $media = $stmt->fetch();
    check((bool) $media && request($media['path'])['status'] === 200, 'Real multipart image upload and public file');
    check(str_contains(request('/admin/media?q=' . $prefix, null, 'admin')['body'], $prefix . '.png'), 'Media search');
    if ($media) {
        $row = $event; unset($row['_token']); $row['slug'] = $prefix . '-media'; $row['cover_image'] = $media['path']; $id = Event::save($row);
        request('/admin/media/' . $media['id'] . '/delete', ['_token' => $mediaCsrf], 'admin');
        check((bool) \App\Models\Media::find((int) $media['id']), 'In-use media deletion prevented');
        Event::delete($id);
        \App\Models\Setting::updateMany(['home_hero_images' => [$media['path']]]);
        request('/admin/media/' . $media['id'] . '/delete', ['_token' => $mediaCsrf], 'admin');
        check((bool) \App\Models\Media::find((int) $media['id']), 'JSON homepage settings protect referenced media');
        \App\Models\Setting::updateMany($settingsSnapshot);
        request('/admin/media/' . $media['id'] . '/delete', ['_token' => $mediaCsrf], 'admin');
        check(!\App\Models\Media::find((int) $media['id']) && request($media['path'])['status'] === 404, 'Unused media removed from database and disk');
    }
    request('/admin/media', ['_token' => $mediaCsrf, 'file' => new CURLFile($png, 'image/png', $prefix . '.php')], 'admin');
    $stmt->execute([$prefix . '.php']); check(!$stmt->fetch(), 'Image with executable filename rejected');
    $bad = tempnam(sys_get_temp_dir(), 'wmma-bad-'); file_put_contents($bad, '<?php echo 1;'); $uploaded[] = $bad;
    request('/admin/media', ['_token' => $mediaCsrf, 'file' => new CURLFile($bad, 'image/png', $prefix . '-bad.png')], 'admin');
    $stmt->execute([$prefix . '-bad.png']); check(!$stmt->fetch(), 'Spoofed image MIME rejected');
    $pdf = tempnam(sys_get_temp_dir(), 'wmma-pdf-'); file_put_contents($pdf, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n"); $uploaded[] = $pdf;
    request('/admin/media', ['_token' => $mediaCsrf, 'file' => new CURLFile($pdf, 'application/pdf', $prefix . '.pdf')], 'admin');
    $stmt->execute([$prefix . '.pdf']); $pdfMedia = $stmt->fetch(); check((bool) $pdfMedia, 'PDF media upload');
    if ($pdfMedia) request('/admin/media/' . $pdfMedia['id'] . '/delete', ['_token' => $mediaCsrf], 'admin');
    $invalid = $event; $invalid['slug'] = $prefix . '-pdf-cover'; $invalid['cover_upload'] = new CURLFile($pdf, 'application/pdf', $prefix . '.pdf');
    request('/admin/events', $invalid, 'admin'); check(countSlug('events', $invalid['slug']) === 0, 'PDF rejected as event image');
    $invalid['cover_upload'] = new CURLFile($png, 'image/png', $prefix . '-cover.png'); $invalid['slug'] = $prefix . '-uploaded-cover';
    request('/admin/events', $invalid, 'admin');
    check(countSlug('events', $invalid['slug']) === 1, 'Event cover multipart upload');
    $stmt->execute([$prefix . '-cover.png']); $coverMedia = $stmt->fetch(); check((bool) $coverMedia, 'Event cover registered in media library');
    $pdo->prepare('DELETE FROM events WHERE slug = ?')->execute([$invalid['slug']]);
    if ($coverMedia) request('/admin/media/' . $coverMedia['id'] . '/delete', ['_token' => $mediaCsrf], 'admin');
    $large = tempnam(sys_get_temp_dir(), 'wmma-large-'); file_put_contents($large, file_get_contents($png) . str_repeat('x', 5 * 1024 * 1024)); $uploaded[] = $large;
    request('/admin/media', ['_token' => $mediaCsrf, 'file' => new CURLFile($large, 'image/png', $prefix . '-large.png')], 'admin');
    $stmt->execute([$prefix . '-large.png']); check(!$stmt->fetch(), 'Oversize upload rejected');

    $settingsBefore = \App\Models\Setting::all();
    request('/admin/settings', ['_token' => token('/admin/settings'), 'site_name' => 'Verification', 'facebook_url' => 'javascript:alert(1)'], 'admin');
    check(\App\Models\Setting::all()['site_name'] === $settingsBefore['site_name'], 'Unsafe settings URL rejected atomically');
    $settingsData = $settingsBefore;
    $settingsData['home_hero_images'] = implode("\n", json_decode($settingsBefore['home_hero_images'] ?? '[]', true) ?: []);
    $settingsData['_token'] = token('/admin/settings'); $settingsData['site_name'] = 'Website ' . $prefix;
    request('/admin/settings', $settingsData, 'admin');
    check(str_contains(request('/')['body'], 'Website ' . $prefix), 'Valid settings update appears on public website');
    \App\Models\Setting::updateMany($settingsSnapshot);
    request('/admin/users/' . $adminId . '/toggle', ['_token' => token('/admin/users')], 'admin');
    check((int) User::find($adminId)['is_active'] === 1, 'Cannot disable own account');
    if ((int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "super_admin" AND is_active = 1')->fetchColumn() === 1) {
        $blocked = false;
        try { User::toggle($adminId); } catch (LogicException) { $blocked = true; }
        check($blocked && (int) User::find($adminId)['is_active'] === 1, 'Last active administrator protected in transaction');
    }
    request('/admin/login', ['_token' => token('/admin/login', 'editor'), 'email' => $prefix . '-editor@example.test', 'password' => $password], 'editor');
    check(request('/admin/events', null, 'editor')['status'] === 200 && request('/admin/users', null, 'editor')['status'] === 403 && request('/admin/settings', null, 'editor')['status'] === 403, 'Editor permissions enforced');
    check(request('/admin/settings', ['_token' => token('/admin/events', 'editor'), 'site_name' => 'Forbidden'], 'editor')['status'] === 403, 'Editor cannot modify settings');
    request('/admin/users/' . $editorId . '/toggle', ['_token' => token('/admin/users')], 'admin');
    check(request('/admin/events', null, 'editor')['status'] === 302, 'Disabling account revokes existing session');
    request('/lang/ar', null, 'admin'); $arabic = request('/admin/events', null, 'admin');
    check(str_contains($arabic['body'], 'lang="ar" dir="rtl"') && str_contains($arabic['body'], 'الفعاليات'), 'Arabic dashboard RTL and translated labels');
    request('/lang/ar'); $arabic = request('/');
    check(str_contains($arabic['body'], 'lang="ar" dir="rtl"') && str_contains($arabic['body'], 'المعرفة الحرة'), 'Arabic public homepage');
    request('/lang/fr'); check(str_contains(request('/')['body'], 'lang="fr" dir="ltr"'), 'French interface restored');
    request('/admin/logout', ['_token' => token('/admin/events')], 'admin');
    check(request('/admin', null, 'admin')['status'] === 302, 'Logout clears authorization');
    for ($i = 0; $i < 5; $i++) request('/admin/login', ['_token' => token('/admin/login', 'rate'), 'email' => $prefix . '-lock@example.test', 'password' => 'incorrect'], 'rate');
    $limited = request('/admin/login', ['_token' => token('/admin/login', 'rate'), 'email' => $prefix . '@example.test', 'password' => $password], 'rate');
    check(str_ends_with($limited['headers']['location'] ?? '', '/admin/login'), 'Login attempt rate limit enforced');
} catch (Throwable $exception) {
    $failed++; echo 'ERROR ' . $exception->getMessage() . "\n";
} finally {
    \App\Models\Setting::updateMany($settingsSnapshot);
    foreach (['events', 'projects', 'news', 'team_members', 'resources'] as $table) { $stmt = $pdo->prepare("DELETE FROM {$table} WHERE slug LIKE ?"); $stmt->execute([$prefix . '%']); }
    $stmt = $pdo->prepare('DELETE FROM media WHERE original_name LIKE ?'); $stmt->execute([$prefix . '%']);
    $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE name = ?'); $stmt->execute([$prefix]);
    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email LIKE ?'); $stmt->execute([$prefix . '%']);
    $stmt = $pdo->prepare('DELETE FROM activity_logs WHERE user_id IN (?, ?)'); $stmt->execute([$adminId, $editorId]);
    $stmt = $pdo->prepare('DELETE FROM users WHERE id IN (?, ?)'); $stmt->execute([$adminId, $editorId]);
    foreach (array_merge(array_values($jars), $uploaded) as $file) if (is_file($file)) unlink($file);
}
echo "Results: {$passed} passed, {$failed} failed.\n";
exit($failed ? 1 : 0);
