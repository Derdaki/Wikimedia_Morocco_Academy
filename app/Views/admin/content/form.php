<?php
$errors = $_SESSION['_errors'] ?? [];
unset($_SESSION['_errors']);
$value = static fn (string $key, mixed $fallback = '') => old($key, $item[$key] ?? $fallback);
$dateValue = static function (?string $date): string {
    return $date ? date('Y-m-d\TH:i', strtotime($date)) : date('Y-m-d\TH:i');
};
$action = $item ? url('/admin/' . $type . '/' . $item['id']) : url('/admin/' . $type);
?>
<form class="stack" method="post" enctype="multipart/form-data" action="<?= e($action) ?>">
    <?= csrf_field() ?>
    <?php if ($errors): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= e(ui($error)) ?></p><?php endforeach; ?></div><?php endif; ?>
    <div class="two-col">
        <section class="admin-card stack">
            <h2 style="margin:0"><?= e(ui($config['singular'])) ?></h2>

            <?php if ($type === 'team'): ?>
                <div>
                    <label class="form-label" for="name"><?= e(ui('Name')) ?></label>
                    <input class="form-control" id="name" name="name" value="<?= e($value('name')) ?>" data-slug-source="#slug" required>
                </div>
                <div>
                    <label class="form-label" for="slug"><?= e(ui('Slug')) ?></label>
                    <input class="form-control" id="slug" name="slug" value="<?= e($value('slug')) ?>" required>
                </div>
                <div>
                    <label class="form-label" for="role_fr"><?= e(ui('Role FR')) ?></label>
                    <input class="form-control" id="role_fr" name="role_fr" value="<?= e($value('role_fr')) ?>" required>
                </div>
                <div>
                    <label class="form-label" for="role_ar"><?= e(ui('Role AR')) ?></label>
                    <input class="form-control" id="role_ar" name="role_ar" value="<?= e($value('role_ar')) ?>">
                </div>
                <div>
                    <label class="form-label" for="bio_fr"><?= e(ui('Biography FR')) ?></label>
                    <textarea class="form-control" id="bio_fr" name="bio_fr" rows="6"><?= e($value('bio_fr')) ?></textarea>
                </div>
                <div>
                    <label class="form-label" for="bio_ar"><?= e(ui('Biography AR')) ?></label>
                    <textarea class="form-control" id="bio_ar" name="bio_ar" rows="6"><?= e($value('bio_ar')) ?></textarea>
                </div>
            <?php else: ?>
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
                </div>
                <?php if ($categories): ?>
                    <div>
                        <label class="form-label" for="category_id"><?= e(ui('Category')) ?></label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value=""><?= e(ui('No category')) ?></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>"<?= selected((string) $value('category_id') === (string) $category['id']) ?>><?= e($category['name_fr']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <?php if ($type !== 'resources'): ?>
                    <div>
                        <label class="form-label" for="excerpt_fr"><?= e(ui('Excerpt FR')) ?></label>
                        <textarea class="form-control" id="excerpt_fr" name="excerpt_fr" rows="3"><?= e($value('excerpt_fr')) ?></textarea>
                    </div>
                    <div>
                        <label class="form-label" for="excerpt_ar"><?= e(ui('Excerpt AR')) ?></label>
                        <textarea class="form-control" id="excerpt_ar" name="excerpt_ar" rows="3"><?= e($value('excerpt_ar')) ?></textarea>
                    </div>
                    <div>
                        <label class="form-label" for="content_fr"><?= e(ui('Content FR')) ?></label>
                        <textarea class="form-control" id="content_fr" name="content_fr" rows="8"><?= e($value('content_fr')) ?></textarea>
                    </div>
                    <div>
                        <label class="form-label" for="content_ar"><?= e(ui('Content AR')) ?></label>
                        <textarea class="form-control" id="content_ar" name="content_ar" rows="8"><?= e($value('content_ar')) ?></textarea>
                    </div>
                <?php else: ?>
                    <div>
                        <label class="form-label" for="description_fr"><?= e(ui('Description FR')) ?></label>
                        <textarea class="form-control" id="description_fr" name="description_fr" rows="5"><?= e($value('description_fr')) ?></textarea>
                    </div>
                    <div>
                        <label class="form-label" for="description_ar"><?= e(ui('Description AR')) ?></label>
                        <textarea class="form-control" id="description_ar" name="description_ar" rows="5"><?= e($value('description_ar')) ?></textarea>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <section class="admin-card stack">
            <h2 style="margin:0"><?= e(ui('Publishing')) ?></h2>
            <div>
                <label class="form-label" for="image_path"><?= e(ui('Image or file URL/path')) ?></label>
                <?php $imageField = $type === 'projects' ? 'cover_image' : ($type === 'news' ? 'featured_image' : ($type === 'team' ? 'photo' : 'file_url')); ?>
                <input class="form-control" id="image_path" name="<?= $type === 'resources' ? 'file_url' : 'image_path' ?>" value="<?= e(old($type === 'resources' ? 'file_url' : 'image_path', $item[$imageField] ?? '')) ?>">
            </div>
            <div>
                <label class="form-label" for="image_upload"><?= $type === 'resources' ? 'Upload file' : 'Upload image' ?></label>
                <input class="form-control" id="image_upload" name="image_upload" type="file" <?= $type === 'resources' ? '' : 'accept="image/*"' ?> data-preview-input="#image_preview">
                <img id="image_preview" class="thumbnail" hidden alt="" style="margin-top:10px;width:160px;height:110px">
            </div>

            <?php if ($type === 'projects'): ?>
                <label class="form-label" for="project_status"><?= e(ui('Project status')) ?></label>
                <select class="form-control" id="project_status" name="project_status">
                    <option value="active"<?= selected($value('project_status', 'active') === 'active') ?>><?= e(ui('Active')) ?></option>
                    <option value="completed"<?= selected($value('project_status') === 'completed') ?>><?= e(ui('Completed')) ?></option>
                </select>
                <label><input type="checkbox" name="is_featured"<?= checked((bool) $value('is_featured')) ?>> <?= e(ui('Featured project')) ?></label>
            <?php endif; ?>

            <?php if ($type === 'news'): ?>
                <div>
                    <label class="form-label" for="author"><?= e(ui('Author')) ?></label>
                    <input class="form-control" id="author" name="author" value="<?= e($value('author', 'Wikimedia Maroc')) ?>">
                </div>
                <div>
                    <label class="form-label" for="published_at"><?= e(ui('Publication date')) ?></label>
                    <input class="form-control" id="published_at" name="published_at" type="datetime-local" value="<?= e(old('published_at', $dateValue($item['published_at'] ?? null))) ?>">
                </div>
            <?php endif; ?>

            <?php if ($type === 'resources'): ?>
                <div>
                    <label class="form-label" for="resource_type"><?= e(ui('Resource type')) ?></label>
                    <select class="form-control" id="resource_type" name="resource_type">
                        <?php foreach (['link', 'pdf', 'guide', 'document'] as $resourceType): ?>
                            <option value="<?= e(ui($resourceType)) ?>"<?= selected($value('resource_type', 'link') === $resourceType) ?>><?= e(ui($resourceType)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($type === 'team'): ?>
                <label><input type="checkbox" name="is_visible"<?= checked((bool) $value('is_visible', true)) ?>> <?= e(ui('Public visibility')) ?></label>
            <?php else: ?>
                <div>
                    <label class="form-label" for="status"><?= e(ui('Status')) ?></label>
                    <select class="form-control" id="status" name="status">
                        <option value="draft"<?= selected($value('status', 'draft') === 'draft') ?>><?= e(ui('Draft')) ?></option>
                        <option value="published"<?= selected($value('status') === 'published') ?>><?= e(ui('Published')) ?></option>
                    </select>
                </div>
            <?php endif; ?>
            <div>
                <label class="form-label" for="sort_order"><?= e(ui('Sort order')) ?></label>
                <input class="form-control" id="sort_order" name="sort_order" type="number" value="<?= e($value('sort_order', 0)) ?>">
            </div>
            <button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= e(ui('Save')) ?></button>
        </section>
    </div>
</form>
<?php clear_old(); ?>
