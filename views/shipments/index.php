<?php include 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <!-- Dashboard Cards -->
    <div class="row mt-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= isset($totalShipments) ? number_format($totalShipments) : 0 ?></h4>
                        <div class="small"><?= isset($lang['total_shipments']) ? $lang['total_shipments'] : 'Total Shipments' ?></div>
                    </div>
                    <div class="fa-3x">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=shipments"><?= isset($lang['view_details']) ? $lang['view_details'] : 'View Details' ?></a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= isset($pendingCount) ? number_format($pendingCount) : 0 ?></h4>
                        <div class="small"><?= isset($lang['pending_shipments']) ? $lang['pending_shipments'] : 'Pending Shipments' ?></div>
                    </div>
                    <div class="fa-3x">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=shipments&status=pending"><?= isset($lang['view_details']) ? $lang['view_details'] : 'View Details' ?></a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= isset($deliveredCount) ? number_format($deliveredCount) : 0 ?></h4>
                        <div class="small"><?= isset($lang['delivered_shipments']) ? $lang['delivered_shipments'] : 'Delivered Shipments' ?></div>
                    </div>
                    <div class="fa-3x">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=shipments&status=delivered"><?= isset($lang['view_details']) ? $lang['view_details'] : 'View Details' ?></a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0"><?= isset($inTransitCount) ? number_format($inTransitCount) : 0 ?></h4>
                        <div class="small"><?= isset($lang['in_transit_shipments']) ? $lang['in_transit_shipments'] : 'In Transit Shipments' ?></div>
                    </div>
                    <div class="fa-3x">
                        <i class="fas fa-truck-moving"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=shipments&status=in_transit"><?= isset($lang['view_details']) ? $lang['view_details'] : 'View Details' ?></a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header and Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            <i class="fas fa-shipping-fast me-2"></i><?= isset($lang['shipments']) ? $lang['shipments'] : 'Shipments' ?>
        </h1>
        <div class="btn-toolbar">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-secondary" id="toggle-filters">
                    <i class="fas fa-filter me-1"></i> <?= isset($lang['filters']) ? $lang['filters'] : 'Filters' ?>
                </button>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-export me-1"></i> <?= isset($lang['export']) ? $lang['export'] : 'Export' ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="index.php?page=shipments&action=export&format=excel"><i class="fas fa-file-excel me-2"></i> Excel</a></li>
                    <li><a class="dropdown-item" href="index.php?page=shipments&action=export&format=pdf"><i class="fas fa-file-pdf me-2"></i> PDF</a></li>
                    <li><a class="dropdown-item" href="index.php?page=shipments&action=export&format=csv"><i class="fas fa-file-csv me-2"></i> CSV</a></li>
                </ul>
            </div>
            <div class="btn-group">
                <a href="index.php?page=shipments&action=create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> <?= isset($lang['add_shipment']) ? $lang['add_shipment'] : 'Add Shipment' ?>
                </a>
                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="index.php?page=shipments&action=import"><i class="fas fa-file-import me-2"></i> <?= isset($lang['import']) ? $lang['import'] : 'Import' ?></a></li>
                    <li><a class="dropdown-item" href="index.php?page=shipments&action=batch"><i class="fas fa-layer-group me-2"></i> <?= isset($lang['batch_update']) ? $lang['batch_update'] : 'Batch Update' ?></a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Panel (Hidden by default) -->
    <div class="card mb-4" id="filters-panel" style="display: none;">
        <div class="card-header bg-light">
            <i class="fas fa-filter me-1"></i>
            <?= isset($lang['advanced_filters']) ? $lang['advanced_filters'] : 'Advanced Filters' ?>
        </div>
        <div class="card-body">
            <form action="index.php" method="get" id="advanced-filter-form">
                <input type="hidden" name="page" value="shipments">
                <input type="hidden" name="p" value="1">
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="date_range" class="form-label"><?= isset($lang['date_range']) ? $lang['date_range'] : 'Date Range' ?></label>
                        <select name="date_range" id="date_range" class="form-select">
                            <option value=""><?= isset($lang['all_time']) ? $lang['all_time'] : 'All Time' ?></option>
                            <option value="today" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'today') ? 'selected' : '' ?>><?= isset($lang['today']) ? $lang['today'] : 'Today' ?></option>
                            <option value="yesterday" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'yesterday') ? 'selected' : '' ?>><?= isset($lang['yesterday']) ? $lang['yesterday'] : 'Yesterday' ?></option>
                            <option value="this_week" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'this_week') ? 'selected' : '' ?>><?= isset($lang['this_week']) ? $lang['this_week'] : 'This Week' ?></option>
                            <option value="last_week" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'last_week') ? 'selected' : '' ?>><?= isset($lang['last_week']) ? $lang['last_week'] : 'Last Week' ?></option>
                            <option value="this_month" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'this_month') ? 'selected' : '' ?>><?= isset($lang['this_month']) ? $lang['this_month'] : 'This Month' ?></option>
                            <option value="last_month" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'last_month') ? 'selected' : '' ?>><?= isset($lang['last_month']) ? $lang['last_month'] : 'Last Month' ?></option>
                            <option value="custom" <?= (isset($_GET['date_range']) && $_GET['date_range'] == 'custom') ? 'selected' : '' ?>><?= isset($lang['custom_range']) ? $lang['custom_range'] : 'Custom Range' ?></option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 custom-date-range" style="display: none;">
                        <label for="date_from" class="form-label"><?= isset($lang['date_from']) ? $lang['date_from'] : 'Date From' ?></label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="<?= isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : '' ?>">
                    </div>
                    
                    <div class="col-md-3 custom-date-range" style="display: none;">
                        <label for="date_to" class="form-label"><?= isset($lang['date_to']) ? $lang['date_to'] : 'Date To' ?></label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="<?= isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : '' ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label"><?= isset($lang['status']) ? $lang['status'] : 'Status' ?></label>
                        <select name="status" id="status" class="form-select">
                            <option value=""><?= isset($lang['all_statuses']) ? $lang['all_statuses'] : 'All Statuses' ?></option>
                            <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>><?= isset($lang['pending']) ? $lang['pending'] : 'Pending' ?></option>
                            <option value="processing" <?= (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'selected' : '' ?>><?= isset($lang['processing']) ? $lang['processing'] : 'Processing' ?></option>
                            <option value="in_transit" <?= (isset($_GET['status']) && $_GET['status'] == 'in_transit') ? 'selected' : '' ?>><?= isset($lang['in_transit']) ? $lang['in_transit'] : 'In Transit' ?></option>
                            <option value="local_delivery" <?= (isset($_GET['status']) && $_GET['status'] == 'local_delivery') ? 'selected' : '' ?>><?= isset($lang['local_delivery']) ? $lang['local_delivery'] : 'Local Delivery' ?></option>
                            <option value="out_for_delivery" <?= (isset($_GET['status']) && $_GET['status'] == 'out_for_delivery') ? 'selected' : '' ?>><?= isset($lang['out_for_delivery']) ? $lang['out_for_delivery'] : 'Out for Delivery' ?></option>
                            <option value="delivered" <?= (isset($_GET['status']) && $_GET['status'] == 'delivered') ? 'selected' : '' ?>><?= isset($lang['delivered']) ? $lang['delivered'] : 'Delivered' ?></option>
                            <option value="cancelled" <?= (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : '' ?>><?= isset($lang['cancelled']) ? $lang['cancelled'] : 'Cancelled' ?></option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="lot_id" class="form-label"><?= isset($lang['lot']) ? $lang['lot'] : 'Lot' ?></label>
                        <select name="lot_id" id="lot_id" class="form-select">
                            <option value=""><?= isset($lang['all_lots']) ? $lang['all_lots'] : 'All Lots' ?></option>
                            <?php 
                            if (isset($lotModel) && method_exists($lotModel, 'getAll')) {
                                $lots = $lotModel->getAll();
                                if (is_array($lots)) {
                                    foreach ($lots as $lot): 
                                    ?>
                                        <option value="<?= $lot['id'] ?>" <?= (isset($_GET['lot_id']) && $_GET['lot_id'] == $lot['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($lot['lot_number']) ?>
                                        </option>
                                    <?php 
                                    endforeach;
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="customer_code" class="form-label"><?= isset($lang['customer_code']) ? $lang['customer_code'] : 'Customer Code' ?></label>
                        <input type="text" name="customer_code" id="customer_code" class="form-control" placeholder="<?= isset($lang['enter_customer_code']) ? $lang['enter_customer_code'] : 'Enter customer code' ?>" value="<?= isset($_GET['customer_code']) ? htmlspecialchars($_GET['customer_code']) : '' ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="search" class="form-label"><?= isset($lang['search']) ? $lang['search'] : 'Search' ?></label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="<?= isset($lang['search_tracking_sender_receiver']) ? $lang['search_tracking_sender_receiver'] : 'Search tracking #, sender, receiver' ?>" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="weight_min" class="form-label"><?= isset($lang['weight_range']) ? $lang['weight_range'] : 'Weight Range' ?> (kg)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="weight_min" id="weight_min" class="form-control" placeholder="<?= isset($lang['min']) ? $lang['min'] : 'Min' ?>" value="<?= isset($_GET['weight_min']) ? htmlspecialchars($_GET['weight_min']) : '' ?>">
                            <span class="input-group-text">-</span>
                            <input type="number" step="0.01" min="0" name="weight_max" id="weight_max" class="form-control" placeholder="<?= isset($lang['max']) ? $lang['max'] : 'Max' ?>" value="<?= isset($_GET['weight_max']) ? htmlspecialchars($_GET['weight_max']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="price_min" class="form-label"><?= isset($lang['price_range']) ? $lang['price_range'] : 'Price Range' ?> (฿)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="price_min" id="price_min" class="form-control" placeholder="<?= isset($lang['min']) ? $lang['min'] : 'Min' ?>" value="<?= isset($_GET['price_min']) ? htmlspecialchars($_GET['price_min']) : '' ?>">
                            <span class="input-group-text">-</span>
                            <input type="number" step="0.01" min="0" name="price_max" id="price_max" class="form-control" placeholder="<?= isset($lang['max']) ? $lang['max'] : 'Max' ?>" value="<?= isset($_GET['price_max']) ? htmlspecialchars($_GET['price_max']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="sort_by" class="form-label"><?= isset($lang['sort_by']) ? $lang['sort_by'] : 'Sort By' ?></label>
                        <select name="sort_by" id="sort_by" class="form-select">
                            <option value="created_at_desc" <?= (!isset($_GET['sort_by']) || $_GET['sort_by'] == 'created_at_desc') ? 'selected' : '' ?>><?= isset($lang['newest_first']) ? $lang['newest_first'] : 'Newest First' ?></option>
                            <option value="created_at_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'created_at_asc') ? 'selected' : '' ?>><?= isset($lang['oldest_first']) ? $lang['oldest_first'] : 'Oldest First' ?></option>
                            <option value="tracking_number_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'tracking_number_asc') ? 'selected' : '' ?>><?= isset($lang['tracking_number_asc']) ? $lang['tracking_number_asc'] : 'Tracking # (A-Z)' ?></option>
                            <option value="tracking_number_desc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'tracking_number_desc') ? 'selected' : '' ?>><?= isset($lang['tracking_number_desc']) ? $lang['tracking_number_desc'] : 'Tracking # (Z-A)' ?></option>
                            <option value="weight_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'weight_asc') ? 'selected' : '' ?>><?= isset($lang['weight_asc']) ? $lang['weight_asc'] : 'Weight (Low to High)' ?></option>
                            <option value="weight_desc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'weight_desc') ? 'selected' : '' ?>><?= isset($lang['weight_desc']) ? $lang['weight_desc'] : 'Weight (High to Low)' ?></option>
                            <option value="price_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_asc') ? 'selected' : '' ?>><?= isset($lang['price_asc']) ? $lang['price_asc'] : 'Price (Low to High)' ?></option>
                            <option value="price_desc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_desc') ? 'selected' : '' ?>><?= isset($lang['price_desc']) ? $lang['price_desc'] : 'Price (High to Low)' ?></option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="per_page" class="form-label"><?= isset($lang['items_per_page']) ? $lang['items_per_page'] : 'Items Per Page' ?></label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="10" <?= (!isset($_GET['per_page']) || $_GET['per_page'] == '10') ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= (isset($_GET['per_page']) && $_GET['per_page'] == '25') ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= (isset($_GET['per_page']) && $_GET['per_page'] == '50') ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= (isset($_GET['per_page']) && $_GET['per_page'] == '100') ? 'selected' : '' ?>>100</option>
                            <option value="250" <?= (isset($_GET['per_page']) && $_GET['per_page'] == '250') ? 'selected' : '' ?>>250</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-3">
                    <a href="index.php?page=shipments" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-1"></i> <?= isset($lang['reset']) ? $lang['reset'] : 'Reset' ?>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> <?= isset($lang['apply_filters']) ? $lang['apply_filters'] : 'Apply Filters' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Search Bar -->
    <div class="card mb-4">
        <div class="card-body p-2">
            <form action="index.php" method="get" id="quick-search-form" class="d-flex">
                <input type="hidden" name="page" value="shipments">
                <input type="hidden" name="p" value="1">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="<?= isset($lang['search_tracking_sender_receiver']) ? $lang['search_tracking_sender_receiver'] : 'Search tracking #, sender, receiver' ?>" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button type="submit" class="btn btn-primary"><?= isset($lang['search']) ? $lang['search'] : 'Search' ?></button>
                    <?php if (isset($_GET['search']) || isset($_GET['status']) || isset($_GET['lot_id']) || isset($_GET['customer_code'])): ?>
                        <a href="index.php?page=shipments" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> <?= isset($lang['reset']) ? $lang['reset'] : 'Reset' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                <?= isset($lang['shipment_list']) ? $lang['shipment_list'] : 'Shipment List' ?>
                <?php if (isset($_GET['search']) || isset($_GET['status']) || isset($_GET['lot_id']) || isset($_GET['customer_code'])): ?>
                    <span class="badge bg-info ms-2"><?= isset($lang['filtered']) ? $lang['filtered'] : 'Filtered' ?></span>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center">
                <a href="index.php?page=shipment_labels" class="btn btn-info me-2">
                    <i class="bi bi-printer"></i> <?= isset($lang['print_parcel_tag']) ? $lang['print_parcel_tag'] : 'พิมพ์ Tag พัสดุ' ?>
                </a>
                <div class="me-3">
                    <?php if (isset($shipments) && is_array($shipments) && count($shipments) > 0): ?>
                        <span class="text-muted small">
                            <?php 
                            $showing_from = isset($page) && isset($limit) ? ($page - 1) * $limit + 1 : 1;
                            $showing_to = isset($page) && isset($limit) && isset($shipments) ? min(($page - 1) * $limit + count($shipments), $totalShipments ?? count($shipments)) : count($shipments);
                            $total = isset($totalShipments) ? $totalShipments : count($shipments);
                            
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
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="toggle-view" data-view="table">
                        <i class="fas fa-th-list"></i>
                    </button>
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

            <!-- Table View -->
            <div id="table-view">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 border-bottom">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="border-top-0">#</th>
                                <th width="15%" class="border-top-0"><?= isset($lang['tracking_number']) ? $lang['tracking_number'] : 'Tracking Number' ?></th>
                                <th width="12%" class="border-top-0"><?= isset($lang['customer_code']) ? $lang['customer_code'] : 'Customer Code' ?></th>
                                <th width="12%" class="border-top-0"><?= isset($lang['sender_name']) ? $lang['sender_name'] : 'Sender Name' ?></th>
                                <th width="12%" class="border-top-0"><?= isset($lang['receiver_name']) ? $lang['receiver_name'] : 'Receiver Name' ?></th>
                                <th width="8%" class="border-top-0"><?= isset($lang['weight']) ? $lang['weight'] : 'Weight' ?> (kg)</th>
                                <th width="8%" class="border-top-0"><?= isset($lang['price']) ? $lang['price'] : 'Price' ?> (฿)</th>
                                <th width="10%" class="border-top-0"><?= isset($lang['status']) ? $lang['status'] : 'Status' ?></th>
                                <th width="10%" class="border-top-0"><?= isset($lang['created_date']) ? $lang['created_date'] : 'Created Date' ?></th>
                                <th width="8%" class="border-top-0 text-center"><?= isset($lang['actions']) ? $lang['actions'] : 'Actions' ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($shipments) && is_array($shipments) && count($shipments) > 0): ?>
                                <?php 
                                $counter = isset($page) && isset($limit) ? ($page - 1) * $limit + 1 : 1;
                                foreach ($shipments as $shipment): 
                                ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td>
                                            <a href="index.php?page=tracking&tracking_number=<?= htmlspecialchars($shipment['tracking_number']) ?>" class="fw-bold text-primary">
                                                <?= htmlspecialchars($shipment['tracking_number']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($shipment['customer_code'] ?? (isset($lang['not_available']) ? $lang['not_available'] : 'N/A')) ?></td>
                                        <td><?= htmlspecialchars($shipment['sender_name']) ?></td>
                                        <td><?= htmlspecialchars($shipment['receiver_name']) ?></td>
                                        <td><?= number_format((float)($shipment['weight'] ?? 0), 2) ?></td>
                                        <td><?= number_format((float)($shipment['price'] ?? 0), 2) ?></td>
                                        <td>
                                            <span class="badge <?= getStatusBadgeClass($shipment['status'] ?? 'pending') ?>">
                                                <?= isset($lang[$shipment['status']]) ? $lang[$shipment['status']] : ($shipment['status'] ?? 'Pending') ?>
                                            </span>
                                        </td>
                                        <td><?= isset($shipment['created_at']) ? date('d/m/Y', strtotime($shipment['created_at'])) : '-' ?></td>
                                        
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="index.php?page=shipments&action=view&id=<?= $shipment['id'] ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="<?= isset($lang['view']) ? $lang['view'] : 'View' ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="index.php?page=shipments&action=edit&id=<?= $shipment['id'] ?>" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="<?= isset($lang['edit']) ? $lang['edit'] : 'Edit' ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?page=shipment_labels&action=printLabel&id=<?php echo $shipment['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="<?= isset($lang['print_tag']) ? $lang['print_tag'] : 'พิมพ์ Tag' ?>">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $shipment['id'] ?>" data-tracking="<?= htmlspecialchars($shipment['tracking_number']) ?>" data-bs-toggle="tooltip" title="<?= isset($lang['delete']) ? $lang['delete'] : 'Delete' ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <p><?= isset($lang['no_shipments_found']) ? $lang['no_shipments_found'] : 'No shipments found' ?></p>
                                            <a href="index.php?page=shipments&action=create" class="btn btn-sm btn-primary mt-2">
                                                <i class="fas fa-plus me-1"></i> <?= isset($lang['add_shipment']) ? $lang['add_shipment'] : 'Add Shipment' ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card View (Hidden by default) -->
            <div id="card-view" class="p-3" style="display: none;">
                <div class="row g-3">
                    <?php if (isset($shipments) && is_array($shipments) && count($shipments) > 0): ?>
                        <?php foreach ($shipments as $shipment): ?>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                        <span class="badge <?= getStatusBadgeClass($shipment['status'] ?? 'pending') ?>">
                                            <?= isset($lang[$shipment['status']]) ? $lang[$shipment['status']] : ($shipment['status'] ?? 'Pending') ?>
                                        </span>
                                        
                                        <div class="btn-group btn-group-sm">
                                            <a href="index.php?page=shipments&action=view&id=<?= $shipment['id'] ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="<?= isset($lang['view']) ? $lang['view'] : 'View' ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?page=shipments&action=edit&id=<?= $shipment['id'] ?>" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="<?= isset($lang['edit']) ? $lang['edit'] : 'Edit' ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?page=shipments&action=print&id=<?= $shipment['id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="<?= isset($lang['print']) ? $lang['print'] : 'Print' ?>">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $shipment['id'] ?>" data-tracking="<?= htmlspecialchars($shipment['tracking_number']) ?>" data-bs-toggle="tooltip" title="<?= isset($lang['delete']) ? $lang['delete'] : 'Delete' ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="index.php?page=tracking&tracking_number=<?= htmlspecialchars($shipment['tracking_number']) ?>" class="text-primary text-decoration-none">
                                                <?= htmlspecialchars($shipment['tracking_number']) ?>
                                            </a>
                                        </h6>
                                        <div class="mb-2 small">
                                            <i class="fas fa-user text-muted me-1"></i> <?= htmlspecialchars($shipment['sender_name']) ?> → <?= htmlspecialchars($shipment['receiver_name']) ?>
                                        </div>
                                        <div class="mb-2 small">
                                            <i class="fas fa-tag text-muted me-1"></i> <?= htmlspecialchars($shipment['customer_code'] ?? (isset($lang['not_available']) ? $lang['not_available'] : 'N/A')) ?>
                                        </div>
                                        <div class="d-flex justify-content-between small">
                                            <div>
                                                <i class="fas fa-weight text-muted me-1"></i> <?= number_format((float)($shipment['weight'] ?? 0), 2) ?> kg
                                            </div>
                                            <div>
                                                <i class="fas fa-dollar-sign text-muted me-1"></i> <?= number_format((float)($shipment['price'] ?? 0), 2) ?> ฿
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent text-muted small">
                                        <i class="far fa-calendar-alt me-1"></i> <?= isset($shipment['created_at']) ? date('d/m/Y', strtotime($shipment['created_at'])) : '-' ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                <p><?= isset($lang['no_shipments_found']) ? $lang['no_shipments_found'] : 'No shipments found' ?></p>
                                <a href="index.php?page=shipments&action=create" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i> <?= isset($lang['add_shipment']) ? $lang['add_shipment'] : 'Add Shipment' ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <label for="per_page_bottom" class="me-2 mb-0 small"><?= isset($lang['show']) ? $lang['show'] : 'Show' ?>:</label>
                    <select id="per_page_bottom" class="form-select form-select-sm" style="width: 70px;">
                        <option value="10" <?= (!isset($_GET['per_page']) || $_GET['per_page'] == '10') ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= (isset($_GET['per_page']) && $_GET['per_page'] == '25') ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= (isset($_GET['per_page']) && $_GET['per_page'] == '50') ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= (isset($_GET['per_page']) && $_GET['per_page'] == '100') ? 'selected' : '' ?>>100</option>
                        <option value="250" <?= (isset($_GET['per_page']) && $_GET['per_page'] == '250') ? 'selected' : '' ?>>250</option>
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
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel"><?= isset($lang['confirm_delete']) ? $lang['confirm_delete'] : 'Confirm Delete' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle text-danger fa-4x"></i>
                </div>
                <p class="text-center"><?= isset($lang['confirm_delete_shipment']) ? $lang['confirm_delete_shipment'] : 'Are you sure you want to delete shipment' ?> <span id="delete-tracking" class="fw-bold"></span>?</p>
                <p class="text-center text-danger small"><?= isset($lang['action_cannot_be_undone']) ? $lang['action_cannot_be_undone'] : 'This action cannot be undone' ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= isset($lang['cancel']) ? $lang['cancel'] : 'Cancel' ?></button>
                <a href="#" id="delete-confirm-btn" class="btn btn-danger"><?= isset($lang['delete']) ? $lang['delete'] : 'Delete' ?></a>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to get appropriate badge class based on status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'processing':
            return 'bg-info text-dark';
        case 'in_transit':
            return 'bg-primary';
        case 'local_delivery':
            return 'bg-info';
        case 'out_for_delivery':
            return 'bg-info';
        case 'delivered':
            return 'bg-success';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle filters panel
    const toggleFiltersBtn = document.getElementById('toggle-filters');
    const filtersPanel = document.getElementById('filters-panel');
    
    if (toggleFiltersBtn && filtersPanel) {
        toggleFiltersBtn.addEventListener('click', function() {
            if (filtersPanel.style.display === 'none') {
                filtersPanel.style.display = 'block';
                toggleFiltersBtn.classList.add('active');
            } else {
                filtersPanel.style.display = 'none';
                toggleFiltersBtn.classList.remove('active');
            }
        });
    }
    
    // Toggle view (table/card)
    const toggleViewBtn = document.getElementById('toggle-view');
    const tableView = document.getElementById('table-view');
    const cardView = document.getElementById('card-view');
    
    if (toggleViewBtn && tableView && cardView) {
        toggleViewBtn.addEventListener('click', function() {
            const currentView = this.getAttribute('data-view');
            
            if (currentView === 'table') {
                tableView.style.display = 'none';
                cardView.style.display = 'block';
                this.setAttribute('data-view', 'card');
                this.innerHTML = '<i class="fas fa-table"></i>';
            } else {
                tableView.style.display = 'block';
                cardView.style.display = 'none';
                this.setAttribute('data-view', 'table');
                this.innerHTML = '<i class="fas fa-th-list"></i>';
            }
        });
    }
    
    // Custom date range toggle
    const dateRangeSelect = document.getElementById('date_range');
    const customDateRangeFields = document.querySelectorAll('.custom-date-range');
    
    if (dateRangeSelect && customDateRangeFields.length > 0) {
        dateRangeSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRangeFields.forEach(field => {
                    field.style.display = 'block';
                });
            } else {
                customDateRangeFields.forEach(field => {
                    field.style.display = 'none';
                });
            }
        });
        
        // Trigger change event on page load
        if (dateRangeSelect.value === 'custom') {
            customDateRangeFields.forEach(field => {
                field.style.display = 'block';
            });
        }
    }
    
    // Delete modal functionality
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteTrackingSpan = document.getElementById('delete-tracking');
    const deleteConfirmBtn = document.getElementById('delete-confirm-btn');
    
    if (deleteButtons.length > 0 && deleteTrackingSpan && deleteConfirmBtn) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const tracking = this.getAttribute('data-tracking');
                
                deleteTrackingSpan.textContent = tracking;
                deleteConfirmBtn.href = 'index.php?page=shipments&action=delete&id=' + id;
                
                // Use Bootstrap 5 modal API
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });
    }
    
    // Items per page change
    const perPageSelect = document.getElementById('per_page_bottom');
    
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('per_page', this.value);
            currentUrl.searchParams.set('p', '1'); // Reset to first page
            window.location.href = currentUrl.toString();
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
                currentUrl.searchParams.set('p', page);
                
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

<?php include 'views/layout/footer.php'; ?>

