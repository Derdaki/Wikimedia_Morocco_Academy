<section class="section section-alt">
    <div class="container">
        <h1 class="section-title"><?= e(ui('Ressources')) ?></h1>
        <p class="section-lead"><?= e(ui('Guides, documents et liens utiles pour apprendre et contribuer.')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($resources): ?>
            <div class="card-grid">
                <?php foreach ($resources as $resource): ?>
                    <article class="content-card">
                        <div class="card-body">
                            <span class="badge"><?= e($resource['resource_type']) ?></span>
                            <h3><?= e(localized($resource, 'title')) ?></h3>
                            <p><?= e(localized($resource, 'description')) ?></p>
                            <?php if ($resource['file_url']): ?>
                                <a class="btn btn-small btn-outline" href="<?= e(asset($resource['file_url'])) ?>" target="_blank" rel="noopener"><?= icon('download') ?> <?= e(ui('Télécharger')) ?></a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('Les ressources publiées apparaîtront ici.')) ?></div>
        <?php endif; ?>
    </div>
</section>
