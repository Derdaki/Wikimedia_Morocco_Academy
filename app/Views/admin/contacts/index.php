<div class="table-wrap">
    <table class="table">
        <thead><tr><th><?= e(ui('Sender')) ?></th><th><?= e(ui('Subject')) ?></th><th><?= e(ui('Message')) ?></th><th><?= e(ui('Date')) ?></th><th><?= e(ui('Actions')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($messages as $message): ?>
            <tr>
                <td><strong><?= e($message['name']) ?></strong><br><span style="color:#667085"><?= e($message['email']) ?></span></td>
                <td><?= e($message['subject']) ?><?= $message['read_at'] ? '' : ' <span class="badge badge-red">Unread</span>' ?></td>
                <td><details><summary><?= e(excerpt($message['message'], 120)) ?></summary><p style="white-space:pre-wrap;max-width:460px"><?= e($message['message']) ?></p></details></td>
                <td><?= e(format_date($message['created_at'], 'd M Y H:i')) ?></td>
                <td>
                    <div class="row-actions">
                        <form method="post" action="<?= e(url('/admin/contact-messages/' . $message['id'] . '/read')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-small btn-ghost" type="submit"><?= e(ui('Read')) ?></button>
                        </form>
                        <form method="post" action="<?= e(url('/admin/contact-messages/' . $message['id'] . '/delete')) ?>" data-confirm="<?= e(ui('Delete this message?')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-small btn-ghost" type="submit"><?= icon('trash') ?></button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$messages): ?>
            <tr><td colspan="5"><div class="empty-state"><?= e(ui('No contact messages.')) ?></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
