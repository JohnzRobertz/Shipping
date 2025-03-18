<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <main class="col-lg-10 col-md-12 mx-auto px-md-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-people me-2"></i><?= __('customers') ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php?page=customer&action=create" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> <?= __('create_new_customer') ?>
                    </a>
                </div>
            </div>

            <!-- Debug Info - Remove in production -->
            <?php if (isset($_GET['search'])): ?>
            <!-- <div class="alert alert-info">
                <strong>Debug:</strong> Search term: "<?= htmlspecialchars($_GET['search']) ?>"
            </div> -->
            <?php endif; ?>

            <!-- Filter Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-search me-2"></i><?= __('search_customers') ?></h5>
                </div>
                <div class="card-body">
                    <form method="get" action="index.php" class="row g-3">
                        <input type="hidden" name="page" value="customer">
                        
                        <!-- Search -->
                        <div class="col-md-8">
                            <label for="search" class="form-label"><?= __('search') ?></label>
                            <div class="d-flex">
                                <input type="text" class="form-control me-2" id="search" name="search" 
                                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                                       placeholder="<?= __('search_customer_name_code_or_contact') ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i> <?= __('search') ?>
                                </button>
                            </div>
                            <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                            <div class="mt-2">
                                <a href="index.php?page=customer" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> <?= __('reset_search') ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Customers Table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-table me-1"></i>
                        <?= __('customer_list') ?>
                        <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                            <span class="badge bg-info ms-2"><?= __('filtered') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php if (isset($customers) && is_array($customers) && count($customers) > 0): ?>
                                <span class="text-muted small">
                                    <?php 
                                    $showing_from = isset($page) && isset($limit) ? ($page - 1) * $limit + 1 : 1;
                                    $showing_to = isset($page) && isset($limit) && isset($customers) ? min(($page - 1) * $limit + count($customers), $totalCustomers ?? count($customers)) : count($customers);
                                    $total = isset($totalCustomers) ? $totalCustomers : count($customers);
                                    
                                    echo sprintf(
                                        isset($lang['showing_entries']) ? $lang['showing_entries'] : 'Showing %d to %d of %d entries',
                                        $showing_from,
                                        $showing_to,
                                        $total
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" id="refresh-data">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-download me-1"></i> <?= __('export') ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                    <li><a class="dropdown-item" href="index.php?page=customer&action=export&format=csv"><i class="bi bi-filetype-csv me-2"></i><?= __('export_csv') ?></a></li>
                                    <li><a class="dropdown-item" href="index.php?page=customer&action=export&format=excel"><i class="bi bi-file-earmark-excel me-2"></i><?= __('export_excel') ?></a></li>
                                    <li><a class="dropdown-item" href="index.php?page=customer&action=export&format=pdf"><i class="bi bi-file-earmark-pdf me-2"></i><?= __('export_pdf') ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Loading Overlay -->
                    <div id="loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex justify-content-center align-items-center d-none" style="z-index: 1000;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-bottom" id="customersTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="border-top-0">#</th>
                                    <th width="10%" class="border-top-0 sortable" data-sort="code">
                                        <div class="d-flex align-items-center">
                                            <?= __('code') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="25%" class="border-top-0 sortable" data-sort="name">
                                        <div class="d-flex align-items-center">
                                            <?= __('name') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="15%" class="border-top-0 sortable" data-sort="contact">
                                        <div class="d-flex align-items-center">
                                            <?= __('contact') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="20%" class="border-top-0 sortable" data-sort="email">
                                        <div class="d-flex align-items-center">
                                            <?= __('email') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="15%" class="border-top-0 sortable" data-sort="phone">
                                        <div class="d-flex align-items-center">
                                            <?= __('phone') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="10%" class="border-top-0 text-center"><?= __('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-people fa-3x mb-3"></i>
                                                <p><?= __('no_customers_found') ?></p>
                                                <a href="index.php?page=customer&action=create" class="btn btn-sm btn-primary mt-2">
                                                    <i class="bi bi-plus-circle me-1"></i> <?= __('create_new_customer') ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $counter = isset($page) && isset($limit) ? ($page - 1) * $limit + 1 : 1;
                                    foreach ($customers as $customer): 
                                    ?>
                                        <tr class="customer-row">
                                            <td><?= $counter++ ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($customer['code']) ?></span>
                                            </td>
                                            <td>
                                                <a href="index.php?page=customer&action=view&id=<?= $customer['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                                    <?= htmlspecialchars($customer['name']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($customer['contact_person'] ?? '-') ?></td>
                                            <td>
                                                <?php if (!empty($customer['email'])): ?>
                                                    <a href="mailto:<?= htmlspecialchars($customer['email']) ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($customer['email']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($customer['phone'])): ?>
                                                    <a href="tel:<?= htmlspecialchars($customer['phone']) ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($customer['phone']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="index.php?page=customer&action=view&id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="index.php?page=customer&action=edit&id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $customer['id'] ?>" data-customer="<?= htmlspecialchars($customer['name']) ?>" data-bs-toggle="tooltip" title="<?= __('delete') ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <label for="pageSize" class="me-2 mb-0 small"><?= __('show') ?>:</label>
                            <select id="pageSize" class="form-select form-select-sm" style="width: 70px;">
                                <option value="10" <?= (!isset($_GET['limit']) || $_GET['limit'] == '10') ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= (isset($_GET['limit']) && $_GET['limit'] == '25') ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= (isset($_GET['limit']) && $_GET['limit'] == '50') ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= (isset($_GET['limit']) && $_GET['limit'] == '100') ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>
                        <?php if (isset($totalPages) && $totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <?php if (isset($page) && $page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?= $page - 1 ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php 
                                // Calculate pagination range
                                $startPage = max(1, ($page ?? 1) - 2);
                                $endPage = min($totalPages, ($page ?? 1) + 2);
                                
                                // Always show first page
                                if ($startPage > 1) {
                                    echo '<li class="page-item"><a class="page-link pagination-link" href="javascript:void(0);" data-page="1">1</a></li>';
                                    if ($startPage > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                
                                // Show page numbers
                                for ($i = $startPage; $i <= $endPage; $i++): 
                                ?>
                                    <li class="page-item <?= ($i == ($page ?? 1)) ? 'active' : '' ?>">
                                        <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php 
                                // Always show last page
                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link pagination-link" href="javascript:void(0);" data-page="' . $totalPages . '">' . $totalPages . '</a></li>';
                                }
                                ?>
                                
                                <?php if (isset($page) && $page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?= $page + 1 ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel"><?= __('confirm_delete') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger fa-4x"></i>
                </div>
                <p class="text-center"><?= __('confirm_delete_customer') ?> <span id="delete-customer" class="fw-bold"></span>?</p>
                <p class="text-center text-danger small"><?= __('action_cannot_be_undone') ?></p>
            </div>
            <!-- แก้ไขฟอร์มในส่วน Delete Modal -->
            <div class="modal-footer">
                <form action="index.php?page=customer&action=delete" method="post">
                    <?php if (function_exists('getCsrfInput')): ?>
                        <?= getCsrfInput() ?>
                    <?php else: ?>
                        <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                    <?php endif; ?>
                    <input type="hidden" name="id" id="delete-id">
                    <input type="hidden" name="confirm" value="yes">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-danger"><?= __('delete') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick Search Functionality
    const quickSearch = document.getElementById('search');
    const table = document.getElementById('customersTable');
    const rows = table ? table.querySelectorAll('tbody tr.customer-row') : [];
    
    if (quickSearch && rows.length > 0) {
        quickSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Sorting functionality
    const sortableHeaders = document.querySelectorAll('th.sortable');
    let currentSort = {
        column: null,
        direction: 'asc'
    };
    
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-sort');
            
            // Toggle direction if same column clicked again
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }
            
            // Update UI to show sort direction
            sortableHeaders.forEach(h => {
                const icon = h.querySelector('i');
                icon.className = 'bi bi-arrow-down-up ms-1 text-muted small';
            });
            
            const icon = this.querySelector('i');
            icon.className = currentSort.direction === 'asc' 
                ? 'bi bi-arrow-up ms-1 text-primary small' 
                : 'bi bi-arrow-down ms-1 text-primary small';
            
            // Sort the table
            sortTable(column, currentSort.direction);
        });
    });
    
    function sortTable(column, direction) {
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr.customer-row'));
        
        if (rows.length === 0) return;
        
        // Sort rows
        const sortedRows = rows.sort((a, b) => {
            let aValue, bValue;
            
            // Get column index based on the data-sort attribute
            let columnIndex;
            switch(column) {
                case 'code': columnIndex = 1; break;
                case 'name': columnIndex = 2; break;
                case 'contact': columnIndex = 3; break;
                case 'email': columnIndex = 4; break;
                case 'phone': columnIndex = 5; break;
                default: columnIndex = 0;
            }
            
            // Get cell values
            aValue = a.querySelector(`td:nth-child(${columnIndex + 1})`).textContent.trim().toLowerCase();
            bValue = b.querySelector(`td:nth-child(${columnIndex + 1})`).textContent.trim().toLowerCase();
            
            // Compare values
            if (aValue < bValue) return direction === 'asc' ? -1 : 1;
            if (aValue > bValue) return direction === 'asc' ? 1 : -1;
            return 0;
        });
        
        // Remove existing rows
        rows.forEach(row => row.remove());
        
        // Add sorted rows
        sortedRows.forEach(row => tbody.appendChild(row));
    }
    
    // Page size selector
    const pageSizeSelector = document.getElementById('pageSize');
    if (pageSizeSelector) {
        pageSizeSelector.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('limit', this.value);
            
            // Reset to first page
            if (url.searchParams.has('page_no')) {
                url.searchParams.set('page_no', '1');
            } else if (url.searchParams.has('p')) {
                url.searchParams.set('p', '1');
            }
            
            window.location.href = url.toString();
        });
    }
    
    // AJAX Pagination
    const paginationLinks = document.querySelectorAll('.pagination-link');
    const loadingOverlay = document.getElementById('loading-overlay');
    
    if (paginationLinks.length > 0 && loadingOverlay) {
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const page = this.getAttribute('data-page');
                const currentUrl = new URL(window.location.href);
                
                // Use the same page parameter that's already in the URL
                if (currentUrl.searchParams.has('page_no')) {
                    currentUrl.searchParams.set('page_no', page);
                } else {
                    currentUrl.searchParams.set('p', page);
                }
                
                // Show loading overlay
                loadingOverlay.classList.remove('d-none');
                
                // Navigate to the new URL
                window.location.href = currentUrl.toString();
            });
        });
    }
    
    // Refresh data button
    const refreshBtn = document.getElementById('refresh-data');
    
    if (refreshBtn && loadingOverlay) {
        refreshBtn.addEventListener('click', function() {
            // Show loading overlay
            loadingOverlay.classList.remove('d-none');
            
            // Reload the current page
            window.location.reload();
        });
    }
    
    // Delete modal functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteIdInput = document.getElementById('delete-id');
    const deleteCustomerSpan = document.getElementById('delete-customer');
    
    if (deleteButtons.length > 0 && deleteIdInput && deleteCustomerSpan) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const customer = this.getAttribute('data-customer');
                
                deleteIdInput.value = id;
                deleteCustomerSpan.textContent = customer;
                
                // Use Bootstrap 5 modal API
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'top',
            trigger: 'hover'
        });
    });
});
</script>

<?php require_once 'views/layout/footer.php'; ?>

