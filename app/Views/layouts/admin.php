<?php
use App\Core\Auth;

$user = Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
$nav = [
    '/admin' => ['Dashboard', 'layout-dashboard'],
    '/admin/events' => ['Events', 'calendar'],
    '/admin/projects' => ['Projects', 'book-open'],
    '/admin/news' => ['News', 'newspaper'],
    '/admin/team' => ['Team', 'users'],
    '/admin/media' => ['Media Library', 'image'],
    '/admin/resources' => ['Resources', 'file-text'],
    '/admin/contact-messages' => ['Contact Messages', 'mail'],
    '/admin/users' => ['Users', 'shield'],
    '/admin/settings' => ['Website Settings', 'settings'],
];
?>
<!doctype html>
<html lang="<?= e(locale()) ?>" dir="<?= e(dir_attr()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(ui($title ?? 'Admin')) ?> | Wikimedia Maroc Admin</title>
    <link rel="stylesheet" href="<?= e(asset('/assets/css/app.css')) ?>">
    <script src="<?= e(asset('/assets/js/app.js')) ?>" defer></script>
</head>
<body dir="<?= e(dir_attr()) ?>">
<div class="admin-layout">
    <button class="admin-sidebar-backdrop" type="button" data-close-sidebar aria-label="<?= e(ui('Fermer le menu')) ?>"></button>
    <aside class="admin-sidebar" id="admin-sidebar">
        <button class="btn btn-small admin-sidebar-close" type="button" data-close-sidebar aria-label="<?= e(ui('Fermer le menu')) ?>">&times;</button>
        <a class="admin-brand" href="<?= e(url('/admin')) ?>">
            <?= icon('shield') ?>
            <span><?= e(ui('WM Maroc Admin')) ?></span>
        </a>
        <nav class="admin-nav" aria-label="<?= e(ui('Administration')) ?>">
            <?php foreach ($nav as $href => [$label, $icon]): ?>
                <?php if (($href === '/admin/users' || $href === '/admin/settings') && !\App\Core\Auth::hasRole('super_admin')) continue; ?>
                <?php $active = $href === '/admin' ? $currentPath === '/admin' : str_starts_with($currentPath, $href); ?>
                <a class="<?= $active ? 'active' : '' ?>" href="<?= e(url($href)) ?>"><?= icon($icon) ?> <?= e(ui($label)) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-user">
            <strong><?= e($user['name'] ?? 'Admin') ?></strong>
            <p style="margin:4px 0 12px;color:#9ca3af"><?= e(ui($user['role'] ?? '')) ?></p>
            <form method="post" action="<?= e(url('/admin/logout')) ?>">
                <?= csrf_field() ?>
                <button class="admin-logout" type="submit"><?= icon('log-out') ?> <?= e(ui('Logout')) ?></button>
            </form>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <div class="breadcrumb"><?= e(ui('Admin /')) ?> <?= e(ui($title ?? 'Dashboard')) ?></div>
                <h1 class="admin-title"><?= e(ui($title ?? 'Dashboard')) ?></h1>
            </div>
            <div style="display:flex;gap:10px;align-items:center">
                <a href="<?= e(url('/lang/' . (locale() === 'fr' ? 'ar' : 'fr'))) ?>" class="btn btn-ghost" aria-label="<?= e(ui('Langue')) ?>"><?= locale() === 'fr' ? 'AR' : 'FR' ?></a>
                <button class="btn btn-ghost mobile-admin-toggle" type="button" data-admin-sidebar-toggle aria-expanded="false" aria-controls="admin-sidebar" aria-label="<?= e(ui('Ouvrir le menu')) ?>"><?= icon('menu') ?></button>
                <a class="btn btn-ghost" href="<?= e(url('/')) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?> <?= e(ui('Public site')) ?></a>
            </div>
        </header>
        <main class="admin-content">
            <?php if ($message = flash('success')): ?>
                <div class="alert alert-success"><?= e(ui($message)) ?></div>
            <?php endif; ?>
            <?php if ($message = flash('error')): ?>
                <div class="alert alert-error"><?= e(ui($message)) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</div>
</body>
</html>
