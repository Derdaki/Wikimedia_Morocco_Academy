<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$siteName = setting('site_name', 'Wikimedia Maroc');
$logo = setting('site_logo') ?: '/assets/images/logo.png';
$description = $metaDescription ?? setting('site_description', 'Wikimedia Maroc promeut la connaissance libre et le partage du patrimoine marocain.');
$nav = [
    '/' => trans('home'),
    '/nos-projets' => trans('projects'),
    '/evenements' => trans('events'),
    '/actualites' => trans('news'),
    '/ressources' => trans('resources'),
    '/a-propos' => trans('about'),
    '/contact' => trans('contact'),
];
?>
<!doctype html>
<html lang="<?= e(locale()) ?>" dir="<?= e(dir_attr()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(ui($title ?? $siteName)) ?> | <?= e($siteName) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta property="og:title" content="<?= e($title ?? $siteName) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(url($currentPath)) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/app.css')) ?>">
    <script src="<?= e(asset('/assets/js/app.js')) ?>" defer></script>
</head>
<body dir="<?= e(dir_attr()) ?>">
<a class="skip-link" href="#main"><?= e(ui('Aller au contenu')) ?></a>
<div class="page-shell">
    <header class="site-header">
        <div class="container nav-bar">
            <a class="brand" href="<?= e(url('/')) ?>" aria-label="Wikimedia Maroc">
                <img src="<?= e(asset($logo)) ?>" alt="Logo Wikimedia Maroc" width="54" height="44">
                <span class="brand-text">Wikimedia <span>Maroc</span></span>
            </a>
            <button class="mobile-menu-button" type="button" data-toggle-menu="#site-menu" aria-expanded="false" aria-controls="site-menu" aria-label="<?= e(ui('Ouvrir le menu')) ?>">
                <?= icon('menu') ?>
            </button>
            <nav class="nav-menu" id="site-menu" aria-label="<?= e(ui('Navigation principale')) ?>">
                <?php foreach ($nav as $href => $label): ?>
                    <?php $active = $href === '/' ? $currentPath === '/' : str_starts_with($currentPath, $href); ?>
                    <a href="<?= e(url($href)) ?>" class="<?= $active ? 'active' : '' ?>"><?= e(ui($label)) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="nav-actions">
                <div class="language-switch" aria-label="<?= e(ui('Langue')) ?>">
                    <a class="<?= locale() === 'fr' ? 'active' : '' ?>" href="<?= e(url('/lang/fr')) ?>">FR</a>
                    <a class="<?= locale() === 'ar' ? 'active' : '' ?>" href="<?= e(url('/lang/ar')) ?>">AR</a>
                </div>
                <a class="btn btn-outline" href="<?= e(url('/rejoindre-la-communaute')) ?>"><?= e(trans('join')) ?></a>
            </div>
        </div>
    </header>

    <main id="main">
        <?php if ($message = flash('success')): ?>
            <div class="container" style="padding-top:18px"><div class="alert alert-success"><?= e(ui($message)) ?></div></div>
        <?php endif; ?>
        <?php if ($message = flash('error')): ?>
            <div class="container" style="padding-top:18px"><div class="alert alert-error"><?= e(ui($message)) ?></div></div>
        <?php endif; ?>
        <?= $content ?>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a class="brand" href="<?= e(url('/')) ?>">
                        <img src="<?= e(asset($logo)) ?>" alt="Logo Wikimedia Maroc" width="54" height="44">
                        <span class="brand-text">Wikimedia <span>Maroc</span></span>
                    </a>
                    <p><?= e(locale() === 'ar' ? (setting('footer_description_ar') ?: 'ويكيميديا المغرب مجموعة مستخدمين تدعم المعرفة الحرة والمجتمع المغربي.') : setting('footer_description', 'Wikimedia Maroc est un groupe d’utilisateurs reconnu qui soutient la connaissance libre au Maroc.')) ?></p>
                </div>
                <div>
                    <h4><?= e(ui('Liens utiles')) ?></h4>
                    <ul>
                        <li><a href="<?= e(url('/nos-projets')) ?>"><?= e(ui('Nos projets')) ?></a></li>
                        <li><a href="<?= e(url('/evenements')) ?>"><?= e(ui('Événements')) ?></a></li>
                        <li><a href="<?= e(url('/ressources')) ?>"><?= e(ui('Ressources')) ?></a></li>
                        <li><a href="<?= e(url('/contact')) ?>"><?= e(ui('Contact')) ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4><?= e(ui('À propos')) ?></h4>
                    <ul>
                        <li><a href="<?= e(url('/a-propos')) ?>"><?= e(ui('Notre mission')) ?></a></li>
                        <li><a href="<?= e(url('/notre-equipe')) ?>"><?= e(ui('Notre équipe')) ?></a></li>
                        <li><a href="<?= e(url('/rejoindre-la-communaute')) ?>"><?= e(ui('Participer')) ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4><?= e(ui('Suivez-nous')) ?></h4>
                    <div class="social-links" aria-label="<?= e(ui('Réseaux sociaux')) ?>">
                        <?php foreach (['facebook_url' => 'f', 'x_url' => 'X', 'instagram_url' => 'ig', 'linkedin_url' => 'in', 'youtube_url' => 'yt'] as $key => $label): ?>
                            <?php if (setting($key) && safe_url(setting($key))): ?>
                                <a href="<?= e(setting($key)) ?>" target="_blank" rel="noopener"><?= e(ui($label)) ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <p><?= e(setting('contact_email', 'contact@wikimedia.ma')) ?></p>
                    <a class="btn btn-outline" href="<?= e(url('/rejoindre-la-communaute')) ?>"><?= e(trans('join')) ?> <?= icon('arrow-right') ?></a>
                </div>
            </div>
            <div class="copyright">© <?= date('Y') ?> Wikimedia Maroc. <a href="<?= e(url('/credits')) ?>"><?= locale() === 'ar' ? 'حقوق الصور' : 'Crédits images' ?></a></div>
        </div>
    </footer>
</div>
</body>
</html>
