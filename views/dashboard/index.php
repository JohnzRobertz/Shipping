<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid mt-4 px-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?= __('dashboard') ?></h1>
                <div>
                    <span class="text-muted"><?= date('l, d M Y') ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($showDebug) && $showDebug): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Debug Information</h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#debugInfo" aria-expanded="false">
                        Toggle Debug Info
                    </button>
                </div>
                <div class="card-body collapse" id="debugInfo">
                    <h6>Database Tables</h6>
                    <?php if (isset($debugInfo['tables'])): ?>
                        <pre><?= print_r($debugInfo['tables'], true) ?></pre>
                        
                        <?php if (in_array('shipments', $debugInfo['tables'])): ?>
                            <h6>Shipments Table Structure</h6>
                            <pre><?= print_r($debugInfo['shipments_columns'], true) ?></pre>
                            
                            <h6>Total Shipments</h6>
                            <p>Total: <?= $debugInfo['shipments_count'] ?></p>
                            
                            <?php if ($debugInfo['shipments_count'] > 0): ?>
                                <h6>Sample Shipment Data</h6>
                                <pre><?= print_r($debugInfo['sample_shipment'], true) ?></pre>
                            <?php else: ?>
                                <div class="alert alert-warning">No shipments found in database.</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-danger">Shipments table does not exist!</div>
                        <?php endif; ?>
                        
                        <?php if (in_array('lots', $debugInfo['tables'])): ?>
                            <h6>Lots Table Structure</h6>
                            <pre><?= print_r($debugInfo['lots_columns'] ?? [], true) ?></pre>
                            
                            <?php if (isset($debugInfo['sample_lot'])): ?>
                                <h6>Sample Lot Data</h6>
                                <pre><?= print_r($debugInfo['sample_lot'], true) ?></pre>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php elseif (isset($debugInfo['error'])): ?>
                        <div class="alert alert-danger">Error: <?= $debugInfo['error'] ?></div>
                    <?php endif; ?>
                    
                    <h6>Summary Data</h6>
                    <pre><?= print_r($summaryData, true) ?></pre>
                    
                    <h6>Recent Shipments</h6>
                    <pre><?= print_r($recentShipments, true) ?></pre>
                    
                    <h6>Status Counts</h6>
                    <pre><?= print_r($statusCounts, true) ?></pre>
                    
                    <h6>Monthly Revenue</h6>
                    <pre><?= print_r($monthlyRevenue, true) ?></pre>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= __('total_revenue') ?></h6>
                            <h2 class="mt-2 mb-0">฿<?= number_format($summaryData['total_revenue'] ?? 0, 2) ?></h2>
                        </div>
                        <div class="display-4">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-white-50">
                        <?= __('month') ?>: ฿<?= number_format($summaryData['monthly_revenue'] ?? 0, 2) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= __('total_shipments') ?></h6>
                            <h2 class="mt-2 mb-0"><?= number_format($summaryData['total_shipments'] ?? 0) ?></h2>
                        </div>
                        <div class="display-4">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-white-50">
                        <?= __('month') ?>: <?= number_format($summaryData['monthly_shipments'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= __('total_weight') ?></h6>
                            <h2 class="mt-2 mb-0"><?= number_format($summaryData['total_weight'] ?? 0, 2) ?> kg</h2>
                        </div>
                        <div class="display-4">
                            <i class="bi bi-stack"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-white-50">
                        <?php 
                        $avgWeight = 0;
                        if (isset($summaryData['total_shipments']) && $summaryData['total_shipments'] > 0 && isset($summaryData['total_weight'])) {
                            $avgWeight = $summaryData['total_weight'] / $summaryData['total_shipments'];
                        }
                        ?>
                        <?= __('avg_per_shipment') ?>: <?= number_format($avgWeight, 2) ?> kg
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= __('avg_price_per_kg') ?></h6>
                            <?php 
                            $avgPrice = 0;
                            if (isset($summaryData['total_weight']) && $summaryData['total_weight'] > 0 && isset($summaryData['total_revenue'])) {
                                $avgPrice = $summaryData['total_revenue'] / $summaryData['total_weight'];
                            }
                            ?>
                            <h2 class="mt-2 mb-0">฿<?= number_format($avgPrice, 2) ?></h2>
                        </div>
                        <div class="display-4">
                            <i class="bi bi-calculator"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-dark-50">
                        <?= __('based_on_all_shipments') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?= __('monthly_revenue') ?></h5>
                </div>
                <div class="card-body">
                    <div style="height: 250px">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?= __('shipment_status') ?></h5>
                </div>
                <div class="card-body">
                    <div style="height: 250px">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Shipments -->
    
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i><?php echo __('recent_shipments'); ?></h5>
            <a href="index.php?page=shipments" class="btn btn-sm btn-primary">
                <?php echo __('view_all'); ?> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentShipments)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox display-6 d-block mb-3"></i>
                <?php echo __('no_shipments_found'); ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo __('tracking_number'); ?></th>
                            <th><?php echo __('customer_code'); ?></th>
                            <th><?php echo __('sender'); ?></th>
                            <th><?php echo __('receiver'); ?></th>
                            <th><?php echo __('status'); ?></th>
                            <th class="text-center"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentShipments as $shipment): ?>
                            <tr>
                                <td>
                                    <a href="index.php?page=shipments&action=view&id=<?php echo $shipment['id']; ?>" class="fw-bold text-decoration-none">
                                        <?php echo htmlspecialchars($shipment['tracking_number']); ?>
                                    </a>
                                </td>
                                <td><?php echo !empty($shipment['customer_code']) ? htmlspecialchars($shipment['customer_code']) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($shipment['sender_name']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['receiver_name']); ?></td>
                                <td>
                                    <?php 
                                    $statusMap = [
                                        'received' => ['badge' => 'bg-secondary', 'text' => 'รับเข้าระบบแล้ว'],
                                        'in_transit' => ['badge' => 'bg-primary', 'text' => 'อยู่ระหว่างขนส่ง'],
                                        'local_delivery' => ['badge' => 'bg-info', 'text' => 'ส่งในประเทศ'],
                                        'arrived_destination' => ['badge' => 'bg-info', 'text' => 'ถึงปลายทางแล้ว'],
                                        'out_for_delivery' => ['badge' => 'bg-warning', 'text' => 'กำลังนำส่ง'],
                                        'delivered' => ['badge' => 'bg-success', 'text' => 'ส่งมอบแล้ว']
                                    ];
                                    
                                    $status = $shipment['status'];
                                    $statusBadge = isset($statusMap[$status]) ? $statusMap[$status]['badge'] : 'bg-secondary';
                                    $statusText = isset($statusMap[$status]) ? $statusMap[$status]['text'] : (function_exists('__') ? __('status_' . $status) : $status);
                                    ?>
                                    <span class="badge <?php echo $statusBadge; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="index.php?page=shipments&action=view&id=<?php echo $shipment['id']; ?>" 
                                           class="btn btn-sm btn-info" title="<?php echo __('view'); ?>">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="index.php?page=shipments&action=edit&id=<?php echo $shipment['id']; ?>" 
                                           class="btn btn-sm btn-warning" title="<?php echo __('edit'); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- ลบส่วน Pagination ออก -->
            <div class="card-footer text-center">
                <a href="index.php?page=shipments" class="btn btn-sm btn-outline-primary">
                    <?php echo __('view_all_shipments'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>


    <!-- Recent Lots -->
    
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-boxes me-2"></i><?php echo __('recent_lots'); ?></h5>
            <a href="index.php?page=lots" class="btn btn-sm btn-primary">
                <?php echo __('view_all'); ?> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentLots)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox display-6 d-block mb-3"></i>
                <?php echo __('no_lots_found'); ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo __('lot_number'); ?></th>
                            <th><?php echo __('lot_type'); ?></th>
                            <th><?php echo __('origin'); ?></th>
                            <th><?php echo __('destination'); ?></th>
                            <th><?php echo __('status'); ?></th>
                            <th class="text-center"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLots as $lot): ?>
                            <tr>
                                <td>
                                    <a href="index.php?page=lots&action=view&id=<?php echo $lot['id']; ?>" class="fw-bold text-decoration-none">
                                        <?php echo htmlspecialchars($lot['lot_number']); ?>
                                    </a>
                                </td>
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
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="index.php?page=lots&action=view&id=<?php echo $lot['id']; ?>" 
                                           class="btn btn-sm btn-info" title="<?php echo __('view'); ?>">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="index.php?page=lots&action=edit&id=<?php echo $lot['id']; ?>" 
                                           class="btn btn-sm btn-warning" title="<?php echo __('edit'); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- ลบส่วน Pagination ออก -->
            <div class="card-footer text-center">
                <a href="index.php?page=lots" class="btn btn-sm btn-outline-primary">
                    <?php echo __('view_all_lots'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart colors
    const primaryColor = '#0d6efd';
    const successColor = '#198754';
    const warningColor = '#ffc107';
    const infoColor = '#0dcaf0';
    const dangerColor = '#dc3545';
    
    // Status chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = <?= json_encode($statusCounts) ?>;

    if (Object.keys(statusData).length > 0 && Object.values(statusData).some(value => value > 0)) {
        const statusLabels = Object.keys(statusData).map(status => {
            // Try to get localized status label
            return '<?= __("' + status + '") ?>';
        });
        const statusValues = Object.values(statusData);
        
        const statusColors = [primaryColor, successColor, warningColor, infoColor, dangerColor, '#6f42c1'];
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: true,
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });
    } else {
        // No data available
        document.getElementById('statusChart').parentNode.innerHTML = '<div class="alert alert-info text-center my-5"><?= __("no_data_available") ?></div>';
    }
    
    // Revenue chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = <?= json_encode($monthlyRevenue) ?>;

    if (revenueData.length > 0 && revenueData.some(item => item.revenue > 0)) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: revenueData.map(item => item.month),
                datasets: [{
                    label: '<?= __("revenue") ?>',
                    data: revenueData.map(item => item.revenue),
                    backgroundColor: primaryColor,
                    borderColor: primaryColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '฿' + new Intl.NumberFormat().format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '<?= __("revenue") ?>: ฿' + new Intl.NumberFormat().format(context.raw);
                            }
                        }
                    }
                }
            }
        });
    } else {
        // No data available
        document.getElementById('revenueChart').parentNode.innerHTML = '<div class="alert alert-info text-center my-5"><?= __("no_data_available") ?></div>';
    }
});
</script>

<?php require_once 'views/layout/footer.php'; ?>

