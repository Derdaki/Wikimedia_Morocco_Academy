<?php
$errors = $_SESSION['_errors'] ?? [];
unset($_SESSION['_errors']);
$value = static fn (string $key, mixed $fallback = '') => old($key, $event[$key] ?? $fallback);
$dateValue = static function (?string $date): string {
    return $date ? date('Y-m-d\TH:i', strtotime($date)) : '';
};
?>
<form class="stack" method="post" enctype="multipart/form-data" action="<?= e($event ? url('/admin/events/' . $event['id']) : url('/admin/events')) ?>">
    <?= csrf_field() ?>
    <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e(ui($error)) ?></p><?php endforeach; ?></div><?php endif; ?>
    <div class="two-col">
        <section class="admin-card stack">
            <h2 style="margin:0"><?= e(ui('Content')) ?></h2>
            <div>
                <label class="form-label" for="title_fr"><?= e(ui('Title FR')) ?></label>
                <input class="form-control" id="title_fr" name="title_fr" value="<?= e($value('title_fr')) ?>" data-slug-source="#slug" required>
                <?php if (isset($errors['title_fr'])): ?><div class="form-error"><?= e(ui($errors['title_fr'])) ?></div><?php endif; ?>
            </div>
            <div>
                <label class="form-label" for="title_ar"><?= e(ui('Title AR')) ?></label>
                <input class="form-control" id="title_ar" name="title_ar" value="<?= e($value('title_ar')) ?>">
            </div>
            <div>
                <label class="form-label" for="slug"><?= e(ui('Slug')) ?></label>
                <input class="form-control" id="slug" name="slug" value="<?= e($value('slug')) ?>" required>
                <?php if (isset($errors['slug'])): ?><div class="form-error"><?= e(ui($errors['slug'])) ?></div><?php endif; ?>
            </div>
            <div>
                <label class="form-label" for="short_description_fr"><?= e(ui('Short description FR')) ?></label>
                <textarea class="form-control" id="short_description_fr" name="short_description_fr" rows="3" required><?= e($value('short_description_fr')) ?></textarea>
            </div>
            <div>
                <label class="form-label" for="short_description_ar"><?= e(ui('Short description AR')) ?></label>
                <textarea class="form-control" id="short_description_ar" name="short_description_ar" rows="3"><?= e($value('short_description_ar')) ?></textarea>
            </div>
            <div>
                <label class="form-label" for="description_fr"><?= e(ui('Full description FR')) ?></label>
                <textarea class="form-control" id="description_fr" name="description_fr" rows="8" required><?= e($value('description_fr')) ?></textarea>
            </div>
            <div>
                <label class="form-label" for="description_ar"><?= e(ui('Full description AR')) ?></label>
                <textarea class="form-control" id="description_ar" name="description_ar" rows="8"><?= e($value('description_ar')) ?></textarea>
            </div>
        </section>
        <section class="admin-card stack">
            <h2 style="margin:0"><?= e(ui('Event details')) ?></h2>
            <div>
                <label class="form-label" for="category_id"><?= e(ui('Category')) ?></label>
                <select class="form-control" id="category_id" name="category_id">
                    <option value=""><?= e(ui('No category')) ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"<?= selected((string) $value('category_id') === (string) $category['id']) ?>><?= e($category['name_fr']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label" for="cover_image"><?= e(ui('Cover image URL or path')) ?></label>
                <input class="form-control" id="cover_image" name="cover_image" value="<?= e($value('cover_image')) ?>">
            </div>
            <div>
                <label class="form-label" for="cover_upload"><?= e(ui('Upload cover')) ?></label>
                <input class="form-control" id="cover_upload" name="cover_upload" type="file" accept="image/*" data-preview-input="#cover_preview">
                <img id="cover_preview" class="thumbnail" hidden alt="" style="margin-top:10px;width:160px;height:110px">
            </div>
            <div class="two-col">
                <div>
                    <label class="form-label" for="start_at"><?= e(ui('Start')) ?></label>
                    <input class="form-control" id="start_at" name="start_at" type="datetime-local" value="<?= e(old('start_at', $dateValue($event['start_at'] ?? null))) ?>" required>
                </div>
                <div>
                    <label class="form-label" for="end_at"><?= e(ui('End')) ?></label>
                    <input class="form-control" id="end_at" name="end_at" type="datetime-local" value="<?= e(old('end_at', $dateValue($event['end_at'] ?? null))) ?>" required>
                </div>
            </div>
            <div>
                <label class="form-label" for="location"><?= e(ui('Location')) ?></label>
                <input class="form-control" id="location" name="location" value="<?= e($value('location')) ?>" required>
            </div>
            <div>
                <label class="form-label" for="organizer"><?= e(ui('Organizer')) ?></label>
                <input class="form-control" id="organizer" name="organizer" value="<?= e($value('organizer', 'Wikimedia Maroc')) ?>" required>
            </div>
            <div>
                <label class="form-label" for="registration_url"><?= e(ui('Registration URL')) ?></label>
                <input class="form-control" id="registration_url" name="registration_url" value="<?= e($value('registration_url')) ?>">
            </div>
            <div>
                <label class="form-label" for="status"><?= e(ui('Status')) ?></label>
                <select class="form-control" id="status" name="status">
                    <?php foreach (['draft', 'published', 'archived', 'cancelled'] as $status): ?>
                        <option value="<?= e($status) ?>"<?= selected($value('status', 'draft') === $status) ?>><?= e(ui($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= e(ui('Save Event')) ?></button>
        </section>
    </div>
</form>
<?php clear_old(); ?>
