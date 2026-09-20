<?php
function renderPagination($currentPage, $totalItems, $itemsPerPage, $query = [])
{
    $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
    if ($totalPages <= 1) {
        return;
    }

    $currentPage = max(1, min((int) $currentPage, $totalPages));
    $prevPage = max(1, $currentPage - 1);
    $nextPage = min($totalPages, $currentPage + 1);
    $prevDisabled = $currentPage <= 1;
    $nextDisabled = $currentPage >= $totalPages;

    $buildUrl = function ($page) use ($query) {
        $params = array_merge($query, ['page' => max(1, (int) $page)]);
        return '?' . http_build_query($params);
    };
    ?>
    <div class="pagination-controls">
      <a
        class="page-btn<?php echo $prevDisabled ? ' disabled' : ''; ?>"
        href="<?php echo $prevDisabled ? '#' : htmlspecialchars($buildUrl($prevPage)); ?>"
        <?php echo $prevDisabled ? 'aria-disabled="true" tabindex="-1"' : ''; ?>
      >Prev</a>
      <?php for ($page = 1; $page <= $totalPages; $page++): ?>
        <a class="page-btn <?php echo $page === $currentPage ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildUrl($page)); ?>"><?php echo $page; ?></a>
      <?php endfor; ?>
      <a
        class="page-btn<?php echo $nextDisabled ? ' disabled' : ''; ?>"
        href="<?php echo $nextDisabled ? '#' : htmlspecialchars($buildUrl($nextPage)); ?>"
        <?php echo $nextDisabled ? 'aria-disabled="true" tabindex="-1"' : ''; ?>
      >Next</a>
    </div>
    <?php
}
