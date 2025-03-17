<?php
// โหลดไฟล์ภาษา
$lang = include 'languages/' . $_SESSION['language'] . '.php';
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['language'] ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['update_status'] ?> - <?= $lang['site_title'] ?></title>
    <link rel="stylesheet" href="<?= site_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= site_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'views/layout/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h2><?= $lang['update_status'] ?></h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error'] ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <h5><?= $lang['shipment_details'] ?></h5>
                            <p><strong><?= $lang['tracking_number'] ?>:</strong> <?= $shipment['tracking_number'] ?></p>
                            <p><strong><?= $lang['current_status'] ?>:</strong> 
                                <span class="badge bg-<?= getStatusColor($shipment['status']) ?>">
                                    <?= $lang['status_' . $shipment['status']] ?>
                                </span>
                            </p>
                        </div>
                        
                        <form action="<?= site_url('admin/shipments/update-status/' . $shipment['id']) ?>" method="POST">
                            <!-- CSRF token -->
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="form-group mb-3">
                                <label for="status"><?= $lang['status'] ?></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value=""><?= $lang['select_status'] ?></option>
                                    <option value="received" <?= $shipment['status'] == 'received' ? 'selected' : '' ?>><?= $lang['status_received'] ?></option>
                                    <option value="processing" <?= $shipment['status'] == 'processing' ? 'selected' : '' ?>><?= $lang['status_processing'] ?></option>
                                    <option value="in_transit" <?= $shipment['status'] == 'in_transit' ? 'selected' : '' ?>><?= $lang['status_in_transit'] ?></option>
                                    <option value="arrived_destination" <?= $shipment['status'] == 'arrived_destination' ? 'selected' : '' ?>><?= $lang['status_arrived_destination'] ?></option>
                                    <option value="local_delivery" <?= $shipment['status'] == 'local_delivery' ? 'selected' : '' ?>><?= $lang['status_local_delivery'] ?></option>
                                    <option value="delivered" <?= $shipment['status'] == 'delivered' ? 'selected' : '' ?>><?= $lang['status_delivered'] ?></option>
                                    <option value="cancelled" <?= $shipment['status'] == 'cancelled' ? 'selected' : '' ?>><?= $lang['status_cancelled'] ?></option>
                                    <option value="out_for_delivery" <?= $shipment['status'] == 'out_for_delivery' ? 'selected' : '' ?>><?= $lang['status_out_for_delivery'] ?></option>
                                </select>
                            </div>
                            
                            <!-- ส่วนที่จะแสดงเมื่อเลือกสถานะ "ขนส่งในประเทศ" -->
                            <div id="domestic-delivery-fields" style="display: none;">
                                <div class="form-group mb-3">
                                    <label for="domestic_carrier"><?= $lang['domestic_carrier'] ?></label>
                                    <select name="domestic_carrier" id="domestic_carrier" class="form-control">
                                        <option value=""><?= $lang['select'] ?></option>
                                        <option value="thailand_post">ไปรษณีย์ไทย (Thailand Post)</option>
                                        <option value="kerry">Kerry Express</option>
                                        <option value="flash">Flash Express</option>
                                        <option value="j&t">J&T Express</option>
                                        <option value="dhl">DHL</option>
                                        <option value="other"><?= $lang['other'] ?></option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="domestic_tracking_number"><?= $lang['domestic_tracking_number'] ?></label>
                                    <input type="text" name="domestic_tracking_number" id="domestic_tracking_number" class="form-control" value="<?= $shipment['domestic_tracking_number'] ?? '' ?>">
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="location"><?= $lang['location'] ?></label>
                                <input type="text" name="location" id="location" class="form-control" value="ประเทศไทย">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="description"><?= $lang['description'] ?></label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary"><?= $lang['save'] ?></button>
                                <a href="<?= site_url('admin/shipments/view/' . $shipment['id']) ?>" class="btn btn-secondary"><?= $lang['cancel'] ?></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- เพิ่มส่วนข้อมูลการขนส่งภายในประเทศในหน้าอัพเดทสถานะ -->
<div class="row mb-4" id="domestic-shipping-section" style="display: <?php echo ($shipment['status'] == 'out_for_delivery' || $shipment['status'] == 'delivered') ? 'block' : 'none'; ?>;">
    <div class="col-md-12">
        <h4 class="card-title mb-3"><?php echo __('domestic_shipping_information'); ?></h4>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="domestic_carrier" class="form-label"><?php echo __('domestic_carrier'); ?></label>
                            <input type="text" name="domestic_carrier" id="domestic_carrier" class="form-control" value="<?php echo htmlspecialchars($shipment['domestic_carrier'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="domestic_tracking_number" class="form-label"><?php echo __('domestic_tracking_number'); ?></label>
                            <input type="text" name="domestic_tracking_number" id="domestic_tracking_number" class="form-control" value="<?php echo htmlspecialchars($shipment['domestic_tracking_number'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="handover_date" class="form-label"><?php echo __('handover_date'); ?></label>
                            <input type="date" name="handover_date" id="handover_date" class="form-control" value="<?php echo htmlspecialchars($shipment['handover_date'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <?php include 'views/layout/footer.php'; ?>
    
    <script src="<?= site_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const domesticFields = document.getElementById('domestic-delivery-fields');
        
        // แสดง/ซ่อนฟิลด์ตามสถานะที่เลือก
        function toggleDomesticFields() {
            if (statusSelect.value === 'local_delivery') {
                domesticFields.style.display = 'block';
                document.getElementById('domestic_carrier').setAttribute('required', 'required');
                document.getElementById('domestic_tracking_number').setAttribute('required', 'required');
            } else {
                domesticFields.style.display = 'none';
                document.getElementById('domestic_carrier').removeAttribute('required');
                document.getElementById('domestic_tracking_number').removeAttribute('required');
            }
        }
        
        statusSelect.addEventListener('change', toggleDomesticFields);
        
        // ตรวจสอบค่าเริ่มต้น
        toggleDomesticFields();
    });
    </script>

<script>
// เพิ่ม JavaScript เพื่อแสดง/ซ่อนส่วนข้อมูลการขนส่งภายในประเทศตามสถานะ
document.getElementById('status').addEventListener('change', function() {
    const domesticSection = document.getElementById('domestic-shipping-section');
    if (this.value === 'out_for_delivery' || this.value === 'delivered') {
        domesticSection.style.display = 'block';
    } else {
        domesticSection.style.display = 'none';
    }
});
</script>
</body>
</html>

