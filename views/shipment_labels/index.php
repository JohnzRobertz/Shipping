<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">พิมพ์ฉลากพัสดุ</h1>
        <div>
            <a href="index.php?page=shipments" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>กลับไปหน้ารายการพัสดุ
            </a>
        </div>
    </div>
    
    <?php include 'views/layout/alerts.php'; ?>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">รายการพัสดุที่รอพิมพ์ฉลาก</h5>
        </div>
        <div class="card-body">
            <?php if (empty($shipments)): ?>
                <div class="alert alert-info">
                    ไม่พบพัสดุที่รอพิมพ์ฉลาก
                </div>
            <?php else: ?>
                <form action="index.php?page=shipment_labels&action=printMultiple" method="post">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th>หมายเลขพัสดุ</th>
                                    <th>ผู้ส่ง</th>
                                    <th>ผู้รับ</th>
                                    <th>น้ำหนัก</th>
                                    <th>ขนาด</th>
                                    <th>วันที่รับเข้า</th>
                                    <th width="120">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipments as $shipment): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input shipment-checkbox" type="checkbox" name="shipment_ids[]" value="<?php echo $shipment['id']; ?>">
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($shipment['tracking_number']); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['sender_name']); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['receiver_name']); ?></td>
                                    <td><?php echo htmlspecialchars($shipment['weight']); ?> kg</td>
                                    <td><?php echo htmlspecialchars($shipment['length']); ?> x <?php echo htmlspecialchars($shipment['width']); ?> x <?php echo htmlspecialchars($shipment['height']); ?> cm</td>
                                    <td><?php echo date('d/m/Y', strtotime($shipment['created_at'])); ?></td>
                                    <td>
                                        <a href="index.php?page=shipment_labels&action=printLabel&id=<?php echo $shipment['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-print"></i> พิมพ์
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success" id="printSelectedBtn" disabled>
                            <i class="fas fa-print me-2"></i>พิมพ์ฉลากที่เลือก
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="selectAllBtn">
                            <i class="fas fa-check-square me-2"></i>เลือกทั้งหมด
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="deselectAllBtn">
                            <i class="fas fa-square me-2"></i>ยกเลิกการเลือกทั้งหมด
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All checkbox functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const shipmentCheckboxes = document.querySelectorAll('.shipment-checkbox');
    const printSelectedBtn = document.getElementById('printSelectedBtn');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    
    // Function to update print button state
    function updatePrintButtonState() {
        const checkedBoxes = document.querySelectorAll('.shipment-checkbox:checked');
        printSelectedBtn.disabled = checkedBoxes.length === 0;
    }
    
    // Select all checkbox change event
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            shipmentCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updatePrintButtonState();
        });
    }
    
    // Individual checkboxes change event
    shipmentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updatePrintButtonState();
            
            // Update select all checkbox
            const allChecked = document.querySelectorAll('.shipment-checkbox:checked').length === shipmentCheckboxes.length;
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
            }
        });
    });
    
    // Select All button click event
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            shipmentCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = true;
            }
            updatePrintButtonState();
        });
    }
    
    // Deselect All button click event
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            shipmentCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            updatePrintButtonState();
        });
    }
    
    // Initial button state
    updatePrintButtonState();
});
</script>

<?php include 'views/layout/footer.php'; ?>

