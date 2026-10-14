<?php if (($result['pages'] ?? 1) > 1): ?>
    <nav class="pagination" aria-label="Pagination">
        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
            <?php $query = array_merge($_GET, ['page' => $i]); ?>
            <?php if ($i === (int) $result['page']): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?<?= e(http_build_query($query)) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
