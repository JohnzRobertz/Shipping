<?php include 'views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil me-2"></i> <?php echo __('edit_shipment'); ?>: <?php echo htmlspecialchars($shipment['tracking_number']); ?></h2>
    <a href="index.php?page=shipments&action=view&id=<?php echo $shipment['id']; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i> <?php echo __('back'); ?>
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="index.php?page=shipments&action=update" method="post" id="shipment-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $shipment['id']; ?>">
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="card-title mb-3"><?php echo __('lot_information'); ?></h4>
                    <div class="mb-3">
                        <label for="lot_id" class="form-label"><?php echo __('select_lot'); ?> <span class="text-danger">*</span></label>
                        <select name="lot_id" id="lot_id" class="form-select" required>
                            <?php foreach ($lots as $lot): ?>
                                <option value="<?php echo $lot['id']; ?>" <?php echo $shipment['lot_id'] == $lot['id'] ? 'selected' : ''; ?>>
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
            </div>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="card-title mb-3"><?php echo __('sender_receiver_information'); ?></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_code" class="form-label">รหัสลูกค้า</label>
                                <input type="text" name="customer_code" id="customer_code" class="form-control" value="<?php echo htmlspecialchars($shipment['customer_code'] ?? ''); ?>">
                                <div class="form-text">รหัสลูกค้าสำหรับการอ้างอิงและการค้นหา</div>
                            </div>
                            <div class="mb-3">
                                <label for="sender_name" class="form-label"><?php echo __('sender_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="sender_name" id="sender_name" class="form-control" value="<?php echo htmlspecialchars($shipment['sender_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="sender_contact" class="form-label"><?php echo __('sender_contact'); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="sender_contact" id="sender_contact" class="form-control" value="<?php echo htmlspecialchars($shipment['sender_contact']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="sender_phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" name="sender_phone" id="sender_phone" class="form-control" value="<?php echo htmlspecialchars($shipment['sender_phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="receiver_name" class="form-label"><?php echo __('receiver_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_name" id="receiver_name" class="form-control" value="<?php echo htmlspecialchars($shipment['receiver_name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="receiver_contact" class="form-label"><?php echo __('receiver_contact'); ?> <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_contact" id="receiver_contact" class="form-control" value="<?php echo htmlspecialchars($shipment['receiver_contact']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="receiver_phone" class="form-label">เบอร์โทรผู้รับ</label>
                                <input type="text" name="receiver_phone" id="receiver_phone" class="form-control" value="<?php echo htmlspecialchars($shipment['receiver_phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="card-title mb-3"><?php echo __('package_information'); ?></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="weight" class="form-label"><?php echo __('weight'); ?> (kg) <span class="text-danger">*</span></label>
                                <input type="number" name="weight" id="weight" class="form-control" step="0.01" min="0.01" value="<?php echo htmlspecialchars($shipment['weight']); ?>" required onchange="updateCalculations()">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label"><?php echo __('description'); ?></label>
                                <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($shipment['description']); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="dimensional-calculator">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo __('dimensions'); ?> (cm) <span class="text-danger">*</span></label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text"><?php echo __('length'); ?></span>
                                        <input type="number" name="length" id="length" class="form-control" step="0.1" min="0.1" value="<?php echo htmlspecialchars($shipment['length']); ?>" required onchange="updateCalculations()">
                                        <span class="input-group-text">×</span>
                                        <span class="input-group-text"><?php echo __('width'); ?></span>
                                        <input type="number" name="width" id="width" class="form-control" step="0.1" min="0.1" value="<?php echo htmlspecialchars($shipment['width']); ?>" required onchange="updateCalculations()">
                                        <span class="input-group-text">×</span>
                                        <span class="input-group-text"><?php echo __('height'); ?></span>
                                        <input type="number" name="height" id="height" class="form-control" step="0.1" min="0.1" value="<?php echo htmlspecialchars($shipment['height']); ?>" required onchange="updateCalculations()">
                                    </div>
                                </div>
                                
                                <!-- เพิ่มส่วนคำนวณราคา -->
                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <p class="mb-1"><?php echo __('volumetric_weight'); ?>:</p>
                                                <h5 id="volumetric-weight"><?php echo htmlspecialchars($shipment['volumetric_weight']); ?> kg</h5>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><?php echo __('chargeable_weight'); ?>:</p>
                                                <h5 id="chargeable-weight"><?php echo htmlspecialchars($shipment['chargeable_weight']); ?> kg</h5>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="price" class="form-label">ราคาต่อกิโลกรัม (บาท)</label>
                                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($shipment['price'] ?? '0.00'); ?>" onchange="updateCalculations()">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">ราคารวม (บาท)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control bg-light" id="total_price" name="total_price" value="<?php echo htmlspecialchars($shipment['total_price'] ?? '0.00'); ?>" readonly>
                                                    <span class="input-group-text">บาท</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- เพิ่มส่วนข้อมูลการขนส่งภายในประเทศหลังจากส่วนข้อมูลพัสดุ -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="card-title mb-3"><?php echo __('domestic_shipping_information'); ?></h4>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="domestic_carrier" class="form-label"><?php echo __('domestic_carrier'); ?></label>
                                    <select class="form-select" id="domestic_carrier" name="domestic_carrier">
                                        <option value=""><?php echo __('select_carrier'); ?></option>
                                        <option value="thailand_post" <?php echo $shipment['domestic_carrier'] == 'thailand_post' ? 'selected' : ''; ?>><?php echo __('thailand_post'); ?></option>
                                        <option value="kerry" <?php echo $shipment['domestic_carrier'] == 'kerry' ? 'selected' : ''; ?>><?php echo __('kerry_express'); ?></option>
                                        <option value="flash" <?php echo $shipment['domestic_carrier'] == 'flash' ? 'selected' : ''; ?>><?php echo __('flash_express'); ?></option>
                                        <option value="j&t" <?php echo $shipment['domestic_carrier'] == 'j&t' ? 'selected' : ''; ?>><?php echo __('jt_express'); ?></option>
                                        <option value="dhl" <?php echo $shipment['domestic_carrier'] == 'dhl' ? 'selected' : ''; ?>><?php echo __('dhl'); ?></option>
                                        <option value="lalamove" <?php echo $shipment['domestic_carrier'] == 'lalamove' ? 'selected' : ''; ?>><?php echo __('lalamove'); ?></option>
                                        <option value="other" <?php echo $shipment['domestic_carrier'] == 'other' ? 'selected' : ''; ?>><?php echo __('other'); ?></option>
                                    </select>
                                    <div class="form-text"><?php echo __('domestic_carrier_help'); ?></div>
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
            
            <div class="d-flex justify-content-end">
                <a href="index.php?page=shipments&action=view&id=<?php echo $shipment['id']; ?>" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-2"></i> <?php echo __('cancel'); ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i> <?php echo __('save'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCalculations() {
    const length = parseFloat(document.getElementById('length').value) || 0;
    const width = parseFloat(document.getElementById('width').value) || 0;
    const height = parseFloat(document.getElementById('height').value) || 0;
    const weight = parseFloat(document.getElementById('weight').value) || 0;
    const pricePerKg = parseFloat(document.getElementById('price').value) || 0;
    
    let divisor = 6000; // Default
    
    const dimensionalWeight = (length * width * height) / divisor;
    const chargeableWeight = Math.max(dimensionalWeight, weight);
    const totalPrice = chargeableWeight * pricePerKg;
    
    document.getElementById('volumetric-weight').textContent = dimensionalWeight.toFixed(2) + ' kg';
    document.getElementById('chargeable-weight').textContent = chargeableWeight.toFixed(2) + ' kg';
    document.getElementById('total_price').value = totalPrice.toFixed(2);
}

// Initialize on page load
updateCalculations();
</script>

<?php include 'views/layout/footer.php'; ?>

