<section class="section section-alt">
    <div class="container">
        <h1 class="section-title"><?= e(ui('Nos projets')) ?></h1>
        <p class="section-lead"><?= e(ui('Découvrez les actions menées pour documenter le Maroc et soutenir les projets Wikimedia.')) ?></p>
        <form class="filters" method="get" style="grid-template-columns:2fr 1fr auto">
            <label class="sr-only" for="project_q"><?= e(ui('Rechercher un projet')) ?></label>
            <input class="form-control" id="project_q" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="<?= e(ui('Rechercher un projet')) ?>">
            <label class="sr-only" for="project_category"><?= e(ui('Catégorie')) ?></label>
            <select class="form-control" id="project_category" name="category_id">
                <option value=""><?= e(ui('Toutes les catégories')) ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>"<?= selected((string) ($filters['category_id'] ?? '') === (string) $category['id']) ?>><?= e(localized($category, 'name')) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit"><?= icon('search') ?> <?= e(trans('filter')) ?></button>
        </form>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($result['items']): ?>
            <div class="card-grid">
                <?php foreach ($result['items'] as $project): ?>
                    <article class="content-card">
                        <?php if ($project['cover_image']): ?><img src="<?= e(asset($project['cover_image'])) ?>" alt="<?= e(localized($project, 'title')) ?>"><?php endif; ?>
                        <div class="card-body">
                            <div class="meta-row"><span class="badge badge-green"><?= e(ui($project['project_status'])) ?></span></div>
                            <h3><?= e(localized($project, 'title')) ?></h3>
                            <p><?= e(localized($project, 'excerpt', excerpt((string) $project['content_fr']))) ?></p>
                            <a class="btn btn-small btn-outline" href="<?= e(url('/nos-projets/' . $project['slug'])) ?>"><?= e(trans('learn_more')) ?></a>
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
