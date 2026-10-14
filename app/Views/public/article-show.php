<section class="section section-alt">
    <div class="container prose">
        <span class="badge"><?= e(format_date($article['published_at'])) ?></span>
        <h1 class="section-title" style="text-align:start;margin-top:14px"><?= e(localized($article, 'title')) ?></h1>
        <p><?= e(localized($article, 'excerpt')) ?></p>
    </div>
</section>
<?php if ($article['featured_image']): ?>
    <div class="container"><img style="border-radius:18px;margin-top:32px" src="<?= e(asset($article['featured_image'])) ?>" alt="<?= e(localized($article, 'title')) ?>"></div>
<?php endif; ?>
<section class="section">
    <div class="container prose"><?= nl2br(e(localized($article, 'content'))) ?></div>
</section>
