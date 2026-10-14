<div style="display:flex;justify-content:space-between;gap:16px;align-items:center">
    <div>
        <h2 style="margin:0"><?= e(ui('Upcoming events')) ?></h2>
        <p style="margin:6px 0 0;color:#667085"><?= e(ui('The next four published events are shown as quick cards.')) ?></p>
    </div>
    <a class="btn btn-primary" href="<?= e(url('/admin/events/create')) ?>"><?= icon('plus') ?> <?= e(ui('Create Event')) ?></a>
</div>

<?php if ($upcoming): ?>
    <div class="admin-event-cards">
        <?php foreach ($upcoming as $event): ?>
            <a class="event-mini" href="<?= e(url('/admin/events/' . $event['id'] . '/edit')) ?>">
                <?php if ($event['cover_image']): ?><img src="<?= e(asset($event['cover_image'])) ?>" alt="<?= e(localized($event, 'title')) ?>"><?php endif; ?>
                <div class="date-badge"><?= e(strtoupper(format_date($event['start_at'], 'd M'))) ?></div>
                <strong><?= e(localized($event, 'title')) ?></strong>
                <span><?= e($event['location']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state" style="margin:22px 0"><?= e(ui('No upcoming events are published.')) ?></div>
<?php endif; ?>

<form class="filters" method="get" style="margin-bottom:18px">
    <label class="sr-only" for="admin_event_q"><?= e(ui('Search events')) ?></label>
    <input class="form-control" id="admin_event_q" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="<?= e(ui('Search events')) ?>">
    <label class="sr-only" for="admin_event_status"><?= e(ui('Status')) ?></label>
    <select class="form-control" id="admin_event_status" name="status">
        <option value=""><?= e(ui('All statuses')) ?></option>
        <?php foreach (['draft', 'published', 'archived', 'cancelled'] as $status): ?>
            <option value="<?= e($status) ?>"<?= selected(($filters['status'] ?? '') === $status) ?>><?= e(ui($status)) ?></option>
        <?php endforeach; ?>
    </select>
    <label class="sr-only" for="admin_event_period"><?= e(ui('Date period')) ?></label>
    <select class="form-control" id="admin_event_period" name="period">
        <option value=""><?= e(ui('All dates')) ?></option>
        <option value="upcoming"<?= selected(($filters['period'] ?? '') === 'upcoming') ?>><?= e(ui('Upcoming')) ?></option>
        <option value="past"<?= selected(($filters['period'] ?? '') === 'past') ?>><?= e(ui('Past')) ?></option>
    </select>
    <button class="btn btn-primary" type="submit"><?= icon('search') ?> <?= e(ui('Search')) ?></button>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
        <tr>
            <th><?= e(ui('Image')) ?></th>
            <th><?= e(ui('Date')) ?></th>
            <th><?= e(ui('Event')) ?></th>
            <th><?= e(ui('Location')) ?></th>
            <th><?= e(ui('Status')) ?></th>
            <th><?= e(ui('Actions')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($result['items'] as $event): ?>
            <tr>
                <td><?php if ($event['cover_image']): ?><img class="thumbnail" src="<?= e(asset($event['cover_image'])) ?>" alt=""><?php endif; ?></td>
                <td><?= e(format_date($event['start_at'], 'd M Y H:i')) ?></td>
                <td><strong><?= e(localized($event, 'title')) ?></strong></td>
                <td><?= e($event['location']) ?></td>
                <td><span class="badge"><?= e(ui($event['status'])) ?></span></td>
                <td>
                    <div class="row-actions">
                        <?php if (in_array($event['status'], ['published', 'cancelled'], true)): ?><a class="btn btn-small btn-ghost" href="<?= e(url('/evenements/' . $event['slug'])) ?>" target="_blank" rel="noopener" aria-label="<?= e(ui('Public site')) ?>"><?= icon('external-link') ?></a><?php endif; ?>
                        <a class="btn btn-small btn-ghost" href="<?= e(url('/admin/events/' . $event['id'] . '/edit')) ?>"><?= icon('edit') ?></a>
                        <form method="post" action="<?= e(url('/admin/events/' . $event['id'] . '/archive')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-small btn-ghost" type="submit"><?= e(ui('Archive')) ?></button>
                        </form>
                        <form method="post" action="<?= e(url('/admin/events/' . $event['id'] . '/delete')) ?>" data-confirm="<?= e(ui('Delete this event?')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-small btn-ghost" type="submit"><?= icon('trash') ?></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$result['items']): ?>
            <tr><td colspan="6"><div class="empty-state"><?= e(ui('No events found.')) ?></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require APP_PATH . '/Views/public/partials-pagination.php'; ?>
