<section class="admin-card" style="margin-bottom:22px">
    <h2 style="margin-top:0"><?= e(ui('Upload media')) ?></h2>
    <form class="filters" method="post" enctype="multipart/form-data" action="<?= e(url('/admin/media')) ?>" style="grid-template-columns:2fr 1fr 1fr 1fr auto">
        <?= csrf_field() ?>
        <label class="sr-only" for="media_file"><?= e(ui('File')) ?></label>
        <input class="form-control" id="media_file" type="file" name="file" required>
        <label class="sr-only" for="media_alt_text"><?= e(ui('Alt text')) ?></label>
        <input class="form-control" id="media_alt_text" name="alt_text" placeholder="<?= e(ui('Alt text')) ?>">
        <label class="sr-only" for="media_attribution"><?= e(ui('Attribution')) ?></label>
        <input class="form-control" id="media_attribution" name="attribution" placeholder="<?= e(ui('Attribution')) ?>">
        <label class="sr-only" for="media_license"><?= e(ui('License')) ?></label>
        <input class="form-control" id="media_license" name="license" placeholder="<?= e(ui('License')) ?>">
        <button class="btn btn-primary" type="submit"><?= icon('plus') ?> <?= e(ui('Upload')) ?></button>
    </form>
</section>

<form method="get" style="display:flex;gap:10px;margin-bottom:18px;max-width:460px">
    <label class="sr-only" for="media_q"><?= e(ui('Search media')) ?></label>
    <input class="form-control" id="media_q" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e(ui('Search media')) ?>">
    <button class="btn btn-primary" type="submit"><?= icon('search') ?></button>
</form>

<?php if ($media): ?>
    <div class="media-grid">
        <?php foreach ($media as $item): ?>
            <article class="media-card">
                <?php if (str_starts_with((string) $item['mime_type'], 'image/')): ?>
                    <img src="<?= e(asset($item['path'])) ?>" alt="<?= e($item['alt_text'] ?: $item['original_name']) ?>">
                <?php else: ?>
                    <div class="empty-state"><?= icon('file-text') ?> <?= e(ui('File')) ?></div>
                <?php endif; ?>
                <div>
                    <strong><?= e($item['original_name']) ?></strong>
                    <p style="color:#667085;word-break:break-all"><?= e($item['path']) ?></p>
                    <p><?= e($item['license'] ?? '') ?></p>
                    <form method="post" action="<?= e(url('/admin/media/' . $item['id'] . '/delete')) ?>" data-confirm="<?= e(ui('Delete this media file?')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-small btn-ghost" type="submit"><?= icon('trash') ?> <?= e(ui('Delete')) ?></button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state"><?= e(ui('No media files yet.')) ?></div>
<?php endif; ?>
