<?php
// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
$isLoggedIn = isset($_SESSION['user_id']);

// ตรวจสอบว่าผู้ใช้เป็นผู้ดูแลระบบหรือไม่
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>

<div class="container mt-4">

    <?php if (!isLoggedIn()): ?>
        <!-- ส่วนสำหรับผู้ใช้ที่ยังไม่ได้เข้าสู่ระบบ - แสดงเฉพาะส่วนติดตามพัสดุ -->
        <div class="row justify-content-center">
            <div class="col-md-8 text-center mb-5">
                <h1 class="display-4 mb-3"><?php echo __('welcome_to_shipping_system'); ?></h1>
                <p class="lead"><?php echo __('track_your_shipment_below'); ?></p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">
                            <i class="bi bi-search me-2"></i> <?php echo __('track_shipment'); ?>
                        </h2>
                        
                        <form action="index.php" method="get" class="mb-3">
                            <input type="hidden" name="page" value="tracking">
                            <div class="input-group mb-3">
                                <input type="text" name="tracking_number" class="form-control form-control-lg" 
                                    placeholder="<?php echo __('enter_tracking_number'); ?>" required>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-search me-2"></i> <?php echo __('track'); ?>
                                </button>
                            </div>
                        </form>
                        
                    </div>
                </div>
                
                <!-- ข้อมูลเพิ่มเติมเกี่ยวกับบริการ -->
                <div class="card shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-3"><?php echo __('our_services'); ?></h3>
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <div class="p-3">
                                    <i class="bi bi-airplane fs-1 text-primary mb-3"></i>
                                    <h5><?php echo __('air_shipping'); ?></h5>
                                    <p class="small text-muted"><?php echo __('fast_reliable_air_shipping'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3">
                                    <i class="bi bi-water fs-1 text-primary mb-3"></i>
                                    <h5><?php echo __('sea_shipping'); ?></h5>
                                    <p class="small text-muted"><?php echo __('cost_effective_sea_shipping'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3">
                                    <i class="bi bi-truck fs-1 text-primary mb-3"></i>
                                    <h5><?php echo __('land_shipping'); ?></h5>
                                    <p class="small text-muted"><?php echo __('reliable_land_shipping'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- แสดงเนื้อหาสำหรับผู้ใช้ที่ล็อกอินแล้ว -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h2><?php echo __('dashboard'); ?></h2>
            </div>
        </div>

        <!-- แสดงสถิติ -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo __('total_shipments'); ?></h5>
                        <h2 class="display-4"><?php echo number_format($totalShipments); ?></h2>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="index.php?page=shipments" class="text-white"><?php echo __('view_details'); ?></a>
                        <i class="bi bi-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo __('total_lots'); ?></h5>
                        <h2 class="display-4"><?php echo number_format($totalLots); ?></h2>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="index.php?page=lots" class="text-white"><?php echo __('view_details'); ?></a>
                        <i class="bi bi-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo __('pending_shipments'); ?></h5>
                        <h2 class="display-4"><?php echo number_format($pendingShipments); ?></h2>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="index.php?page=shipments&status=pending" class="text-white"><?php echo __('view_details'); ?></a>
                        <i class="bi bi-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo __('processing_shipments'); ?></h5>
                        <h2 class="display-4"><?php echo number_format($processingShipments); ?></h2>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="index.php?page=shipments&status=processing" class="text-white"><?php echo __('view_details'); ?></a>
                        <i class="bi bi-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ปุ่มลัด -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title"><?php echo __('quick_actions'); ?></h5>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="index.php?page=shipments&action=create" class="btn btn-primary me-2">
                                <i class="bi bi-plus-circle"></i> <?php echo __('create_shipment'); ?>
                            </a>
                            <a href="index.php?page=lots&action=create" class="btn btn-success me-2">
                                <i class="bi bi-plus-circle"></i> <?php echo __('create_lot'); ?>
                            </a>
                            <a href="index.php?page=shipments&action=import" class="btn btn-info me-2">
                                <i class="bi bi-upload"></i> <?php echo __('import_shipments'); ?>
                            </a>
                            <a href="index.php?page=tracking" class="btn btn-secondary">
                                <i class="bi bi-search"></i> <?php echo __('track_shipment'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- พัสดุล่าสุด -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo __('recent_shipments'); ?></h5>
                        <a href="index.php?page=shipments" class="btn btn-sm btn-primary">
                            <?php echo __('view_all'); ?>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentShipments)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('tracking_number'); ?></th>
                                            <th><?php echo __('sender'); ?></th>
                                            <th><?php echo __('receiver'); ?></th>
                                            <th><?php echo __('status'); ?></th>
                                            <th><?php echo __('created_at'); ?></th>
                                            <th><?php echo __('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentShipments as $shipment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($shipment['tracking_number']); ?></td>
                                                <td><?php echo htmlspecialchars($shipment['sender_name']); ?></td>
                                                <td><?php echo htmlspecialchars($shipment['receiver_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusBadgeClass($shipment['status']); ?>">
                                                        <?php echo __('status_' . $shipment['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($shipment['created_at'])); ?></td>
                                                <td>
                                                    <a href="index.php?page=shipments&action=view&id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php echo __('no_recent_shipments'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
/**
 * Helper function to get the appropriate badge class for a status
 * 
 * @param string $status The status
 * @return string The badge class
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'received':
            return 'info';
        case 'processing':
            return 'primary';
        case 'in_transit':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'local_delivery':
            return 'info';
        default:
            return 'secondary';
    }
}
?>

