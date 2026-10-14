<section class="auth-card">
    <a href="<?= e(url('/lang/' . (locale() === 'fr' ? 'ar' : 'fr'))) ?>" class="btn btn-small btn-ghost" style="float:inline-end"><?= locale() === 'fr' ? 'AR' : 'FR' ?></a>
    <div class="brand" style="margin-bottom:22px">
        <img src="<?= e(asset(setting('site_logo') ?: '/assets/images/logo.png')) ?>" alt="Logo Wikimedia Maroc">
        <span class="brand-text">Wikimedia <span>Maroc</span></span>
    </div>
    <h1 style="margin:0 0 8px;color:#111827"><?= e(ui('Connexion admin')) ?></h1>
    <p style="margin:0 0 22px;color:#667085"><?= e(ui('Accédez au tableau de bord de gestion.')) ?></p>
    <?php if ($message = flash('success')): ?><div class="alert alert-success"><?= e(ui($message)) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="alert alert-error"><?= e(ui($message)) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/admin/login')) ?>" class="stack">
        <?= csrf_field() ?>
        <div>
            <label class="form-label" for="email"><?= e(ui('Email')) ?></label>
            <input class="form-control" id="email" name="email" type="email" autocomplete="email" required>
        </div>
        <div>
            <label class="form-label" for="password"><?= e(ui('Mot de passe')) ?></label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary" type="submit"><?= icon('lock') ?> <?= e(ui('Se connecter')) ?></button>
    </form>
</section>
