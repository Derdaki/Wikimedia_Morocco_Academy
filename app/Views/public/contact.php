<?php $errors = $_SESSION['_errors'] ?? []; unset($_SESSION['_errors']); ?>
<section class="section section-alt">
    <div class="container">
        <h1 class="section-title"><?= e(ui('Contact')) ?></h1>
        <p class="section-lead"><?= e(ui('Une question, une proposition de partenariat ou une idée d’atelier ? Écrivez-nous.')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container two-col">
        <form class="stack admin-card" method="post" action="<?= e(url('/contact')) ?>">
            <?= csrf_field() ?>
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
            <div>
                <label class="form-label" for="name"><?= e(ui('Nom')) ?></label>
                <input class="form-control" id="name" name="name" value="<?= e(old('name')) ?>" required>
                <?php if (isset($errors['name'])): ?><div class="form-error"><?= e(ui($errors['name'])) ?></div><?php endif; ?>
            </div>
            <div>
                <label class="form-label" for="email"><?= e(ui('Email')) ?></label>
                <input class="form-control" id="email" name="email" type="email" value="<?= e(old('email')) ?>" required>
                <?php if (isset($errors['email'])): ?><div class="form-error"><?= e(ui($errors['email'])) ?></div><?php endif; ?>
            </div>
            <div>
                <label class="form-label" for="subject"><?= e(ui('Sujet')) ?></label>
                <input class="form-control" id="subject" name="subject" value="<?= e(old('subject')) ?>" required>
                <?php if (isset($errors['subject'])): ?><div class="form-error"><?= e(ui($errors['subject'])) ?></div><?php endif; ?>
            </div>
            <div>
                <label class="form-label" for="message"><?= e(ui('Message')) ?></label>
                <textarea class="form-control" id="message" name="message" rows="7" required><?= e(old('message')) ?></textarea>
                <?php if (isset($errors['message'])): ?><div class="form-error"><?= e(ui($errors['message'])) ?></div><?php endif; ?>
            </div>
            <button class="btn btn-primary" type="submit"><?= icon('mail') ?> <?= e(ui('Envoyer')) ?></button>
        </form>
        <aside class="admin-card">
            <h2><?= e(ui('Coordonnées')) ?></h2>
            <p><strong><?= e(ui('Email')) ?></strong><br><?= e(setting('contact_email', 'contact@wikimedia.ma')) ?></p>
            <p><strong><?= e(ui('Communauté')) ?></strong><br><?= e(ui('Rejoignez les ateliers, campagnes et rencontres de contribution.')) ?></p>
            <a class="btn btn-outline" href="<?= e(url('/rejoindre-la-communaute')) ?>"><?= e(ui('Participer')) ?></a>
        </aside>
    </div>
</section>
