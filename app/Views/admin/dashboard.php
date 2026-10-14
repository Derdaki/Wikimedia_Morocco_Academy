<div class="admin-grid">
    <div class="admin-card"><span><?= e(ui('Total events')) ?></span><div class="metric"><?= (int) $metrics['events'] ?></div></div>
    <div class="admin-card"><span><?= e(ui('Upcoming events')) ?></span><div class="metric"><?= (int) $metrics['upcoming_events'] ?></div></div>
    <div class="admin-card"><span><?= e(ui('Total projects')) ?></span><div class="metric"><?= (int) $metrics['projects'] ?></div></div>
    <div class="admin-card"><span><?= e(ui('Published news')) ?></span><div class="metric"><?= (int) $metrics['news'] ?></div></div>
    <div class="admin-card"><span><?= e(ui('Team members')) ?></span><div class="metric"><?= (int) $metrics['team'] ?></div></div>
    <div class="admin-card"><span><?= e(ui('Unread messages')) ?></span><div class="metric"><?= (int) $metrics['unread_messages'] ?></div></div>
</div>

<div class="two-col" style="margin-top:24px">
    <section class="admin-card">
        <div class="section-headline-row">
            <h2 style="margin:0"><?= e(ui('Latest events')) ?></h2>
            <a class="btn btn-small btn-outline" href="<?= e(url('/admin/events/create')) ?>"><?= icon('plus') ?> <?= e(ui('Create')) ?></a>
        </div>
        <?php if ($upcoming): ?>
            <div class="stack">
                <?php foreach ($upcoming as $event): ?>
                    <div class="meta-row" style="justify-content:space-between">
                        <strong><?= e(localized($event, 'title')) ?></strong>
                        <span><?= e(format_date($event['start_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('No upcoming events.')) ?></div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <h2 style="margin-top:0"><?= e(ui('Recent activity')) ?></h2>
        <?php if ($activity): ?>
            <div class="stack">
                <?php foreach ($activity as $item): ?>
                    <div>
                        <strong><?= e($item['action']) ?></strong>
                        <p style="margin:4px 0;color:#667085"><?= e($item['description'] ?? '') ?> <?= e(ui('by')) ?> <?= e($item['user_name'] ?? 'System') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('No activity yet.')) ?></div>
        <?php endif; ?>
    </section>
</div>

<div class="two-col" style="margin-top:24px">
    <section class="admin-card">
        <h2 style="margin-top:0"><?= e(ui('Recently published articles')) ?></h2>
        <?php if ($articles): ?>
            <div class="stack">
                <?php foreach ($articles as $article): ?>
                    <a href="<?= e(url('/admin/news/' . $article['id'] . '/edit')) ?>"><?= e(localized($article, 'title')) ?></a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('No published articles.')) ?></div>
        <?php endif; ?>
    </section>
    <section class="admin-card">
        <h2 style="margin-top:0"><?= e(ui('Unread messages')) ?></h2>
        <?php if ($messages): ?>
            <div class="stack">
                <?php foreach ($messages as $message): ?>
                    <div>
                        <strong><?= e($message['subject']) ?></strong>
                        <p style="margin:4px 0;color:#667085"><?= e($message['name']) ?>, <?= e($message['email']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><?= e(ui('No unread messages.')) ?></div>
        <?php endif; ?>
    </section>
</div>
