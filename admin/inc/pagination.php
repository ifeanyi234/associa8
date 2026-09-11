<?php
function renderPagination($currentPage, $totalItems, $itemsPerPage, $query = [])
{
    $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
    if ($totalPages <= 1) {
        return;
    }

    $currentPage = max(1, min((int) $currentPage, $totalPages));
    $buildUrl = function ($page) use ($query) {
        return '?' . http_build_query(array_merge($query, ['page' => $page]));
    };
    ?>
    <div class="pagination-controls">
      <a class="page-btn" href="<?php echo htmlspecialchars($buildUrl($currentPage - 1)); ?>" <?php echo $currentPage === 1 ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>Prev</a>
      <?php for ($page = 1; $page <= $totalPages; $page++): ?>
        <a class="page-btn <?php echo $page === $currentPage ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildUrl($page)); ?>"><?php echo $page; ?></a>
      <?php endfor; ?>
      <a class="page-btn" href="<?php echo htmlspecialchars($buildUrl($currentPage + 1)); ?>" <?php echo $currentPage === $totalPages ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>Next</a>
    </div>
    <?php
}
