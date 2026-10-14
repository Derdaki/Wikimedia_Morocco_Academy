<section class="admin-card" style="max-width:560px">
    <h2><?= e(ui('Change password')) ?></h2>
    <form class="stack" method="post" action="<?= e(url('/admin/account/password')) ?>">
        <?= csrf_field() ?>
        <div>
            <label class="form-label" for="current_password"><?= e(ui('Current password')) ?></label>
            <input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
        </div>
        <div>
            <label class="form-label" for="new_password"><?= e(ui('New password')) ?></label>
            <input class="form-control" id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="10" maxlength="72" required>
        </div>
        <div>
            <label class="form-label" for="password_confirmation"><?= e(ui('Confirm password')) ?></label>
            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="10" maxlength="72" required>
        </div>
        <button class="btn btn-primary" type="submit"><?= icon('lock') ?> <?= e(ui('Change password')) ?></button>
    </form>
</section>
