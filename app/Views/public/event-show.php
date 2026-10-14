<section class="section section-alt">
    <div class="container two-col">
        <div>
            <span class="badge badge-red"><?= e(localized($event, 'category', 'Événement')) ?></span>
            <h1 class="section-title" style="text-align:start;margin-top:14px"><?= e(localized($event, 'title')) ?></h1>
            <p class="section-lead" style="text-align:start;margin-inline:0"><?= e(localized($event, 'short_description')) ?></p>
            <div class="stack" style="margin-top:24px">
                <div><strong><?= e(ui('Date')) ?></strong><br><?= e(format_date($event['start_at'], 'd M Y H:i')) ?> <?= e(ui('à')) ?> <?= e(format_date($event['end_at'], 'd M Y H:i')) ?></div>
                <div><strong><?= e(ui('Lieu')) ?></strong><br><?= e($event['location']) ?></div>
                <div><strong><?= e(ui('Organisateur')) ?></strong><br><?= e($event['organizer']) ?></div>
            </div>
            <?php if ($event['status'] !== 'cancelled' && $event['registration_url'] && safe_url($event['registration_url'])): ?>
                <p><a class="btn btn-primary" href="<?= e(asset($event['registration_url'])) ?>" target="_blank" rel="noopener"><?= e(ui('Inscription')) ?> <?= icon('external-link') ?></a></p>
            <?php endif; ?>
            <?php if ($event['status'] === 'cancelled'): ?><p class="alert alert-error"><?= e(ui('cancelled')) ?></p><?php endif; ?>
        </div>
        <?php if ($event['cover_image']): ?>
            <img style="border-radius:18px;box-shadow:0 18px 45px rgba(20,24,39,.12)" src="<?= e(asset($event['cover_image'])) ?>" alt="<?= e(localized($event, 'title')) ?>">
        <?php endif; ?>
    </div>
</section>
<section class="section">
    <div class="container prose">
        <?= nl2br(e(localized($event, 'description'))) ?>
    </div>
</section>
<?php if ($related): ?>
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title"><?= e(ui('Événements liés')) ?></h2>
        <div class="card-grid">
            <?php foreach ($related as $item): ?>
                <article class="event-card">
                    <?php if ($item['cover_image']): ?><img src="<?= e(asset($item['cover_image'])) ?>" alt="<?= e(localized($item, 'title')) ?>"><?php endif; ?>
                    <div class="card-body">
                        <h3><?= e(localized($item, 'title')) ?></h3>
                        <p><?= e(format_date($item['start_at'])) ?>, <?= e($item['location']) ?></p>
                        <a class="btn btn-small btn-outline" href="<?= e(url('/evenements/' . $item['slug'])) ?>"><?= e(ui('Détails')) ?></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
