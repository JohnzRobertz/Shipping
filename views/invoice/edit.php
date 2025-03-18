<?php
// ตรวจสอบว่ามีการเรียกไฟล์นี้โดยตรงหรือไม่
if (!defined('BASE_PATH')) {
    exit('No direct script access allowed');
}

// ตรวจสอบว่ามีข้อมูล invoice หรือไม่
if (!isset($invoice) || !$invoice) {
    echo '<div class="alert alert-danger">ไม่พบข้อมูลใบแจ้งหนี้</div>';
    return;
}

// Debug: แสดงข้อมูล invoice
error_log("Invoice data in edit.php: " . print_r($invoice, true));
?>
<?php include 'views/layout/header.php'; ?>
<div class="container-fluid px-4">
    <h1 class="mt-4"><?= __('edit_invoice') ?></h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            <?= __('edit_invoice_details') ?>
        </div>
        <div class="card-body">
            <form id="editInvoiceForm" action="index.php?page=invoice&action=update" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($invoice['id']) ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_number"><?= __('invoice_number') ?>:</label>
                            <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="<?= htmlspecialchars($invoice['invoice_number']) ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id"><?= __('customer') ?>:</label>
                            <select class="form-control" id="customer_id" name="customer_id" required>
                                <option value=""><?= __('select_customer') ?></option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= htmlspecialchars($customer['id']) ?>" <?= $customer['id'] == $invoice['customer_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($customer['code']) ?> - <?= htmlspecialchars($customer['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_date"><?= __('invoice_date') ?>:</label>
                            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?= htmlspecialchars($invoice['invoice_date']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="due_date"><?= __('due_date') ?>:</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= htmlspecialchars($invoice['due_date']) ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="notes"><?= __('notes') ?>:</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($invoice['notes']) ?></textarea>
                        </div>
                    </div>
                </div>
                
                <h4 class="mt-4"><?= __('shipments') ?></h4>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;"><input type="checkbox" id="select-all-shipments"></th>
                                <th><?= __('tracking_number') ?></th>
                                <th><?= __('origin') ?> - <?= __('destination') ?></th>
                                <th><?= __('weight') ?></th>
                                <th><?= __('price') ?></th>
                                <th><?= __('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // แสดงรายการ shipments ที่อยู่ในใบแจ้งหนี้
                            foreach ($invoiceShipments as $shipment): 
                                $isSelected = true; // shipment นี้ถูกเลือกอยู่แล้ว
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="shipment_ids[]" value="<?= htmlspecialchars($shipment['id']) ?>" <?= $isSelected ? 'checked' : '' ?>>
                                    </td>
                                    <td><?= htmlspecialchars($shipment['tracking_number']) ?></td>
                                    <td><?= htmlspecialchars($shipment['origin']) ?> - <?= htmlspecialchars($shipment['destination']) ?></td>
                                    <td><?= htmlspecialchars($shipment['weight']) ?> kg</td>
                                    <td><?= number_format($shipment['total_price'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $shipment['status'] == 'delivered' ? 'success' : 'warning' ?>">
                                            <?= __($shipment['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php 
                            // แสดงรายการ shipments ที่ยังไม่ได้ออกใบแจ้งหนี้
                            foreach ($unpaidShipments as $shipment): 
                                $isSelected = in_array($shipment['id'], $shipmentIds);
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="shipment_ids[]" value="<?= htmlspecialchars($shipment['id']) ?>" <?= $isSelected ? 'checked' : '' ?>>
                                    </td>
                                    <td><?= htmlspecialchars($shipment['tracking_number']) ?></td>
                                    <td><?= htmlspecialchars($shipment['origin']) ?> - <?= htmlspecialchars($shipment['destination']) ?></td>
                                    <td><?= htmlspecialchars($shipment['weight']) ?> kg</td>
                                    <td><?= number_format($shipment['total_price'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $shipment['status'] == 'delivered' ? 'success' : 'warning' ?>">
                                            <?= __($shipment['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <h4 class="mt-4"><?= __('additional_charges') ?></h4>
                <div id="additional-charges-container">
                    <?php if (!empty($additionalCharges)): ?>
                        <?php foreach ($additionalCharges as $index => $charge): ?>
                            <div class="row mb-2 charge-row">
                                <div class="col-md-3">
                                    <select class="form-control" name="charges[<?= $index ?>][charge_type]">
                                        <option value="fee" <?= $charge['charge_type'] == 'fee' ? 'selected' : '' ?>><?= __('fee') ?></option>
                                        <option value="discount" <?= $charge['charge_type'] == 'discount' ? 'selected' : '' ?>><?= __('discount') ?></option>
                                        <option value="tax" <?= $charge['charge_type'] == 'tax' ? 'selected' : '' ?>><?= __('tax') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="charges[<?= $index ?>][description]" placeholder="<?= __('description') ?>" value="<?= htmlspecialchars($charge['description']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" class="form-control charge-amount" name="charges[<?= $index ?>][amount]" placeholder="<?= __('amount') ?>" value="<?= htmlspecialchars($charge['amount']) ?>">
                                </div>
                                <div class="col-md-1">
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input is-percentage" name="charges[<?= $index ?>][is_percentage]" <?= $charge['is_percentage'] ? 'checked' : '' ?>>
                                        <label class="form-check-label">%</label>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-charge"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <button type="button" id="add-charge" class="btn btn-sm btn-secondary">
                        <i class="fas fa-plus"></i> <?= __('add_charge') ?>
                    </button>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6 offset-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-6 text-end"><?= __('subtotal') ?>:</div>
                                    <div class="col-6 text-end">
                                        <span id="subtotal"><?= number_format($invoice['subtotal'], 2) ?></span>
                                        <input type="hidden" name="subtotal" id="subtotal-input" value="<?= $invoice['subtotal'] ?>">
                                    </div>
                                </div>
                                <div id="charges-summary">
                                    <!-- Charges will be added here dynamically -->
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6 text-end"><strong><?= __('total') ?>:</strong></div>
                                    <div class="col-6 text-end">
                                        <strong id="total"><?= number_format($invoice['total_amount'], 2) ?></strong>
                                        <input type="hidden" name="total_amount" id="total-input" value="<?= $invoice['total_amount'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= __('save_changes') ?>
                    </button>
                    <a href="index.php?page=invoice&action=view&id=<?= htmlspecialchars($invoice['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> <?= __('cancel') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all shipments checkbox
    document.getElementById('select-all-shipments').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="shipment_ids[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateTotals();
    });
    
    // Add charge button
    document.getElementById('add-charge').addEventListener('click', function() {
        const container = document.getElementById('additional-charges-container');
        const index = document.querySelectorAll('.charge-row').length;
        
        const row = document.createElement('div');
        row.className = 'row mb-2 charge-row';
        row.innerHTML = `
            <div class="col-md-3">
                <select class="form-control" name="charges[${index}][charge_type]">
                    <option value="fee"><?= __('fee') ?></option>
                    <option value="discount"><?= __('discount') ?></option>
                    <option value="tax"><?= __('tax') ?></option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="charges[${index}][description]" placeholder="<?= __('description') ?>">
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" class="form-control charge-amount" name="charges[${index}][amount]" placeholder="<?= __('amount') ?>">
            </div>
            <div class="col-md-1">
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input is-percentage" name="charges[${index}][is_percentage]">
                    <label class="form-check-label">%</label>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm remove-charge"><i class="fas fa-times"></i></button>
            </div>
        `;
        
        container.appendChild(row);
        
        // Add event listeners to new elements
        row.querySelector('.remove-charge').addEventListener('click', function() {
            row.remove();
            updateTotals();
        });
        
        row.querySelector('.charge-amount').addEventListener('input', updateTotals);
        row.querySelector('.is-percentage').addEventListener('change', updateTotals);
        row.querySelector('select[name^="charges"]').addEventListener('change', updateTotals);
    });
    
    // Remove charge button
    document.querySelectorAll('.remove-charge').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.charge-row').remove();
            updateTotals();
        });
    });
    
    // Update totals when shipment selection changes
    document.querySelectorAll('input[name="shipment_ids[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotals);
    });
    
    // Update totals when charge amount changes
    document.querySelectorAll('.charge-amount').forEach(input => {
        input.addEventListener('input', updateTotals);
    });
    
    // Update totals when percentage checkbox changes
    document.querySelectorAll('.is-percentage').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotals);
    });
    
    // Update totals when charge type changes
    document.querySelectorAll('select[name^="charges"]').forEach(select => {
        select.addEventListener('change', updateTotals);
    });
    
    // Calculate totals
    function updateTotals() {
        // Calculate subtotal from selected shipments
        let subtotal = 0;
        document.querySelectorAll('input[name="shipment_ids[]"]:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const priceText = row.querySelector('td:nth-child(5)').textContent;
            const price = parseFloat(priceText.replace(/,/g, ''));
            subtotal += price;
        });
        
        // Update subtotal display
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('subtotal-input').value = subtotal.toFixed(2);
        
        // Calculate charges
        let total = subtotal;
        const chargesSummary = document.getElementById('charges-summary');
        chargesSummary.innerHTML = '';
        
        document.querySelectorAll('.charge-row').forEach(row => {
            const typeSelect = row.querySelector('select[name^="charges"]');
            const amountInput = row.querySelector('.charge-amount');
            const isPercentageCheckbox = row.querySelector('.is-percentage');
            const descriptionInput = row.querySelector('input[name$="[description]"]');
            
            if (amountInput.value) {
                const chargeType = typeSelect.value;
                const amount = parseFloat(amountInput.value);
                const isPercentage = isPercentageCheckbox.checked;
                const description = descriptionInput.value || (chargeType === 'fee' ? '<?= __('fee') ?>' : 
                                                             (chargeType === 'discount' ? '<?= __('discount') ?>' : '<?= __('tax') ?>'));
                
                let chargeAmount = isPercentage ? (subtotal * amount / 100) : amount;
                
                // Add to or subtract from total based on charge type
                if (chargeType === 'discount') {
                    total -= chargeAmount;
                    
                    // Add to summary
                    const summaryRow = document.createElement('div');
                    summaryRow.className = 'row mb-2';
                    summaryRow.innerHTML = `
                        <div class="col-6 text-end">${description} (${isPercentage ? amount + '%' : ''}):
                        </div>
                        <div class="col-6 text-end text-danger">-${chargeAmount.toFixed(2)}</div>
                    `;
                    chargesSummary.appendChild(summaryRow);
                } else {
                    total += chargeAmount;
                    
                    // Add to summary
                    const summaryRow = document.createElement('div');
                    summaryRow.className = 'row mb-2';
                    summaryRow.innerHTML = `
                        <div class="col-6 text-end">${description} (${isPercentage ? amount + '%' : ''}):
                        </div>
                        <div class="col-6 text-end">${chargeAmount.toFixed(2)}</div>
                    `;
                    chargesSummary.appendChild(summaryRow);
                }
            }
        });
        
        // Update total display
        document.getElementById('total').textContent = total.toFixed(2);
        document.getElementById('total-input').value = total.toFixed(2);
    }
    
    // Initialize totals
    updateTotals();
});
</script>

<?php include 'views/layout/footer.php'; ?>