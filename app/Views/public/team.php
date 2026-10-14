<section class="section section-alt">
    <div class="container">
        <h1 class="section-title"><?= e(ui('Notre équipe')) ?></h1>
        <p class="section-lead"><?= e(ui('Les bénévoles et responsables qui accompagnent les activités de Wikimedia Maroc.')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($members): ?>
            <div class="card-grid">
                <?php foreach ($members as $member): ?>
                    <article class="content-card">
                        <?php if ($member['photo']): ?><img src="<?= e(asset($member['photo'])) ?>" alt="<?= e($member['name']) ?>"><?php endif; ?>
                        <div class="card-body">
                            <h3><?= e($member['name']) ?></h3>
                            <p><strong><?= e(localized($member, 'role')) ?></strong></p>
                            <p><?= e(localized($member, 'bio')) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('Les membres visibles apparaîtront ici.')) ?></div>
        <?php endif; ?>
    </div>
</section>
