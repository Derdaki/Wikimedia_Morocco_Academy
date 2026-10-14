<section class="hero">
    <div class="container hero-grid">
        <div>
            <h1><?= e(ui('La connaissance libre, au cœur du Maroc.')) ?></h1>
            <p><?= e(ui('Ensemble, partageons le patrimoine, la culture et le savoir du Maroc avec le monde.')) ?></p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(url('/nos-projets')) ?>"><?= e(trans('discover_projects')) ?> <?= icon('arrow-right') ?></a>
                <a class="btn btn-outline" href="<?= e(url('/rejoindre-la-communaute')) ?>"><?= e(trans('join')) ?></a>
            </div>
        </div>
        <div class="hero-collage" aria-label="<?= e(ui('Patrimoine culturel marocain')) ?>">
            <?php foreach (array_slice($heroImages, 0, 4) as $image): ?>
                <figure><img src="<?= e(asset($image)) ?>" alt="<?= e(ui('Patrimoine du Maroc')) ?>" fetchpriority="high"></figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title"><?= e(ui('Agir pour la connaissance libre')) ?></h2>
        <div class="title-rule"></div>
        <div class="mission-grid">
            <a class="mission-card" href="<?= e(url('/rejoindre-la-communaute')) ?>">
                <span class="mission-icon"><?= icon('wikipedia') ?></span>
                <span>
                    <h3><?= e(ui('Contribuer à Wikipédia')) ?></h3>
                    <p><?= e(ui('Écrivez, améliorez et enrichissez les articles sur le Maroc et bien plus encore.')) ?></p>
                </span>
                <?= icon('arrow-right') ?>
            </a>
            <a class="mission-card" href="<?= e(url('/nos-projets')) ?>">
                <span class="mission-icon"><?= icon('home') ?></span>
                <span>
                    <h3><?= e(ui('Valoriser le patrimoine')) ?></h3>
                    <p><?= e(ui('Mettez en lumière l’histoire, la culture et la diversité du patrimoine marocain.')) ?></p>
                </span>
                <?= icon('arrow-right') ?>
            </a>
            <a class="mission-card" href="<?= e(url('/evenements')) ?>">
                <span class="mission-icon"><?= icon('graduation-cap') ?></span>
                <span>
                    <h3><?= e(ui('Apprendre et partager')) ?></h3>
                    <p><?= e(ui('Participez à nos ateliers, formations et rencontres avec la communauté.')) ?></p>
                </span>
                <?= icon('arrow-right') ?>
            </a>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-headline-row">
            <h2 class="section-title"><?= e(ui('Projets en avant')) ?></h2>
            <a class="btn btn-outline" href="<?= e(url('/nos-projets')) ?>"><?= e(ui('Tous les projets')) ?> <?= icon('arrow-right') ?></a>
        </div>
        <?php if ($featuredProjects): ?>
            <div class="card-grid">
                <?php foreach ($featuredProjects as $project): ?>
                    <article class="content-card">
                        <?php if ($project['cover_image']): ?><img src="<?= e(asset($project['cover_image'])) ?>" alt="<?= e(localized($project, 'title')) ?>" loading="lazy"><?php endif; ?>
                        <div class="card-body">
                            <div class="meta-row">
                                <?php if (!empty($project['category_fr'])): ?><span class="badge badge-green"><?= e(localized($project, 'category')) ?></span><?php endif; ?>
                                <span class="badge"><?= e(ui($project['project_status'])) ?></span>
                            </div>
                            <h3><?= e(localized($project, 'title')) ?></h3>
                            <p><?= e(localized($project, 'excerpt', excerpt((string) $project['content_fr']))) ?></p>
                            <a class="btn btn-small btn-outline" href="<?= e(url('/nos-projets/' . $project['slug'])) ?>"><?= e(trans('learn_more')) ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('Les projets publiés apparaîtront ici dès qu’ils seront ajoutés depuis le tableau de bord.')) ?></div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-headline-row">
            <h2 class="section-title"><?= e(ui('À la une')) ?></h2>
            <a class="btn btn-outline" href="<?= e(url('/evenements')) ?>"><?= e(trans('all_events')) ?> <?= icon('arrow-right') ?></a>
        </div>
        <?php if ($events): ?>
            <div class="card-grid">
                <?php foreach ($events as $event): ?>
                    <article class="event-card">
                        <?php if ($event['cover_image']): ?><img src="<?= e(asset($event['cover_image'])) ?>" alt="<?= e(localized($event, 'title')) ?>" loading="lazy"><?php endif; ?>
                        <div class="card-body">
                            <div class="meta-row">
                                <span class="badge badge-red"><?= icon('calendar') ?> <?= e(format_date($event['start_at'])) ?></span>
                                <span><?= e($event['location']) ?></span>
                            </div>
                            <h3><?= e(localized($event, 'title')) ?></h3>
                            <p><?= e(localized($event, 'short_description')) ?></p>
                            <a class="btn btn-small btn-outline" href="<?= e(url('/evenements/' . $event['slug'])) ?>"><?= e(ui('Détails')) ?> <?= icon('arrow-right') ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('Aucun événement à venir n’est publié pour le moment.')) ?></div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <h2 class="section-title"><?= e(ui('Dernières actualités')) ?></h2>
        <div class="title-rule"></div>
        <?php if ($news): ?>
            <div class="card-grid">
                <?php foreach ($news as $article): ?>
                    <article class="content-card">
                        <?php if ($article['featured_image']): ?><img src="<?= e(asset($article['featured_image'])) ?>" alt="<?= e(localized($article, 'title')) ?>" loading="lazy"><?php endif; ?>
                        <div class="card-body">
                            <div class="meta-row">
                                <span><?= e(format_date($article['published_at'])) ?></span>
                                <?php if (!empty($article['category_fr'])): ?><span class="badge"><?= e(localized($article, 'category')) ?></span><?php endif; ?>
                            </div>
                            <h3><?= e(localized($article, 'title')) ?></h3>
                            <p><?= e(localized($article, 'excerpt', excerpt((string) $article['content_fr']))) ?></p>
                            <a class="btn btn-small btn-outline" href="<?= e(url('/actualites/' . $article['slug'])) ?>"><?= e(trans('read_more')) ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('Les actualités publiées apparaîtront ici.')) ?></div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-band">
            <div>
                <h2><?= e(ui('Construisons une mémoire ouverte du Maroc.')) ?></h2>
                <p><?= e(ui('Participez aux ateliers, documentez le patrimoine et aidez de nouveaux contributeurs à rejoindre les projets Wikimedia.')) ?></p>
            </div>
            <a class="btn btn-primary" href="<?= e(url('/rejoindre-la-communaute')) ?>"><?= e(trans('join')) ?></a>
        </div>
    </div>
</section>
