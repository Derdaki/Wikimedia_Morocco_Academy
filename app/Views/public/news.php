<section class="section section-alt">
    <div class="container">
        <h1 class="section-title"><?= e(ui('Actualités')) ?></h1>
        <p class="section-lead"><?= e(ui('Suivez les nouvelles de la communauté, des projets et des campagnes de contribution.')) ?></p>
        <form class="filters" method="get" style="grid-template-columns:2fr 1fr auto">
            <label class="sr-only" for="news_q"><?= e(ui('Rechercher une actualité')) ?></label>
            <input class="form-control" id="news_q" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="<?= e(ui('Rechercher une actualité')) ?>">
            <label class="sr-only" for="news_category"><?= e(ui('Catégorie')) ?></label>
            <select class="form-control" id="news_category" name="category_id">
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
                <?php foreach ($result['items'] as $article): ?>
                    <article class="content-card">
                        <?php if ($article['featured_image']): ?><img src="<?= e(asset($article['featured_image'])) ?>" alt="<?= e(localized($article, 'title')) ?>"><?php endif; ?>
                        <div class="card-body">
                            <div class="meta-row"><span><?= e(format_date($article['published_at'])) ?></span></div>
                            <h3><?= e(localized($article, 'title')) ?></h3>
                            <p><?= e(localized($article, 'excerpt', excerpt((string) $article['content_fr']))) ?></p>
                            <a class="btn btn-small btn-outline" href="<?= e(url('/actualites/' . $article['slug'])) ?>"><?= e(trans('read_more')) ?></a>
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
