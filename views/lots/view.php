<?php include 'views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-boxes me-2"></i> 
        <?php echo __('lot_details'); ?>: <?php echo htmlspecialchars($lot['lot_number']); ?>
    </h2>
    <div>
        <a href="index.php?page=lots&action=edit&id=<?php echo $lot['id']; ?>" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-2"></i> <?php echo __('edit'); ?>
        </a>
        <a href="index.php?page=lots" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> <?php echo __('back'); ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i> <?php echo __('lot_information'); ?>
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th><?php echo __('lot_number'); ?>:</th>
                        <td><?php echo htmlspecialchars($lot['lot_number']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('lot_type'); ?>:</th>
                        <td>
                            <?php 
                            $typeIcon = '';
                            switch ($lot['lot_type']) {
                                case 'sea':
                                    $typeIcon = '<i class="bi bi-water me-1"></i>';
                                    break;
                                case 'air':
                                    $typeIcon = '<i class="bi bi-airplane me-1"></i>';
                                    break;
                                case 'land':
                                    $typeIcon = '<i class="bi bi-truck me-1"></i>';
                                    break;
                            }
                            echo $typeIcon . __($lot['lot_type'] . '_freight');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo __('status'); ?>:</th>
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
                    </tr>
                    <tr>
                        <th><?php echo __('origin'); ?>:</th>
                        <td><?php echo htmlspecialchars($lot['origin']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('destination'); ?>:</th>
                        <td><?php echo htmlspecialchars($lot['destination']); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('departure_date'); ?>:</th>
                        <td><?php echo date('d M Y', strtotime($lot['departure_date'])); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('arrival_date'); ?>:</th>
                        <td><?php echo date('d M Y', strtotime($lot['arrival_date'])); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo __('created_at'); ?>:</th>
                        <td><?php echo date('d M Y, H:i', strtotime($lot['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-up-circle me-2"></i> <?php echo __('update_status'); ?>
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <form action="index.php?page=lots&action=updateStatus" method="post" id="status-update-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" value="<?php echo $lot['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label"><?php echo __('status'); ?> <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="received" <?php echo $lot['status'] === 'received' ? 'selected' : ''; ?>>
                                <?php echo __('received'); ?>
                            </option>
                            <option value="in_transit" <?php echo $lot['status'] === 'in_transit' ? 'selected' : ''; ?>>
                                <?php echo __('in_transit'); ?>
                            </option>
                            <option value="arrived_destination" <?php echo $lot['status'] === 'arrived_destination' ? 'selected' : ''; ?>>
                                <?php echo __('arrived_destination'); ?>
                            </option>
                            <option value="local_delivery" <?php echo $lot['status'] === 'local_delivery' ? 'selected' : ''; ?>>
                                <?php echo __('local_delivery'); ?>
                            </option>
                            <option value="delivered" <?php echo $lot['status'] === 'delivered' ? 'selected' : ''; ?>>
                                <?php echo __('delivered'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success" id="status-update-submit">
                            <i class="bi bi-check-circle me-2"></i> <?php echo __('update_status'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Shipments in this Lot -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-box-seam me-2"></i> <?php echo __('shipments_in_lot'); ?>
            </h5>
            <a href="index.php?page=shipments&action=create&lot_id=<?php echo $lot['id']; ?>" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle me-2"></i> <?php echo __('add_shipment'); ?>
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($shipments && count($shipments) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php echo __('tracking_number'); ?></th>
                            <th><?php echo __('sender'); ?></th>
                            <th><?php echo __('receiver'); ?></th>
                            <th><?php echo __('weight'); ?></th>
                            <th><?php echo __('status'); ?></th>
                            <th><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shipments as $shipment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($shipment['tracking_number']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['sender_name']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['receiver_name']); ?></td>
                                <td><?php echo htmlspecialchars($shipment['weight']); ?> kg</td>
                                <td>
                                    <?php 
                                    $statusBadge = '';
                                    switch ($shipment['status']) {
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
                                        <?php echo __($shipment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="index.php?page=shipments&action=view&id=<?php echo urlencode($shipment['id']); ?>" 
                                           class="btn btn-sm btn-info" title="<?php echo __('view'); ?>">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="index.php?page=shipments&action=edit&id=<?php echo urlencode($shipment['id']); ?>" 
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
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> <?php echo __('no_shipments_in_lot'); ?>
            </div>
            <div class="text-center">
                <a href="index.php?page=shipments&action=create&lot_id=<?php echo $lot['id']; ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i> <?php echo __('add_first_shipment'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

