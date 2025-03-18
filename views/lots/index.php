<?php include 'views/layout/header.php'; ?>

<div class="container-fluid py-4">
   <div class="d-flex justify-content-between align-items-center mb-4">
       <div class="d-flex align-items-center">
           <i class="bi bi-boxes fs-4 me-2"></i>
           <h2 class="mb-0"><?php echo __('lot_management'); ?></h2>
       </div>
       <a href="index.php?page=lots&action=create" class="btn btn-primary">
           <i class="bi bi-plus-circle me-2"></i> <?php echo __('create_lot'); ?>
       </a>
   </div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
   <div class="card-body p-4">
       <form id="lot-search-form" action="index.php" method="get" class="row g-3">
            <input type="hidden" name="page" value="lots">
            <input type="hidden" name="page_no" id="page_no" value="<?php echo isset($_GET['page_no']) ? intval($_GET['page_no']) : 1; ?>">
            
            <!-- Quick Search -->
            <div class="col-md-4">
                <label for="search" class="form-label"><?php echo __('quick_search'); ?></label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="<?php echo __('search_lot_placeholder'); ?>"
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <small class="text-muted"><?php echo __('search_by_lot_origin_destination'); ?></small>
            </div>
            
            <div class="col-md-4">
                <label for="lot_type" class="form-label"><?php echo __('lot_type'); ?></label>
                <select name="lot_type" id="lot_type" class="form-select">
                    <option value=""><?php echo __('all'); ?></option>
                    <option value="sea" <?php echo isset($_GET['lot_type']) && $_GET['lot_type'] === 'sea' ? 'selected' : ''; ?>>
                        <?php echo __('sea_freight'); ?>
                    </option>
                    <option value="air" <?php echo isset($_GET['lot_type']) && $_GET['lot_type'] === 'air' ? 'selected' : ''; ?>>
                        <?php echo __('air_freight'); ?>
                    </option>
                    <option value="land" <?php echo isset($_GET['lot_type']) && $_GET['lot_type'] === 'land' ? 'selected' : ''; ?>>
                        <?php echo __('land_freight'); ?>
                    </option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="status" class="form-label"><?php echo __('status'); ?></label>
                <select name="status" id="status" class="form-select">
                    <option value=""><?php echo __('all'); ?></option>
                    <option value="received" <?php echo isset($_GET['status']) && $_GET['status'] === 'received' ? 'selected' : ''; ?>>
                        <?php echo __('received'); ?>
                    </option>
                    <option value="in_transit" <?php echo isset($_GET['status']) && $_GET['status'] === 'in_transit' ? 'selected' : ''; ?>>
                        <?php echo __('in_transit'); ?>
                    </option>
                    <option value="arrived_destination" <?php echo isset($_GET['status']) && $_GET['status'] === 'arrived_destination' ? 'selected' : ''; ?>>
                        <?php echo __('arrived_destination'); ?>
                    </option>
                    <option value="local_delivery" <?php echo isset($_GET['status']) && $_GET['status'] === 'local_delivery' ? 'selected' : ''; ?>>
                        <?php echo __('local_delivery'); ?>
                    </option>
                    <option value="delivered" <?php echo isset($_GET['status']) && $_GET['status'] === 'delivered' ? 'selected' : ''; ?>>
                        <?php echo __('delivered'); ?>
                    </option>
                </select>
            </div>
            
            <!-- Date Range Filters -->
            <div class="col-md-3">
                <label for="date_from" class="form-label"><?php echo __('departure_date_from'); ?></label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="date_to" class="form-label"><?php echo __('departure_date_to'); ?></label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
            </div>
            
            <div class="col-md-2">
                <label for="origin" class="form-label"><?php echo __('origin'); ?></label>
                <input type="text" class="form-control" id="origin" name="origin" 
                       value="<?php echo isset($_GET['origin']) ? htmlspecialchars($_GET['origin']) : ''; ?>">
            </div>
            
            <div class="col-md-2">
                <label for="destination" class="form-label"><?php echo __('destination'); ?></label>
                <input type="text" class="form-control" id="destination" name="destination" 
                       value="<?php echo isset($_GET['destination']) ? htmlspecialchars($_GET['destination']) : ''; ?>">
            </div>
            
            <div class="col-md-2">
                <label for="limit" class="form-label"><?php echo __('items_per_page'); ?></label>
                <select name="limit" id="limit" class="form-select">
                    <option value="10" <?php echo (isset($_GET['limit']) && $_GET['limit'] == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="20" <?php echo (isset($_GET['limit']) && $_GET['limit'] == 20) || !isset($_GET['limit']) ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php echo isset($_GET['limit']) && $_GET['limit'] == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo isset($_GET['limit']) && $_GET['limit'] == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i> <?php echo __('search'); ?>
                </button>
                <button type="button" id="reset-search" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> <?php echo __('reset'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lots Content Area -->
<div id="lots-content">
    <?php if (!$lots || count($lots) === 0): ?>
       <div class="card shadow-sm">
           <div class="card-body p-4">
               <div class="text-center py-4">
                   <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                   <h4 class="text-muted"><?php echo __('no_lots_found'); ?></h4>
                   <p class="text-muted"><?php echo __('no_lots_found_message'); ?></p>
                   <a href="index.php?page=lots&action=create" class="btn btn-primary mt-3">
                       <i class="bi bi-plus-circle me-2"></i> <?php echo __('create_lot'); ?>
                   </a>
               </div>
           </div>
       </div>
    <?php else: ?>
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?php 
                    $start = ($pagination['current_page'] - 1) * $pagination['limit'] + 1;
                    $end = min($pagination['current_page'] * $pagination['limit'], $pagination['total_items']);
                    ?>
                    <span class="text-secondary">
                        <?php echo __('showing'); ?> <?php echo $start; ?> 
                        <?php echo __('to'); ?> <?php echo $end; ?> 
                        <?php echo __('of'); ?> <?php echo $pagination['total_items']; ?> <?php echo __('lots'); ?>
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button id="refresh-btn" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="<?php echo __('refresh'); ?>">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" title="<?php echo __('export'); ?>">
                            <i class="bi bi-download me-1"></i> <?php echo __('export'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                            <li><a class="dropdown-item" href="#" id="export-csv"><i class="bi bi-filetype-csv me-2"></i> CSV</a></li>
                            <li><a class="dropdown-item" href="#" id="export-excel"><i class="bi bi-file-earmark-excel me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item" href="#" id="export-pdf"><i class="bi bi-file-earmark-pdf me-2"></i> PDF</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="lotsTable" class="table table-hover align-middle mb-0 border-bottom">
                    <thead class="table-light border-top-0">
                        <tr>
                            <th width="5%" class="border-top-0 ps-3">#</th>
                            <th width="15%" class="border-top-0 sortable" data-sort="lot_number"><?php echo __('lot_number'); ?></th>
                            <th width="10%" class="border-top-0 sortable" data-sort="lot_type"><?php echo __('lot_type'); ?></th>
                            <th width="12%" class="border-top-0 sortable" data-sort="departure_date"><?php echo __('departure_date'); ?></th>
                            <th width="12%" class="border-top-0 sortable" data-sort="arrival_date"><?php echo __('arrival_date'); ?></th>
                            <th width="15%" class="border-top-0 sortable" data-sort="origin"><?php echo __('origin'); ?></th>
                            <th width="15%" class="border-top-0 sortable" data-sort="destination"><?php echo __('destination'); ?></th>
                            <th width="8%" class="border-top-0 sortable" data-sort="status"><?php echo __('status'); ?></th>
                            <th width="8%" class="border-top-0 text-end pe-3"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = ($pagination['current_page'] - 1) * $pagination['limit'];
                        foreach ($lots as $lot): 
                            $counter++;
                        ?>
                            <tr class="lot-row">
                                <td class="ps-3"><?php echo $counter; ?></td>
                                <td><?php echo htmlspecialchars($lot['lot_number']); ?></td>
                                <td>
                                    <?php 
                                    $typeIcon = '';
                                    $typeBadge = '';
                                    switch ($lot['lot_type']) {
                                        case 'sea':
                                            $typeIcon = '<i class="bi bi-water me-1"></i>';
                                            $typeBadge = 'bg-info';
                                            break;
                                        case 'air':
                                            $typeIcon = '<i class="bi bi-airplane me-1"></i>';
                                            $typeBadge = 'bg-primary';
                                            break;
                                        case 'land':
                                            $typeIcon = '<i class="bi bi-truck me-1"></i>';
                                            $typeBadge = 'bg-success';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $typeBadge; ?>">
                                        <?php echo $typeIcon . __($lot['lot_type'] . '_freight'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($lot['departure_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($lot['arrival_date'])); ?></td>
                                <td><?php echo htmlspecialchars($lot['origin']); ?></td>
                                <td><?php echo htmlspecialchars($lot['destination']); ?></td>
                                <td>
                                    <?php 
                                    $statusBadge = '';
                                    switch ($lot['status']) {
                                        case 'received':
                                            $statusBadge = 'bg-secondary';
                                            break;
                                        case 'in_transit':
                                            $statusBadge = 'bg-primary';
                                            break;
                                        case 'arrived_destination':
                                            $statusBadge = 'bg-info';
                                            break;
                                        case 'local_delivery':
                                            $statusBadge = 'bg-warning';
                                            break;
                                        case 'delivered':
                                            $statusBadge = 'bg-success';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusBadge; ?>">
                                        <?php echo __($lot['status']); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <a href="index.php?page=lots&action=view&id=<?php echo urlencode($lot['id']); ?>" 
                                           class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="<?php echo __('view'); ?>">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="index.php?page=lots&action=edit&id=<?php echo urlencode($lot['id']); ?>" 
                                           class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="<?php echo __('edit'); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button onclick="confirmDelete('<?php echo $lot['id']; ?>')" class="btn btn-sm btn-danger" title="<?php echo __('delete'); ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
            <div id="pagination-container" class="py-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo $pagination['current_page'] <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link pagination-link" href="#" data-page="<?php echo $pagination['current_page'] - 1; ?>">
                                <i class="bi bi-chevron-left"></i> <?php echo __('previous'); ?>
                            </a>
                        </li>
                        
                        <?php
                        // Calculate range of page numbers to display
                        $range = 2; // Display 2 pages before and after current page
                        $start = max(1, $pagination['current_page'] - $range);
                        $end = min($pagination['total_pages'], $pagination['current_page'] + $range);
                        
                        // Always show first page
                        if ($start > 1) {
                            ?>
                            <li class="page-item">
                                <a class="page-link pagination-link" href="#" data-page="1">1</a>
                            </li>
                            <?php if ($start > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <?php
                        }
                        
                        // Display page numbers
                        for ($i = $start; $i <= $end; $i++) {
                            ?>
                            <li class="page-item <?php echo $pagination['current_page'] == $i ? 'active' : ''; ?>">
                                <a class="page-link pagination-link" href="#" data-page="<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php
                        }
                        
                        // Always show last page
                        if ($end < $pagination['total_pages']) {
                            ?>
                            <?php if ($end < $pagination['total_pages'] - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link pagination-link" href="#" data-page="<?php echo $pagination['total_pages']; ?>">
                                    <?php echo $pagination['total_pages']; ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                        
                        <li class="page-item <?php echo $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : ''; ?>">
                            <a class="page-link pagination-link" href="#" data-page="<?php echo $pagination['current_page'] + 1; ?>">
                                <?php echo __('next'); ?> <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteLotModal" tabindex="-1" aria-labelledby="deleteLotModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteLotModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i> <?php echo __('delete_confirmation'); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?php echo __('delete_lot_confirmation'); ?></p>
                <p><strong id="lot-number-to-delete"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php echo __('cancel'); ?>
                </button>
                <form id="delete-lot-form" action="index.php?page=lots&action=delete" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" id="lot-id-to-delete" value="">
                    <button type="submit" class="btn btn-danger">
                        <?php echo __('delete'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background-color: rgba(255,255,255,0.7); z-index: 1050;">
    <div class="position-absolute top-50 start-50 translate-middle text-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 fw-bold"><?php echo __('loading'); ?>...</p>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('<?php echo __('confirm_delete_lot'); ?>')) {
        window.location.href = 'index.php?page=lots&action=delete&id=' + encodeURIComponent(id);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // AJAX Pagination
    const paginationLinks = document.querySelectorAll('.pagination-link');
    const lotSearchForm = document.getElementById('lot-search-form');
    const pageNoInput = document.getElementById('page_no');
    const loadingOverlay = document.getElementById('loading-overlay');
    const lotsContent = document.getElementById('lots-content');
    const resetSearchBtn = document.getElementById('reset-search');
    const refreshBtn = document.getElementById('refresh-btn');
    
    // Function to show loading overlay
    function showLoading() {
        loadingOverlay.classList.remove('d-none');
    }
    
    // Function to hide loading overlay
    function hideLoading() {
        loadingOverlay.classList.add('d-none');
    }
    
    // Function to load content via AJAX
    function loadContent(url) {
        showLoading();
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Create a temporary element to parse the HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Extract the lots content
                const newContent = tempDiv.querySelector('#lots-content');
                
                if (newContent) {
                    // Replace the current content with the new content
                    lotsContent.innerHTML = newContent.innerHTML;
                    
                    // Reinitialize event listeners for the new content
                    initEventListeners();
                    
                    // Update the URL without reloading the page
                    window.history.pushState({}, '', url);
                } else {
                    console.error('Could not find lots content in the response');
                }
                
                hideLoading();
            })
            .catch(error => {
                console.error('Error loading content:', error);
                hideLoading();
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล กรุณาลองใหม่อีกครั้ง');
            });
    }
    
    // Function to initialize event listeners
    function initEventListeners() {
        // Pagination links
        document.querySelectorAll('.pagination-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const page = this.getAttribute('data-page');
                pageNoInput.value = page;
                
                // Submit the form via AJAX
                const formData = new FormData(lotSearchForm);
                const queryString = new URLSearchParams(formData).toString();
                const url = `index.php?${queryString}`;
                
                loadContent(url);
            });
        });
        
        // Initialize tooltips for new content
        const newTooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        newTooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Delete lot buttons
        document.querySelectorAll('.delete-lot-btn').forEach(button => {
            button.addEventListener('click', function() {
                const lotId = this.getAttribute('data-id');
                const lotNumber = this.getAttribute('data-lot-number');
                
                document.getElementById('lot-id-to-delete').value = lotId;
                document.getElementById('lot-number-to-delete').textContent = lotNumber;
            });
        });
        
        // Sortable columns
        document.querySelectorAll('.sortable').forEach(column => {
            column.addEventListener('click', function() {
                const sort = this.getAttribute('data-sort');
                const currentSort = lotSearchForm.querySelector('input[name="sort"]');
                const currentOrder = lotSearchForm.querySelector('input[name="order"]');
                
                // If sort input doesn't exist, create it
                if (!currentSort) {
                    const sortInput = document.createElement('input');
                    sortInput.type = 'hidden';
                    sortInput.name = 'sort';
                    sortInput.value = sort;
                    lotSearchForm.appendChild(sortInput);
                } else {
                    // If clicking on the same column, toggle order
                    if (currentSort.value === sort) {
                        if (!currentOrder || currentOrder.value === 'asc') {
                            if (!currentOrder) {
                                const orderInput = document.createElement('input');
                                orderInput.type = 'hidden';
                                orderInput.name = 'order';
                                orderInput.value = 'desc';
                                lotSearchForm.appendChild(orderInput);
                            } else {
                                currentOrder.value = 'desc';
                            }
                        } else {
                            currentOrder.value = 'asc';
                        }
                    } else {
                        // If clicking on a different column, set sort and reset order to asc
                        currentSort.value = sort;
                        if (!currentOrder) {
                            const orderInput = document.createElement('input');
                            orderInput.type = 'hidden';
                            orderInput.name = 'order';
                            orderInput.value = 'asc';
                            lotSearchForm.appendChild(orderInput);
                        } else {
                            currentOrder.value = 'asc';
                        }
                    }
                }
                
                // Reset to page 1
                pageNoInput.value = 1;
                
                // Submit the form
                const formData = new FormData(lotSearchForm);
                const queryString = new URLSearchParams(formData).toString();
                const url = `index.php?${queryString}`;
                
                loadContent(url);
            });
        });
        
        // Quick search input
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                // If Enter key is pressed, submit the form  function(e) {
                // If Enter key is pressed, submit the form
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    // Reset to page 1
                    pageNoInput.value = 1;
                    
                    // Submit the form
                    const formData = new FormData(lotSearchForm);
                    const queryString = new URLSearchParams(formData).toString();
                    const url = `index.php?${queryString}`;
                    
                    loadContent(url);
                }
            });
        }
    }
    
    // Initialize event listeners
    initEventListeners();
    
    // Handle form submission
    if (lotSearchForm) {
        lotSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset to page 1 when searching
            pageNoInput.value = 1;
            
            const formData = new FormData(this);
            const queryString = new URLSearchParams(formData).toString();
            const url = `index.php?${queryString}`;
            
            loadContent(url);
        });
    }
    
    // Reset search form
    if (resetSearchBtn) {
        resetSearchBtn.addEventListener('click', function() {
            // Clear all form fields except the hidden ones
            const formElements = lotSearchForm.elements;
            for (let i = 0; i < formElements.length; i++) {
                const element = formElements[i];
                if (element.type !== 'hidden' && element.type !== 'submit' && element.type !== 'button') {
                    if (element.tagName === 'SELECT') {
                        element.selectedIndex = 0;
                    } else {
                        element.value = '';
                    }
                }
            }
            
            // Remove any sort and order inputs
            const sortInput = lotSearchForm.querySelector('input[name="sort"]');
            const orderInput = lotSearchForm.querySelector('input[name="order"]');
            if (sortInput) sortInput.remove();
            if (orderInput) orderInput.remove();
            
            // Reset page number to 1
            pageNoInput.value = 1;
            
            // Submit the form
            const formData = new FormData(lotSearchForm);
            const queryString = new URLSearchParams(formData).toString();
            const url = `index.php?${queryString}`;
            
            loadContent(url);
        });
    }
    
    // Refresh button
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Get current URL and reload content
            const currentUrl = window.location.href;
            loadContent(currentUrl);
        });
    }
    
    // Export buttons
    document.getElementById('export-csv')?.addEventListener('click', function(e) {
        e.preventDefault();
        alert('CSV export functionality will be implemented here');
    });
    
    document.getElementById('export-excel')?.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Excel export functionality will be implemented here');
    });
    
    document.getElementById('export-pdf')?.addEventListener('click', function(e) {
        e.preventDefault();
        alert('PDF export functionality will be implemented here');
    });
    
    // Add event listeners for real-time search (optional)
    const searchInputs = document.querySelectorAll('#lot-search-form select');
    let debounceTimer;
    
    searchInputs.forEach(input => {
        input.addEventListener('change', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                // Reset to page 1 when search criteria changes
                pageNoInput.value = 1;
                
                // Submit the form
                const formData = new FormData(lotSearchForm);
                const queryString = new URLSearchParams(formData).toString();
                const url = `index.php?${queryString}`;
                
                loadContent(url);
            }, 300); // 300ms debounce
        });
    });
});
</script>

<?php include 'views/layout/footer.php'; ?>

