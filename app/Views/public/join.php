<section class="section section-alt">
    <div class="container">
        <h1 class="section-title"><?= e(ui('Rejoindre la communauté')) ?></h1>
        <p class="section-lead"><?= e(ui('Contribuez à Wikipédia, Commons, Wikidata et aux projets qui rendent le savoir marocain plus accessible.')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="mission-grid">
            <div class="mission-card">
                <span class="mission-icon"><?= icon('edit') ?></span>
                <span><h3><?= e(ui('Contribuer')) ?></h3><p><?= e(ui('Améliorez des articles, ajoutez des sources fiables et documentez des sujets marocains.')) ?></p></span>
            </div>
            <div class="mission-card">
                <span class="mission-icon"><?= icon('camera') ?></span>
                <span><h3><?= e(ui('Photographier')) ?></h3><p><?= e(ui('Partagez des images libres de monuments, paysages, archives et savoir-faire.')) ?></p></span>
            </div>
            <div class="mission-card">
                <span class="mission-icon"><?= icon('graduation-cap') ?></span>
                <span><h3><?= e(ui('Former')) ?></h3><p><?= e(ui('Aidez de nouveaux contributeurs à apprendre les bonnes pratiques de contribution.')) ?></p></span>
            </div>
        </div>
        <p style="text-align:center;margin-top:30px">
            <a class="btn btn-primary" href="<?= e(asset(setting('join_url') ?: '/contact')) ?>" target="_blank" rel="noopener"><?= e(ui('Commencer')) ?> <?= icon('external-link') ?></a>
        </p>
    </div>
</section>
