<section class="section section-alt">
    <div class="container">
        <h1 class="section-title"><?= e(ui('Événements')) ?></h1>
        <p class="section-lead"><?= e(ui('Ateliers, rencontres et initiatives pour apprendre, contribuer et partager la connaissance libre.')) ?></p>
        <form class="filters" method="get">
            <label class="sr-only" for="event_q"><?= e(ui('Rechercher un événement')) ?></label>
            <input class="form-control" id="event_q" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="<?= e(ui('Rechercher un événement')) ?>">
            <label class="sr-only" for="event_category"><?= e(ui('Catégorie')) ?></label>
            <select class="form-control" id="event_category" name="category_id">
                <option value=""><?= e(ui('Toutes les catégories')) ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"<?= selected((string) ($filters['category_id'] ?? '') === (string) $category['id']) ?>><?= e(localized($category, 'name')) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="sr-only" for="event_location"><?= e(ui('Lieu')) ?></label>
            <input class="form-control" id="event_location" name="location" value="<?= e($filters['location'] ?? '') ?>" placeholder="<?= e(ui('Lieu')) ?>">
            <label class="sr-only" for="event_period"><?= e(ui('Période')) ?></label>
            <select class="form-control" id="event_period" name="period">
                <option value="upcoming"<?= selected(($filters['period'] ?? '') === 'upcoming') ?>><?= e(ui('À venir')) ?></option>
                <option value="past"<?= selected(($filters['period'] ?? '') === 'past') ?>><?= e(ui('Passés')) ?></option>
                <option value=""<?= selected(($filters['period'] ?? '') === '') ?>><?= e(ui('Tous')) ?></option>
            </select>
            <button class="btn btn-primary" type="submit"><?= icon('search') ?> <?= e(trans('filter')) ?></button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container" data-view-wrapper data-current-view="grid">
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:18px">
            <button class="btn btn-small btn-ghost is-active" type="button" data-view="grid" aria-pressed="true"><?= e(ui('Grille')) ?></button>
            <button class="btn btn-small btn-ghost" type="button" data-view="list" aria-pressed="false"><?= e(ui('Liste')) ?></button>
        </div>
        <?php if ($result['items']): ?>
            <div class="card-grid">
                <?php foreach ($result['items'] as $event): ?>
                    <article class="event-card">
                        <?php if ($event['cover_image']): ?><img src="<?= e(asset($event['cover_image'])) ?>" alt="<?= e(localized($event, 'title')) ?>" loading="lazy"><?php endif; ?>
                        <div class="card-body">
                            <div class="meta-row">
                                <span class="badge badge-red"><?= e(format_date($event['start_at'])) ?></span>
                                <span><?= e($event['location']) ?></span>
                            </div>
                            <h3><?= e(localized($event, 'title')) ?></h3>
                            <p><?= e(localized($event, 'short_description')) ?></p>
                            <a class="btn btn-small btn-outline" href="<?= e(url('/evenements/' . $event['slug'])) ?>"><?= e(ui('Détails')) ?> <?= icon('arrow-right') ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php require APP_PATH . '/Views/public/partials-pagination.php'; ?>
        <?php else: ?>
            <div class="empty-state"><?= e(trans('no_results')) ?></div>
        <?php endif; ?>
    </div>
</section>
