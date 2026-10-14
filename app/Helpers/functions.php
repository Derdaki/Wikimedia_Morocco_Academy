<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Env;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) Env::get('APP_URL', ''), '/');
    $path = '/' . ltrim($path, '/');

    return $base ? $base . $path : $path;
}

function asset(string $path): string
{
    if (!safe_url($path)) {
        return '';
    }
    if (str_starts_with($path, 'https://commons.wikimedia.org/wiki/Special:FilePath/')) {
        $name = str_replace('_', ' ', rawurldecode(basename((string) parse_url($path, PHP_URL_PATH))));
        $bundled = [
            'Hassan II Mosque Plaza.jpg' => 'plaza.jpg',
            'Moroccan Carpet (4804578240).jpg' => 'rug.jpg',
            'Wikimedia MA User Group.svg' => 'logo.png',
            'Maroc, Mosquée Hassan 2, Grand Casablanca.jpg' => 'mosque.jpg',
            'Ait Benhaddou.jpg' => 'ksar.jpg', 'Moroccan carpet.jpg' => 'carpet.jpg',
            'Atlas Mountains, Fez, Morocco.jpg' => 'atlas.jpg',
        ];
        if (isset($bundled[$name]) && is_file(PUBLIC_PATH . '/assets/images/' . $bundled[$name])) return url('/assets/images/' . $bundled[$name]);
    }
    if (preg_match('#^https?://#', $path) === 1) {
        return $path;
    }

    return url('/' . ltrim($path, '/'));
}

function safe_url(string $value): bool
{
    if ($value === '') return true;
    if (preg_match('/[\x00-\x20\x7f\\\\]/', $value)) return false;
    if (str_starts_with($value, '/') && !str_starts_with($value, '//')) return true;
    return filter_var($value, FILTER_VALIDATE_URL) !== false &&
        in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function safe_back_path(string $referer): string
{
    $path = (string) (parse_url($referer, PHP_URL_PATH) ?: '/');
    $query = (string) (parse_url($referer, PHP_URL_QUERY) ?: '');
    $path .= $query !== '' ? '?' . $query : '';
    return str_starts_with($path, '/') && safe_url($path) ? $path : '/';
}

function ui(string $text): string
{
    static $messages = null;
    $messages ??= require APP_PATH . '/Lang/interface.php';
    return $messages[$text][locale()] ?? $text;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function csrf_field(): string
{
    return Csrf::input();
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function remember_old(array $data): void
{
    unset($data['password'], $data['_token']);
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function locale(): string
{
    $locale = $_SESSION['locale'] ?? 'fr';
    return in_array($locale, ['fr', 'ar'], true) ? $locale : 'fr';
}

function dir_attr(): string
{
    return locale() === 'ar' ? 'rtl' : 'ltr';
}

function trans(string $key): string
{
    static $lines = [];
    $current = locale();

    if (!isset($lines[$current])) {
        $path = APP_PATH . '/Lang/' . $current . '.php';
        $lines[$current] = is_file($path) ? require $path : [];
    }

    return $lines[$current][$key] ?? $key;
}

function localized(array $row, string $field, ?string $fallback = null): string
{
    $current = locale();
    $value = $row[$field . '_' . $current] ?? null;

    if ($value !== null && trim((string) $value) !== '') {
        return (string) $value;
    }

    $fallbackValue = $row[$field . '_fr'] ?? $row[$field] ?? $fallback ?? '';
    return (string) $fallbackValue;
}

function slugify(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?: '';
    $value = trim($value, '-');

    return $value !== '' ? $value : bin2hex(random_bytes(4));
}

function excerpt(string $value, int $length = 140): string
{
    $plain = trim(strip_tags($value));

    if (mb_strlen($plain) <= $length) {
        return $plain;
    }

    return rtrim(mb_substr($plain, 0, $length - 1)) . '...';
}

function format_date(?string $date, string $format = 'd M Y'): string
{
    if (!$date) {
        return '';
    }

    $formatted = (new DateTime($date))->format($format);
    $months = locale() === 'ar'
        ? ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
        : ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    return str_replace(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], $months, $formatted);
}

function setting(string $key, string $default = ''): string
{
    static $settings = null;

    if ($settings === null) {
        try {
            $rows = Database::pdo()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable) {
            $settings = [];
        }
    }

    return (string) ($settings[$key] ?? $default);
}

function activity(string $action, ?string $subjectType = null, ?int $subjectId = null, ?string $description = null): void
{
    try {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO activity_logs (user_id, action, subject_type, subject_id, description, ip_address, created_at)
             VALUES (:user_id, :action, :subject_type, :subject_id, :description, :ip, NOW())'
        );
        $stmt->execute([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable) {
    }
}

function selected(bool $condition): string
{
    return $condition ? ' selected' : '';
}

function checked(bool $condition): string
{
    return $condition ? ' checked' : '';
}

function icon(string $name, string $class = 'icon'): string
{
    $icons = [
        'arrow-right' => '<path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path>',
        'book-open' => '<path d="M12 7v14"></path><path d="M3 18a1 1 0 0 1-1-1V5a2 2 0 0 1 2-2h5a3 3 0 0 1 3 3v15a3 3 0 0 0-3-3z"></path><path d="M21 18a1 1 0 0 0 1-1V5a2 2 0 0 0-2-2h-5a3 3 0 0 0-3 3"></path>',
        'calendar' => '<path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="18" height="18" x="3" y="4" rx="2"></rect><path d="M3 10h18"></path>',
        'camera' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3z"></path><circle cx="12" cy="13" r="3"></circle>',
        'check' => '<path d="M20 6 9 17l-5-5"></path>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"></path>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"></path>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="M7 10l5 5 5-5"></path><path d="M12 15V3"></path>',
        'edit' => '<path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path>',
        'external-link' => '<path d="M15 3h6v6"></path><path d="M10 14 21 3"></path><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>',
        'file-text' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M16 13H8"></path><path d="M16 17H8"></path><path d="M10 9H8"></path>',
        'graduation-cap' => '<path d="M22 10 12 5 2 10l10 5 10-5Z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path>',
        'home' => '<path d="m3 11 9-8 9 8"></path><path d="M5 10v10h14V10"></path><path d="M9 20v-6h6v6"></path>',
        'image' => '<rect width="18" height="18" x="3" y="3" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"></path>',
        'layout-dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
        'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path>',
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-10 6L2 7"></path>',
        'menu' => '<path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h16"></path>',
        'newspaper' => '<path d="M4 22h14a2 2 0 0 0 2-2V7H8"></path><path d="M4 22a2 2 0 0 1-2-2V5h6v15a2 2 0 0 1-2 2"></path><path d="M10 12h8"></path><path d="M10 16h8"></path><path d="M10 8h8"></path>',
        'plus' => '<path d="M12 5v14"></path><path d="M5 12h14"></path>',
        'search' => '<circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path>',
        'settings' => '<path d="M12.2 2h-.4a2 2 0 0 0-2 2l-.1.8a2 2 0 0 1-1.1 1.5l-.7.4a2 2 0 0 1-1.9 0l-.7-.4a2 2 0 0 0-2.7.7l-.2.3a2 2 0 0 0 .7 2.7l.7.4A2 2 0 0 1 5 12a2 2 0 0 1-1.1 1.6l-.7.4a2 2 0 0 0-.7 2.7l.2.3a2 2 0 0 0 2.7.7l.7-.4a2 2 0 0 1 1.9 0l.7.4a2 2 0 0 1 1.1 1.5l.1.8a2 2 0 0 0 2 2h.4a2 2 0 0 0 2-2l.1-.8a2 2 0 0 1 1.1-1.5l.7-.4a2 2 0 0 1 1.9 0l.7.4a2 2 0 0 0 2.7-.7l.2-.3a2 2 0 0 0-.7-2.7l-.7-.4A2 2 0 0 1 19 12a2 2 0 0 1 1.1-1.6l.7-.4a2 2 0 0 0 .7-2.7l-.2-.3a2 2 0 0 0-2.7-.7l-.7.4a2 2 0 0 1-1.9 0l-.7-.4a2 2 0 0 1-1.1-1.5l-.1-.8a2 2 0 0 0-2-2Z"></path><circle cx="12" cy="12" r="3"></circle>',
        'shield' => '<path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3z"></path>',
        'trash' => '<path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.9"></path><path d="M16 3.1a4 4 0 0 1 0 7.8"></path>',
        'wikipedia' => '<path d="M3 5h3l3.5 11L13 5h3l3.5 11L22 5"></path>',
    ];

    $paths = $icons[$name] ?? $icons['arrow-right'];
    $label = match ($name) { 'trash' => ui('Delete'), 'search' => trans('search'), 'edit' => locale() === 'ar' ? 'تعديل' : 'Modifier', 'plus' => ui('Create'), default => ui('Public site') };

    return '<svg data-icon-label="' . e($label) . '" class="' . e($class) . '" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}
