<?php include 'views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-boxes me-2"></i> <?php echo __('assign_to_lot'); ?>: <?php echo htmlspecialchars($shipment['tracking_number']); ?></h2>
    <a href="index.php?page=shipments&action=view&id=<?php echo $shipment['id']; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i> <?php echo __('back'); ?>
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="index.php?page=shipments&action=saveLotAssignment" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $shipment['id']; ?>">
            
            <div class="mb-4">
                <h5 class="card-title mb-3"><?php echo __('shipment_information'); ?></h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><?php echo __('tracking_number'); ?>:</strong> <?php echo htmlspecialchars($shipment['tracking_number']); ?></p>
                        <p><strong><?php echo __('sender'); ?>:</strong> <?php echo htmlspecialchars($shipment['sender_name']); ?></p>
                        <p><strong><?php echo __('receiver'); ?>:</strong> <?php echo htmlspecialchars($shipment['receiver_name']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><?php echo __('weight'); ?>:</strong> <?php echo htmlspecialchars($shipment['weight']); ?> kg</p>
                        <p><strong><?php echo __('dimensions'); ?>:</strong> 
                            <?php echo htmlspecialchars($shipment['length']); ?> × 
                            <?php echo htmlspecialchars($shipment['width']); ?> × 
                            <?php echo htmlspecialchars($shipment['height']); ?> cm
                        </p>
                        <p><strong><?php echo __('status'); ?>:</strong> 
                            <span class="badge bg-secondary"><?php echo __($shipment['status']); ?></span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <h5 class="card-title mb-3"><?php echo __('select_lot'); ?></h5>
                <div class="mb-3">
                    <label for="lot_id" class="form-label"><?php echo __('lot'); ?> <span class="text-danger">*</span></label>
                    <select name="lot_id" id="lot_id" class="form-select" required>
                        <option value=""><?php echo __('select_lot'); ?></option>
                        <?php foreach ($lots as $lot): ?>
                            <option value="<?php echo $lot['id']; ?>">
                                <?php 
                                $typeIcon = '';
                                switch ($lot['lot_type']) {
                                    case 'sea':
                                        $typeIcon = '🚢 ';
                                        break;
                                    case 'air':
                                        $typeIcon = '✈️ ';
                                        break;
                                    case 'land':
                                        $typeIcon = '🚚 ';
                                        break;
                                }
                                echo $typeIcon . htmlspecialchars($lot['lot_number']) . ' - ' . 
                                     htmlspecialchars($lot['origin']) . ' → ' . 
                                     htmlspecialchars($lot['destination']) . ' (' . 
                                     date('d M Y', strtotime($lot['departure_date'])) . ')';
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="index.php?page=shipments&action=view&id=<?php echo $shipment['id']; ?>" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-2"></i> <?php echo __('cancel'); ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i> <?php echo __('assign'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

