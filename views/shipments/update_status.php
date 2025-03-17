<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?php echo __('update_shipment_status'); ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo __('shipment_details'); ?></h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong><?php echo __('tracking_number'); ?>:</strong> <?php echo htmlspecialchars($shipment['tracking_number']); ?></p>
                                        <p><strong><?php echo __('sender'); ?>:</strong> <?php echo htmlspecialchars($shipment['sender_name']); ?></p>
                                        <p><strong><?php echo __('receiver'); ?>:</strong> <?php echo htmlspecialchars($shipment['receiver_name']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong><?php echo __('current_status'); ?>:</strong> 
                                            <span class="badge bg-<?php echo getStatusColor($shipment['status']); ?>">
                                                <?php echo __('status_' . $shipment['status']); ?>
                                            </span>
                                        </p>
                                        <p><strong><?php echo __('created_at'); ?>:</strong> <?php echo date('d/m/Y H:i', strtotime($shipment['created_at'])); ?></p>
                                        <?php if (!empty($shipment['lot_number'])): ?>
                                            <p><strong><?php echo __('lot'); ?>:</strong> <?php echo htmlspecialchars($shipment['lot_number']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form action="index.php?controller=shipments&action=updateStatus&id=<?php echo $shipment['id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label"><?php echo __('status'); ?> <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value=""><?php echo __('select_status'); ?></option>
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo $shipment['status'] == $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label"><?php echo __('location'); ?></label>
                            <input type="text" name="location" id="location" class="form-control" value="<?php echo htmlspecialchars($shipment['location'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label"><?php echo __('description'); ?></label>
                            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <!-- ส่วนข้อมูลการขนส่งภายในประเทศ -->
                        <div id="domestic-shipping-section" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><?php echo __('domestic_shipping_information'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="domestic_carrier" class="form-label"><?php echo __('domestic_carrier'); ?></label>
                                                <select name="domestic_carrier" id="domestic_carrier" class="form-select">
                                                    <option value=""><?php echo __('select_carrier'); ?></option>
                                                    <option value="Thailand Post" <?php echo ($shipment['domestic_carrier'] ?? '') == 'Thailand Post' ? 'selected' : ''; ?>>ไปรษณีย์ไทย (Thailand Post)</option>
                                                    <option value="Kerry Express" <?php echo ($shipment['domestic_carrier'] ?? '') == 'Kerry Express' ? 'selected' : ''; ?>>Kerry Express</option>
                                                    <option value="Flash Express" <?php echo ($shipment['domestic_carrier'] ?? '') == 'Flash Express' ? 'selected' : ''; ?>>Flash Express</option>
                                                    <option value="J&T Express" <?php echo ($shipment['domestic_carrier'] ?? '') == 'J&T Express' ? 'selected' : ''; ?>>J&T Express</option>
                                                    <option value="DHL" <?php echo ($shipment['domestic_carrier'] ?? '') == 'DHL' ? 'selected' : ''; ?>>DHL</option>
                                                    <option value="Ninja Van" <?php echo ($shipment['domestic_carrier'] ?? '') == 'Ninja Van' ? 'selected' : ''; ?>>Ninja Van</option>
                                                    <option value="Other" <?php echo ($shipment['domestic_carrier'] ?? '') == 'Other' ? 'selected' : ''; ?>><?php echo __('other'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="domestic_tracking_number" class="form-label"><?php echo __('domestic_tracking_number'); ?></label>
                                                <input type="text" name="domestic_tracking_number" id="domestic_tracking_number" class="form-control" value="<?php echo htmlspecialchars($shipment['domestic_tracking_number'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="handover_date" class="form-label"><?php echo __('handover_date'); ?></label>
                                                <input type="date" name="handover_date" id="handover_date" class="form-control" value="<?php echo !empty($shipment['handover_date']) ? date('Y-m-d', strtotime($shipment['handover_date'])) : date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php?controller=shipments&action=view&id=<?php echo $shipment['id']; ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> <?php echo __('cancel'); ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> <?php echo __('update_status'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- ประวัติการติดตาม -->
            <?php if (!empty($trackingHistory)): ?>
            <div class="card shadow mt-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?php echo __('tracking_history'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($trackingHistory as $history): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-<?php echo getStatusColor($history['status']); ?>"></div>
                            <div class="timeline-content">
                                <div class="timeline-heading">
                                    <span class="badge bg-<?php echo getStatusColor($history['status']); ?> mb-2">
                                        <?php echo __('status_' . $history['status']); ?>
                                    </span>
                                    <small class="text-muted d-block"><?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?></small>
                                </div>
                                <div class="timeline-body">
                                    <?php if (!empty($history['location'])): ?>
                                    <p><strong><?php echo __('location'); ?>:</strong> <?php echo htmlspecialchars($history['location']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($history['description'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($history['description'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Timeline styling */
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}
.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: -23px;
    top: 15px;
    height: calc(100% - 15px);
    width: 2px;
    background-color: #dee2e6;
}
.timeline-content {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}
.timeline-heading {
    margin-bottom: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const domesticShippingSection = document.getElementById('domestic-shipping-section');
    
    // แสดง/ซ่อนส่วนข้อมูลการขนส่งภายในประเทศตามสถานะที่เลือก
    function toggleDomesticShippingSection() {
        const selectedStatus = statusSelect.value;
        if (selectedStatus === 'local_delivery' || selectedStatus === 'out_for_delivery') {
            domesticShippingSection.style.display = 'block';
        } else {
            domesticShippingSection.style.display = 'none';
        }
    }
    
    // เรียกใช้ฟังก์ชันเมื่อโหลดหน้าและเมื่อมีการเปลี่ยนสถานะ
    toggleDomesticShippingSection();
    statusSelect.addEventListener('change', toggleDomesticShippingSection);
});
</script>

<?php include 'views/layout/footer.php'; ?>

