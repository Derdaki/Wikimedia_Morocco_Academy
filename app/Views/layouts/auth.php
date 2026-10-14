<!doctype html>
<html lang="<?= e(locale()) ?>" dir="<?= e(dir_attr()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(ui('Connexion admin')) ?> | Wikimedia Maroc Admin</title>
    <link rel="stylesheet" href="<?= e(asset('/assets/css/app.css')) ?>">
</head>
<body dir="<?= e(dir_attr()) ?>">
<main class="auth-page">
    <?= $content ?>
</main>
</body>
</html>
