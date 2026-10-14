<section class="section section-alt">
    <div class="container two-col">
        <div>
            <span class="badge badge-green"><?= e(ui($project['project_status'])) ?></span>
            <h1 class="section-title" style="text-align:start;margin-top:14px"><?= e(localized($project, 'title')) ?></h1>
            <p class="section-lead" style="text-align:start;margin-inline:0"><?= e(localized($project, 'excerpt')) ?></p>
        </div>
        <?php if ($project['cover_image']): ?><img style="border-radius:18px" src="<?= e(asset($project['cover_image'])) ?>" alt="<?= e(localized($project, 'title')) ?>"><?php endif; ?>
    </div>
</section>
<section class="section">
    <div class="container prose"><?= nl2br(e(localized($project, 'content'))) ?></div>
</section>
