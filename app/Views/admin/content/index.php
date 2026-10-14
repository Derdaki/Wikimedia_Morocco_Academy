<div style="display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:20px">
    <form method="get" style="display:flex;gap:10px;min-width:min(420px,100%)">
        <label class="sr-only" for="content_q"><?= e(ui('Search')) ?> <?= e(mb_strtolower($config['label'])) ?></label>
        <input class="form-control" id="content_q" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e(trans('search')) ?> <?= e(ui($config['label'])) ?>">
        <button class="btn btn-primary" type="submit"><?= icon('search') ?></button>
    </form>
    <a class="btn btn-primary" href="<?= e(url('/admin/' . $type . '/create')) ?>"><?= icon('plus') ?> <?= e(ui('Create')) ?> <?= e(ui($config['singular'])) ?></a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr>
            <th><?= e(ui('Title')) ?></th>
            <th><?= e(ui('Slug')) ?></th>
            <th><?= e(ui('Status')) ?></th>
            <th><?= e(ui('Updated')) ?></th>
            <th><?= e(ui('Actions')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($result['items'] as $item): ?>
            <tr>
                <td><strong><?= e($type === 'team' ? $item['name'] : localized($item, 'title')) ?></strong></td>
                <td><?= e($item['slug']) ?></td>
                <td><span class="badge"><?= e(ui($type === 'team' ? ((int) $item['is_visible'] ? 'visible' : 'hidden') : $item['status'])) ?></span></td>
                <td><?= e(format_date($item['updated_at'] ?? $item['created_at'], 'd M Y')) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-small btn-ghost" href="<?= e(url('/admin/' . $type . '/' . $item['id'] . '/edit')) ?>"><?= icon('edit') ?></a>
                        <form method="post" action="<?= e(url('/admin/' . $type . '/' . $item['id'] . '/delete')) ?>" data-confirm="<?= e(ui('Delete this item?')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-small btn-ghost" type="submit"><?= icon('trash') ?></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$result['items']): ?>
            <tr><td colspan="5"><div class="empty-state"><?= e(ui('No items found.')) ?></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require APP_PATH . '/Views/public/partials-pagination.php'; ?>
