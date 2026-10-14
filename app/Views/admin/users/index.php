<?php $errors = $_SESSION['_errors'] ?? []; unset($_SESSION['_errors']); ?>
<section class="admin-card" style="margin-bottom:22px">
    <h2 style="margin-top:0"><?= e(ui('Create administrator')) ?></h2>
    <?php foreach ($errors as $error): ?><p class="form-error" role="alert"><?= e(ui($error)) ?></p><?php endforeach; ?>
    <form class="filters" method="post" action="<?= e(url('/admin/users')) ?>" style="grid-template-columns:1fr 1fr 1fr 1fr auto">
        <?= csrf_field() ?>
        <label class="sr-only" for="user_name"><?= e(ui('Name')) ?></label>
        <input class="form-control" id="user_name" name="name" placeholder="<?= e(ui('Name')) ?>" maxlength="120" autocomplete="name" required>
        <label class="sr-only" for="user_email"><?= e(ui('Email')) ?></label>
        <input class="form-control" id="user_email" name="email" type="email" placeholder="<?= e(ui('Email')) ?>" maxlength="160" autocomplete="off" required>
        <label class="sr-only" for="user_password"><?= e(ui('Password')) ?></label>
        <input class="form-control" id="user_password" name="password" type="password" placeholder="<?= e(ui('Password')) ?>" minlength="10" maxlength="72" autocomplete="new-password" required>
        <label class="sr-only" for="user_role"><?= e(ui('Role')) ?></label>
        <select class="form-control" id="user_role" name="role">
            <option value="editor"><?= e(ui('Editor')) ?></option>
            <option value="super_admin"><?= e(ui('Super Administrator')) ?></option>
        </select>
        <button class="btn btn-primary" type="submit"><?= icon('plus') ?> <?= e(ui('Create')) ?></button>
    </form>
</section>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th><?= e(ui('Name')) ?></th><th><?= e(ui('Email')) ?></th><th><?= e(ui('Role')) ?></th><th><?= e(ui('Status')) ?></th><th><?= e(ui('Last login')) ?></th><th><?= e(ui('Actions')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= e($user['name']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td>
                    <form class="row-actions" style="min-width:272px" method="post" action="<?= e(url('/admin/users/' . $user['id'] . '/role')) ?>">
                        <?= csrf_field() ?>
                        <label class="sr-only" for="role_<?= (int) $user['id'] ?>"><?= e(ui('Role') . ': ' . $user['name']) ?></label>
                        <select class="form-control" id="role_<?= (int) $user['id'] ?>" name="role">
                            <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>><?= e(ui('Editor')) ?></option>
                            <option value="super_admin" <?= $user['role'] === 'super_admin' ? 'selected' : '' ?>><?= e(ui('Super Administrator')) ?></option>
                        </select>
                        <button class="btn btn-small btn-ghost" type="submit" title="<?= e(ui('Save role')) ?>" aria-label="<?= e(ui('Save role') . ': ' . $user['name']) ?>"><?= icon('check') ?></button>
                    </form>
                </td>
                <td><span class="badge"><?= e(ui((int) $user['is_active'] ? 'active' : 'disabled')) ?></span></td>
                <td><?= e($user['last_login_at'] ? format_date($user['last_login_at'], 'd M Y H:i') : ui('Never')) ?></td>
                <td>
                    <form method="post" action="<?= e(url('/admin/users/' . $user['id'] . '/toggle')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-small btn-ghost" style="white-space:nowrap" type="submit"><?= e(ui((int) $user['is_active'] ? 'Deactivate' : 'Activate')) ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
