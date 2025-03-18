<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-plus-circle me-2"></i> <?php echo getTranslation('create_shipment'); ?>
        </h2>
        <a href="index.php?page=shipments" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> <?php echo getTranslation('back'); ?>
        </a>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- แก้ไขฟอร์มให้ส่งข้อมูลที่ถูกต้อง -->
    <form action="index.php?page=shipments&action=store" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <!-- Sender and Receiver Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <!-- เพิ่มฟิลด์รหัสลูกค้าและเบอร์โทรในส่วนข้อมูลผู้ส่ง -->
                <div class="card h-100">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h5 class="card-title mb-0"><?php echo __('sender_information'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="customer_code" class="form-label"><?php echo __('customer_code'); ?></label>
                            <input type="text" class="form-control" id="customer_code" name="customer_code">
                            <div class="form-text"><?php echo __('customer_code_help'); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="sender_name" class="form-label"><?php echo __('sender_name'); ?></label>
                            <input type="text" class="form-control" id="sender_name" name="sender_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="sender_contact" class="form-label"><?php echo __('sender_contact'); ?></label>
                            <input type="text" class="form-control" id="sender_contact" name="sender_contact" required>
                        </div>
                        <div class="mb-3">
                            <label for="sender_phone" class="form-label"><?php echo __('sender_phone'); ?></label>
                            <input type="text" class="form-control" id="sender_phone" name="sender_phone">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h5 class="card-title mb-0"><?php echo __('receiver_information'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="receiver_name" class="form-label"><?php echo __('receiver_name'); ?></label>
                            <input type="text" class="form-control" id="receiver_name" name="receiver_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="receiver_contact" class="form-label"><?php echo __('receiver_contact'); ?></label>
                            <input type="text" class="form-control" id="receiver_contact" name="receiver_contact" required>
                        </div>
                        <div class="mb-3">
                            <label for="receiver_phone" class="form-label"><?php echo __('receiver_phone'); ?></label>
                            <input type="text" class="form-control" id="receiver_phone" name="receiver_phone">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Details -->
        <div class="card mb-4">
            <div class="card-header bg-primary bg-opacity-10">
                <h5 class="card-title mb-0"><?php echo __('package_information'); ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="weight" class="form-label"><?php echo __('weight'); ?> (<?php echo __('kg'); ?>)</label>
                        <input type="number" class="form-control" id="weight" name="weight" step="0.01" min="0.01" required onchange="updateCalculations()">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="form-label"><?php echo __('dimensions'); ?> (<?php echo __('cm'); ?>)</label>
                    <div class="col-md-4">
                        <input type="number" class="form-control" id="length" name="length" placeholder="<?php echo __('length'); ?>" value="10" step="0.1" min="0.1" required onchange="updateCalculations()">
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" id="width" name="width" placeholder="<?php echo __('width'); ?>" value="10" step="0.1" min="0.1" required onchange="updateCalculations()">
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" id="height" name="height" placeholder="<?php echo __('height'); ?>" value="10" step="0.1" min="0.1" required onchange="updateCalculations()">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label"><?php echo __('description'); ?></label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <!-- เพิ่มส่วนคำนวณราคา -->
                <div class="card bg-light mb-3">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h5 class="card-title mb-0"><?php echo __('weight_calculation'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><?php echo __('volumetric_weight'); ?>:</p>
                                <h5 id="volumetric_weight">0.00 <?php echo __('kg'); ?></h5>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><?php echo __('chargeable_weight'); ?>:</p>
                                <h5 id="chargeable_weight">0.00 <?php echo __('kg'); ?></h5>
                            </div>
                        </div>
                        
                        <!-- แก้ไขส่วนของฟอร์มราคา -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="price" class="form-label"><?php echo __('price_per_kg'); ?></label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="0.00" onchange="updateCalculations()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?php echo __('total_price'); ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light" id="total_price" name="total_price" readonly>
                                    <span class="input-group-text"><?php echo __('currency'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lot Assignment -->
        <div class="card mb-4">
            <div class="card-header bg-primary bg-opacity-10">
                <h5 class="card-title mb-0"><?php echo __('lot_assignment'); ?></h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="lot_number" class="form-label"><?php echo __('lot_number'); ?></label>
                    <select class="form-select" id="lot_number" name="lot_number">
                        <option value="">-- <?php echo __('select'); ?> --</option>
                        <?php foreach ($lots as $lot): ?>
                            <option value="<?php echo htmlspecialchars($lot['lot_number']); ?>">
                                <?php echo htmlspecialchars($lot['lot_number']); ?> - <?php echo htmlspecialchars($lot['origin']); ?> → <?php echo htmlspecialchars($lot['destination']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text"><?php echo __('lot_help_text'); ?></div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save me-2"></i> <?php echo __('create_shipment'); ?>
            </button>
        </div>
    </form>
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

    document.getElementById('volumetric_weight').textContent = dimensionalWeight.toFixed(2) + ' ' + '<?php echo __('kg'); ?>';
    document.getElementById('chargeable_weight').textContent = chargeableWeight.toFixed(2) + ' ' + '<?php echo __('kg'); ?>';
    document.getElementById('total_price').value = totalPrice.toFixed(2);
}

// Initialize on page load
updateCalculations();
</script>

<?php include 'views/layout/footer.php'; ?>