<?php require_once 'views/layout/header.php'; ?>

<style>
.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.15);
}
.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.15);
}
.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.15);
}
</style>

<div class="container-fluid">
    <div class="row">
        <main class="col-lg-10 col-md-12 mx-auto px-md-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-file-earmark-text me-2"></i><?= __('invoices') ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php?page=invoice&action=create" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> <?= __('create_new_invoice') ?>
                    </a>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i><?= __('filter_invoices') ?></h5>
                </div>
                <div class="card-body">
                    <form action="index.php" method="get" class="row g-3">
                        <input type="hidden" name="page" value="invoice">
                        
                        <!-- Month Filter -->
                        <div class="col-md-2">
                            <label for="month" class="form-label"><?= __('month') ?></label>
                            <select name="month" id="month" class="form-select">
                                <option value="all" <?= isset($month) && $month === 'all' ? 'selected' : '' ?>><?= __('all_months') ?></option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= isset($month) && $month == $i ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <!-- Year Filter -->
                        <div class="col-md-2">
                            <label for="year" class="form-label"><?= __('year') ?></label>
                            <select name="year" id="year" class="form-select">
                                <option value="all" <?= isset($year) && $year === 'all' ? 'selected' : '' ?>><?= __('all_years') ?></option>
                                <?php if (isset($availableYears) && is_array($availableYears)): ?>
                                    <?php foreach ($availableYears as $yr): ?>
                                        <option value="<?= $yr ?>" <?= isset($year) && $year == $yr ? 'selected' : '' ?>>
                                            <?= $yr ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Customer Search -->
                        <div class="col-md-3">
                            <label for="customer" class="form-label"><?= __('customer') ?></label>
                            <input type="text" class="form-control" id="customer" name="customer" 
                                   value="<?= htmlspecialchars(isset($customerSearch) ? $customerSearch : '') ?>" 
                                   placeholder="<?= __('search_customer_name_or_code') ?>">
                        </div>
                        
                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label for="status" class="form-label"><?= __('status') ?></label>
                            <select name="status" id="status" class="form-select">
                                <option value="" <?= isset($status) && $status === '' ? 'selected' : '' ?>><?= __('all_statuses') ?></option>
                                <option value="paid" <?= isset($status) && $status === 'paid' ? 'selected' : '' ?>><?= __('paid') ?></option>
                                <option value="unpaid" <?= isset($status) && $status === 'unpaid' ? 'selected' : '' ?>><?= __('unpaid') ?></option>
                            </select>
                        </div>
                        
                        <!-- Filter Buttons -->
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i> <?= __('filter') ?>
                            </button>
                            <a href="index.php?page=invoice" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> <?= __('reset') ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <?php 
            // Default values for statistics if not set
            $defaultStats = [
                'total_invoices' => 0,
                'paid_invoices' => 0,
                'unpaid_invoices' => 0,
                'overdue_invoices' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'unpaid_amount' => 0,
                'overdue_amount' => 0
            ];
            
            // Use statistics if set, otherwise use defaults
            $stats = isset($statistics) && is_array($statistics) ? array_merge($defaultStats, $statistics) : $defaultStats;
            ?>
            
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-primary h-100 py-2 shadow-sm">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        <?= __('total_invoices') ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= number_format($stats['total_invoices'] ?? 0) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-file-earmark-text fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-success h-100 py-2 shadow-sm">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        <?= __('paid_invoices') ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= number_format($stats['paid_invoices'] ?? 0) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-warning h-100 py-2 shadow-sm">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        <?= __('unpaid_invoices') ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= number_format($stats['unpaid_invoices'] ?? 0) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-exclamation-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-danger h-100 py-2 shadow-sm">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        <?= __('overdue_invoices') ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= number_format($stats['overdue_invoices'] ?? 0) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-x fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title"><?= __('total_amount') ?></h5>
                                <i class="bi bi-cash-stack text-muted"></i>
                            </div>
                            <p class="card-text display-6"><?= number_format($stats['total_amount'] ?? 0, 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title"><?= __('paid_amount') ?></h5>
                                <i class="bi bi-cash-coin text-success"></i>
                            </div>
                            <p class="card-text display-6"><?= number_format($stats['paid_amount'] ?? 0, 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title"><?= __('unpaid_amount') ?></h5>
                                <i class="bi bi-cash text-warning"></i>
                            </div>
                            <p class="card-text display-6"><?= number_format($stats['unpaid_amount'] ?? 0, 2) ?></p>
                        </div>
                    </div>
                </div>
            
            <!-- Invoices Table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-table me-1"></i>
                        <?= __('invoice_list') ?>
                        <?php if (isset($_GET['search']) || isset($_GET['status']) || isset($_GET['customer'])): ?>
                            <span class="badge bg-info ms-2"><?= __('filtered') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php if (isset($invoices) && is_array($invoices) && count($invoices) > 0): ?>
                                <span class="text-muted small">
                                    <?php 
                                    $showing_from = isset($currentPage) && isset($limit) ? ($currentPage - 1) * $limit + 1 : 1;
                                    $showing_to = isset($currentPage) && isset($limit) && isset($invoices) ? min(($currentPage - 1) * $limit + count($invoices), $totalInvoices ?? count($invoices)) : count($invoices);
                                    $total = isset($totalInvoices) ? $totalInvoices : count($invoices);
                                    
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
                                    <li><a class="dropdown-item" href="index.php?page=invoice&action=export&format=csv"><i class="bi bi-filetype-csv me-2"></i><?= __('export_csv') ?></a></li>
                                    <li><a class="dropdown-item" href="index.php?page=invoice&action=export&format=excel"><i class="bi bi-file-earmark-excel me-2"></i><?= __('export_excel') ?></a></li>
                                    <li><a class="dropdown-item" href="index.php?page=invoice&action=export&format=pdf"><i class="bi bi-file-earmark-pdf me-2"></i><?= __('export_pdf') ?></a></li>
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
                        <table class="table table-hover align-middle mb-0 border-bottom" id="invoicesTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="border-top-0">#</th>
                                    <th width="15%" class="border-top-0 sortable" data-sort="invoice_number">
                                        <div class="d-flex align-items-center">
                                            <?= __('invoice_number') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="15%" class="border-top-0 sortable" data-sort="customer_name">
                                        <div class="d-flex align-items-center">
                                            <?= __('customer') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="12%" class="border-top-0 sortable" data-sort="invoice_date">
                                        <div class="d-flex align-items-center">
                                            <?= __('date') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="12%" class="border-top-0 sortable" data-sort="due_date">
                                        <div class="d-flex align-items-center">
                                            <?= __('due_date') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="10%" class="border-top-0 sortable text-end" data-sort="total_amount">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <?= __('amount') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <th width="10%" class="border-top-0 sortable" data-sort="status">
                                        <div class="d-flex align-items-center">
                                            <?= __('status') ?>
                                            <i class="bi bi-arrow-down-up ms-1 text-muted small"></i>
                                        </div>
                                    </th>
                                    <!-- ลบคอลัมน์ shipments ในส่วน <th> -->
                                    <!-- <th width="10%" class="border-top-0 text-center"><?= __('shipments') ?></th> -->
                                    <th width="8%" class="border-top-0 text-center"><?= __('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!isset($invoices) || empty($invoices)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fa-3x mb-3"></i>
                                                <p><?= __('no_invoices_found') ?></p>
                                                <a href="index.php?page=invoice&action=create" class="btn btn-sm btn-primary mt-2">
                                                    <i class="bi bi-plus-circle me-1"></i> <?= __('create_new_invoice') ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $counter = isset($currentPage) && isset($limit) ? ($currentPage - 1) * $limit + 1 : 1;
                                    foreach ($invoices as $invoice): 
                                    ?>
                                        <tr class="invoice-row">
                                            <td><?= $counter++ ?></td>
                                            <td>
                                                <a href="index.php?page=invoice&action=view&id=<?= $invoice['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                                    <?= htmlspecialchars($invoice['invoice_number']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-light text-primary me-2">
                                                        <?= strtoupper(substr(isset($invoice['customer_name']) ? $invoice['customer_name'] : 'NA', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <a href="index.php?page=customer&action=view&id=<?= $invoice['customer_id'] ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars(isset($invoice['customer_name']) ? $invoice['customer_name'] : 'N/A') ?>
                                                        </a>
                                                        <?php if (isset($invoice['customer_code'])): ?>
                                                            <div class="small text-muted"><?= htmlspecialchars($invoice['customer_code']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-sort-value="<?= isset($invoice['invoice_date']) ? strtotime($invoice['invoice_date']) : 0 ?>">
                                                <div>
                                                    <?= isset($invoice['invoice_date']) ? date('d/m/Y', strtotime($invoice['invoice_date'])) : 'N/A' ?>
                                                    <?php if (isset($invoice['invoice_date'])): ?>
                                                        <div class="small text-muted"><?= date('l', strtotime($invoice['invoice_date'])) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td data-sort-value="<?= isset($invoice['due_date']) ? strtotime($invoice['due_date']) : 0 ?>">
                                                <div>
                                                    <?= isset($invoice['due_date']) ? date('d/m/Y', strtotime($invoice['due_date'])) : 'N/A' ?>
                                                    <?php if (isset($invoice['status']) && $invoice['status'] === 'unpaid' && isset($invoice['due_date']) && strtotime($invoice['due_date']) < time()): ?>
                                                        <span class="badge bg-danger ms-1"><?= __('overdue') ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold" data-sort-value="<?= isset($invoice['total_amount']) ? $invoice['total_amount'] : 0 ?>">
                                                <?= isset($invoice['total_amount']) ? number_format($invoice['total_amount'], 2) : '0.00' ?>
                                            </td>
                                            <td>
                                                <?php if (isset($invoice['status']) && $invoice['status'] === 'paid'): ?>
                                                    <span class="badge bg-success-subtle text-success px-3 py-2">
                                                        <i class="bi bi-check-circle me-1"></i> <?= __('paid') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                                        <i class="bi bi-clock me-1"></i> <?= __('unpaid') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- ลบคอลัมน์ shipments ในส่วน <td> ของแต่ละแถว -->
                                            <!-- <td class="text-center">
                                                <?php if (isset($invoice['total_shipments']) && isset($invoice['paid_shipments'])): ?>
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="progress" style="width: 60px; height: 6px;">
                                                            <?php 
                                                            $percentage = ($invoice['total_shipments'] > 0) ? 
                                                                ($invoice['paid_shipments'] / $invoice['total_shipments']) * 100 : 0;
                                                            ?>
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                style="width: <?= $percentage ?>%;" 
                                                                aria-valuenow="<?= $percentage ?>" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <span class="badge bg-secondary ms-2">
                                                            <?= $invoice['paid_shipments'] ?>/<?= $invoice['total_shipments'] ?>
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td> -->
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="index.php?page=invoice&action=view&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="<?= __('view') ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <?php if (!isset($invoice['status']) || $invoice['status'] === 'unpaid'): ?>
                                                        <a href="index.php?page=invoice&action=edit&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="<?= __('edit') ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="index.php?page=invoice&action=markAsPaid&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="<?= __('mark_as_paid') ?>" onclick="return confirm('<?= __('confirm_mark_as_paid') ?>');">
                                                            <i class="bi bi-check-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="index.php?page=invoice&action=print&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-info" target="_blank" data-bs-toggle="tooltip" title="<?= __('print') ?>">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $invoice['id'] ?>" data-invoice="<?= htmlspecialchars($invoice['invoice_number']) ?>" data-bs-toggle="tooltip" title="<?= __('delete') ?>">
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
                                <?php if (isset($currentPage) && $currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?= $currentPage - 1 ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php 
                                // Calculate pagination range
                                $startPage = max(1, ($currentPage ?? 1) - 2);
                                $endPage = min($totalPages, ($currentPage ?? 1) + 2);
                                
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
                                    <li class="page-item <?= ($i == ($currentPage ?? 1)) ? 'active' : '' ?>">
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
                                
                                <?php if (isset($currentPage) && $currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link pagination-link" href="javascript:void(0);" data-page="<?= $currentPage + 1 ?>" aria-label="Next">
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
                <p class="text-center"><?= __('confirm_delete_invoice') ?> <span id="delete-invoice" class="fw-bold"></span>?</p>
                <p class="text-center text-danger small"><?= __('action_cannot_be_undone') ?></p>
            </div>
            <div class="modal-footer">
                <form action="index.php?page=invoice&action=delete" method="post">
                    <?php if (function_exists('getCsrfInput')): ?>
                        <?= getCsrfInput() ?>
                    <?php else: ?>
                        <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '' ?>">
                    <?php endif; ?>
                    <input type="hidden" name="id" id="delete-id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-danger"><?= __('delete') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when select fields change
    document.querySelectorAll('#month, #year, #status').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
    
    // Add datalist for customer search
    const customerInput = document.getElementById('customer');
    if (customerInput) {
        // Create datalist element
        const datalist = document.createElement('datalist');
        datalist.id = 'customer-list';
        
        // Add options from customers array if available
        <?php if (isset($customers) && is_array($customers) && !empty($customers)): ?>
        <?php foreach ($customers as $customer): ?>
        const option<?= $customer['id'] ?> = document.createElement('option');
        option<?= $customer['id'] ?>.value = "<?= htmlspecialchars($customer['name'] ?? '') ?>";
        datalist.appendChild(option<?= $customer['id'] ?>);
        <?php endforeach; ?>
        <?php endif; ?>
        
        // Append datalist to document
        document.body.appendChild(datalist);
        
        // Set datalist for input
        customerInput.setAttribute('list', 'customer-list');
    }
    
    // Quick Search Functionality
    const quickSearch = document.getElementById('quickSearch');
    const clearSearch = document.getElementById('clearSearch');
    const table = document.getElementById('invoicesTable');
    const rows = table.querySelectorAll('tbody tr.invoice-row');
    
    if (quickSearch) {
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
        
        // Clear search
        clearSearch.addEventListener('click', function() {
            quickSearch.value = '';
            rows.forEach(row => {
                row.style.display = '';
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
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr.invoice-row'));
        
        // Sort rows
        const sortedRows = rows.sort((a, b) => {
            let aValue, bValue;
            
            // Get values based on column
            if (column === 'total_amount' || column === 'invoice_date' || column === 'due_date') {
                // Use data-sort-value for numeric sorting
                const aCell = a.querySelector(`td[data-sort-value]`);
                const bCell = b.querySelector(`td[data-sort-value]`);
                
                aValue = aCell ? parseFloat(aCell.getAttribute('data-sort-value')) : 0;
                bValue = bCell ? parseFloat(bCell.getAttribute('data-sort-value')) : 0;
            } else if (column === 'status') {
                // Special handling for status (paid comes before unpaid)
                aValue = a.querySelector('td:nth-child(7)').textContent.includes('paid') ? 1 : 0;
                bValue = b.querySelector('td:nth-child(7)').textContent.includes('paid') ? 1 : 0;
            } else {
                // Text comparison for other columns
                const aIndex = column === 'invoice_number' ? 1 : column === 'customer_name' ? 2 : 0;
                const bIndex = aIndex;
                
                aValue = a.querySelector(`td:nth-child(${aIndex + 1})`).textContent.trim().toLowerCase();
                bValue = b.querySelector(`td:nth-child(${bIndex + 1})`).textContent.trim().toLowerCase();
            }
            
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
    const deleteInvoiceSpan = document.getElementById('delete-invoice');
    
    if (deleteButtons.length > 0 && deleteIdInput && deleteInvoiceSpan) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const invoice = this.getAttribute('data-invoice');
                
                deleteIdInput.value = id;
                deleteInvoiceSpan.textContent = invoice;
                
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

