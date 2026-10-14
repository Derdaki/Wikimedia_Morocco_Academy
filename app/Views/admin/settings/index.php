<form class="stack" method="post" action="<?= e(url('/admin/settings')) ?>">
    <?= csrf_field() ?>
    <div class="two-col">
        <section class="admin-card stack">
            <h2 style="margin:0"><?= e(ui('Identity')) ?></h2>
            <div>
                <label class="form-label" for="site_name"><?= e(ui('Website name')) ?></label>
                <input class="form-control" id="site_name" name="site_name" value="<?= e($settings['site_name'] ?? 'Wikimedia Maroc') ?>">
            </div>
            <div>
                <label class="form-label" for="site_logo"><?= e(ui('Website logo URL')) ?></label>
                <input class="form-control" id="site_logo" name="site_logo" value="<?= e($settings['site_logo'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label" for="site_description"><?= e(ui('Site description')) ?></label>
                <textarea class="form-control" id="site_description" name="site_description" rows="3"><?= e($settings['site_description'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="form-label" for="footer_description"><?= e(ui('Footer description')) ?></label>
                <textarea class="form-control" id="footer_description" name="footer_description" rows="4"><?= e($settings['footer_description'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="form-label" for="contact_email"><?= e(ui('Contact email')) ?></label>
                <input class="form-control" id="contact_email" name="contact_email" type="email" value="<?= e($settings['contact_email'] ?? '') ?>">
            </div>
            <div><label class="form-label" for="site_description_ar">وصف الموقع بالعربية</label><textarea class="form-control" id="site_description_ar" name="site_description_ar" dir="rtl" rows="3"><?= e($settings['site_description_ar'] ?? '') ?></textarea></div>
            <div><label class="form-label" for="footer_description_ar">وصف التذييل بالعربية</label><textarea class="form-control" id="footer_description_ar" name="footer_description_ar" dir="rtl" rows="3"><?= e($settings['footer_description_ar'] ?? '') ?></textarea></div>
        </section>
        <section class="admin-card stack">
            <h2 style="margin:0"><?= e(ui('Links and homepage')) ?></h2>
            <?php foreach (['facebook_url', 'x_url', 'instagram_url', 'linkedin_url', 'youtube_url', 'join_url'] as $key): ?>
                <div>
                    <label class="form-label" for="<?= e($key) ?>"><?= e(str_replace('_', ' ', $key)) ?></label>
                    <input class="form-control" id="<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($settings[$key] ?? '') ?>">
                </div>
            <?php endforeach; ?>
            <div>
                <label class="form-label" for="home_hero_images"><?= e(ui('Hero images')) ?></label>
                <textarea class="form-control" id="home_hero_images" name="home_hero_images" rows="6"><?= e(implode("\n", json_decode($settings['home_hero_images'] ?? '[]', true) ?: [])) ?></textarea>
                <p class="form-help"><?= e(ui('One image URL per line.')) ?></p>
            </div>
            <button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= e(ui('Save settings')) ?></button>
        </section>
    </div>
</form>
