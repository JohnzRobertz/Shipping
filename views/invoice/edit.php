<?php include 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= __('edit_invoice') ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard"><?= __('dashboard') ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=invoice"><?= __('invoices') ?></a></li>
        <li class="breadcrumb-item active"><?= __('edit_invoice') ?></li>
    </ol>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            <?= __('edit_invoice') ?> #<?= htmlspecialchars($invoice['invoice_number']) ?>
        </div>
        <div class="card-body">
            <form action="index.php?page=invoice&action=update" method="post" id="editInvoiceForm">
                <input type="hidden" name="id" value="<?= $invoice['id'] ?>">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label"><?= __('customer') ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="customerSearch" placeholder="<?= __('search_customer') ?>" autocomplete="off">
                                <select class="form-select d-none" id="customer_id" name="customer_id" required>
                                    <option value=""><?= __('select_customer') ?></option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>" <?= $customer['id'] == $invoice['customer_id'] ? 'selected' : '' ?> data-name="<?= htmlspecialchars($customer['name']) ?>" data-code="<?= htmlspecialchars($customer['code'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($customer['name']) ?> (<?= htmlspecialchars($customer['code'] ?? 'N/A') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="input-group-text" id="selectedCustomerDisplay">
                                    <?php 
                                    $selectedCustomer = '';
                                    foreach ($customers as $customer) {
                                        if ($customer['id'] == $invoice['customer_id']) {
                                            $selectedCustomer = htmlspecialchars($customer['name']) . ' (' . htmlspecialchars($customer['code'] ?? 'N/A') . ')';
                                            break;
                                        }
                                    }
                                    echo $selectedCustomer ?: __('select_customer');
                                    ?>
                                </span>
                            </div>
                            <div id="customerSearchResults" class="dropdown-menu w-100" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="invoice_date" class="form-label"><?= __('invoice_date') ?></label>
                            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?= date('Y-m-d', strtotime($invoice['invoice_date'])) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="due_date" class="form-label"><?= __('due_date') ?></label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime($invoice['due_date'])) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><?= __('select_shipments') ?> <span class="text-danger">*</span></label>
                    
                    <!-- เพิ่มช่องค้นหาและปุ่มเลือกทั้งหมด -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="customerCodeSearch" placeholder="<?= __('search_by_customer_code_or_tracking') ?>">
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllVisibleBtn">
                                <i class="fas fa-check-double"></i> <?= __('select_all_visible') ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">
                                <i class="fas fa-times"></i> <?= __('deselect_all') ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="shipmentsTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th><?= __('tracking_number') ?></th>
                                    <th><?= __('customer_code') ?></th>
                                    <th><?= __('customer') ?></th>
                                    <th><?= __('origin') ?></th>
                                    <th><?= __('destination') ?></th>
                                    <th><?= __('status') ?></th>
                                    <th><?= __('amount') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Combine current invoice shipments and unpaid shipments
                                $allShipments = array_merge($invoiceShipments, $unpaidShipments);
                                $uniqueShipments = [];
                                
                                // Remove duplicates
                                foreach ($allShipments as $shipment) {
                                    $uniqueShipments[$shipment['id']] = $shipment;
                                }
                                
                                foreach ($uniqueShipments as $shipment): 
                                    $isChecked = in_array($shipment['id'], $shipmentIds);
                                ?>
                                    <tr class="shipment-row">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input shipment-checkbox" type="checkbox" name="shipment_ids[]" value="<?= $shipment['id'] ?>" <?= $isChecked ? 'checked' : '' ?> data-customer-code="<?= htmlspecialchars($shipment['customer_code'] ?? 'N/A') ?>">
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($shipment['tracking_number']) ?></td>
                                        <td><?= htmlspecialchars($shipment['customer_code'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($shipment['customer_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($shipment['origin'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($shipment['destination'] ?? 'N/A') ?></td>
                                        <td><span class="badge bg-info"><?= __('status_' . $shipment['status']) ?></span></td>
                                        <td><?= number_format($shipment['total_price'], 2) ?> <?= __('currency') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-end"><?= __('total') ?>:</th>
                                    <th id="totalAmount"><?= number_format($invoice['total_amount'], 2) ?> <?= __('currency') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label"><?= __('notes') ?></label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($invoice['notes']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><?= __('additional_charges') ?></label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="chargesTable">
                            <thead>
                                <tr>
                                    <th><?= __('description') ?></th>
                                    <th><?= __('type') ?></th>
                                    <th><?= __('amount') ?></th>
                                    <th><?= __('is_percentage') ?></th>
                                    <th width="100"><?= __('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($additionalCharges)): ?>
                                    <?php foreach ($additionalCharges as $index => $charge): ?>
                                        <tr>
                                            <td>
                                                <input type="text" class="form-control" name="charges[<?= $index ?>][description]" value="<?= htmlspecialchars($charge['description']) ?>" required>
                                                <input type="hidden" name="charges[<?= $index ?>][id]" value="<?= $charge['id'] ?>">
                                            </td>
                                            <td>
                                                <select class="form-select" name="charges[<?= $index ?>][charge_type]">
                                                    <option value="fee" <?= $charge['charge_type'] == 'fee' ? 'selected' : '' ?>><?= __('fee') ?></option>
                                                    <option value="discount" <?= $charge['charge_type'] == 'discount' ? 'selected' : '' ?>><?= __('discount') ?></option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="charges[<?= $index ?>][amount]" value="<?= $charge['amount'] ?>" step="0.01" min="0" required>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="charges[<?= $index ?>][is_percentage]" value="1" <?= $charge['is_percentage'] == 1 ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-charge">
                                                    <i class="fas fa-trash"></i> <?= __('remove') ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5">
                                        <button type="button" class="btn btn-success btn-sm" id="addCharge">
                                            <i class="fas fa-plus"></i> <?= __('add_charge') ?>
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary"><?= __('update_invoice') ?></button>
                    <a href="index.php?page=invoice&action=view&id=<?= $invoice['id'] ?>" class="btn btn-secondary"><?= __('cancel') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ฟังก์ชันค้นหาลูกค้า
    const customerSearch = document.getElementById('customerSearch');
    const customerSelect = document.getElementById('customer_id');
    const customerSearchResults = document.getElementById('customerSearchResults');
    const selectedCustomerDisplay = document.getElementById('selectedCustomerDisplay');
    
    // แสดงผลลัพธ์การค้นหาเมื่อพิมพ์
    customerSearch.addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const options = customerSelect.options;
        let resultsHtml = '';
        
        for (let i = 1; i < options.length; i++) {
            const option = options[i];
            const customerName = option.getAttribute('data-name').toLowerCase();
            const customerCode = option.getAttribute('data-code').toLowerCase();
            
            if (customerName.includes(searchValue) || customerCode.includes(searchValue)) {
                resultsHtml += `<a class="dropdown-item" href="#" data-value="${option.value}" data-display="${option.text}">${option.text}</a>`;
            }
        }
        
        if (resultsHtml) {
            customerSearchResults.innerHTML = resultsHtml;
            customerSearchResults.classList.add('show');
            
            // เพิ่ม event listener สำหรับการคลิกที่ผลลัพธ์
            const resultItems = customerSearchResults.querySelectorAll('.dropdown-item');
            resultItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    customerSelect.value = this.getAttribute('data-value');
                    selectedCustomerDisplay.textContent = this.getAttribute('data-display');
                    customerSearch.value = '';
                    customerSearchResults.classList.remove('show');
                });
            });
        } else {
            customerSearchResults.innerHTML = `<span class="dropdown-item disabled"><?= __('no_results_found') ?></span>`;
            customerSearchResults.classList.add('show');
        }
    });
    
    // ซ่อนผลลัพธ์เมื่อคลิกที่อื่น
    document.addEventListener('click', function(e) {
        if (!customerSearch.contains(e.target) && !customerSearchResults.contains(e.target)) {
            customerSearchResults.classList.remove('show');
        }
    });
    
    // ฟังก์ชันคำนวณยอดรวม
    function updateTotalAmount() {
        let total = 0;
        document.querySelectorAll('.shipment-checkbox:checked').forEach(function(checkbox) {
            const row = checkbox.closest('tr');
            const amountCell = row.cells[row.cells.length - 1];
            const amountText = amountCell.textContent;
            const amount = parseFloat(amountText.replace(/[^0-9.-]+/g, ''));
            if (!isNaN(amount)) {
                total += amount;
            }
        });
        
        document.getElementById('totalAmount').textContent = total.toFixed(2) + ' <?= __('currency') ?>';
    }
    
    // ฟังก์ชันตรวจสอบสถานะ "Select All" checkbox
    function updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const visibleRows = document.querySelectorAll('tr.shipment-row:not([style*="display: none"])');
        const visibleCheckboxes = document.querySelectorAll('tr.shipment-row:not([style*="display: none"]) .shipment-checkbox');
        const checkedVisibleCheckboxes = document.querySelectorAll('tr.shipment-row:not([style*="display: none"]) .shipment-checkbox:checked');
        
        if (visibleCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedVisibleCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedVisibleCheckboxes.length === visibleCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }
    
    // ค้นหาตาม customer code หรือ tracking number
    document.getElementById('customerCodeSearch').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('tr.shipment-row');
        
        rows.forEach(function(row) {
            const trackingNumber = row.cells[1].textContent.toLowerCase();
            const customerCode = row.cells[2].textContent.toLowerCase();
            const customerName = row.cells[3].textContent.toLowerCase();
            
            if (trackingNumber.includes(searchValue) || 
                customerCode.includes(searchValue) || 
                customerName.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        updateSelectAllCheckbox();
    });
    
    // Select All checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const isChecked = this.checked;
        const visibleCheckboxes = document.querySelectorAll('tr.shipment-row:not([style*="display: none"]) .shipment-checkbox');
        
        visibleCheckboxes.forEach(function(checkbox) {
            checkbox.checked = isChecked;
        });
        
        updateTotalAmount();
        updateCustomerSelection();
    });
    
    // Individual checkboxes
    document.querySelectorAll('.shipment-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateTotalAmount();
            updateSelectAllCheckbox();
            updateCustomerSelection();
        });
    });
    
    // Select All Visible button
    document.getElementById('selectAllVisibleBtn').addEventListener('click', function() {
        const visibleCheckboxes = document.querySelectorAll('tr.shipment-row:not([style*="display: none"]) .shipment-checkbox');
        
        visibleCheckboxes.forEach(function(checkbox) {
            checkbox.checked = true;
        });
        
        updateTotalAmount();
        updateSelectAllCheckbox();
        updateCustomerSelection();
    });
    
    // Deselect All button
    document.getElementById('deselectAllBtn').addEventListener('click', function() {
        document.querySelectorAll('.shipment-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
        });
        
        document.getElementById('selectAll').checked = false;
        updateTotalAmount();
        updateSelectAllCheckbox();
    });
    
    // ฟังก์ชันสำหรับเลือกลูกค้าอัตโนมัติ
    function updateCustomerSelection() {
        const checkboxes = document.querySelectorAll('.shipment-checkbox:checked');
        let firstCheckedCustomerCode = null;
        
        for (let i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                firstCheckedCustomerCode = checkboxes[i].getAttribute('data-customer-code');
                break;
            }
        }
        
        if (firstCheckedCustomerCode && firstCheckedCustomerCode !== 'N/A') {
            const options = customerSelect.options;
            
            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                if (option.text.includes('(' + firstCheckedCustomerCode + ')')) {
                    customerSelect.value = option.value;
                    selectedCustomerDisplay.textContent = option.text;
                    break;
                }
            }
        }
    }
    
    // Form validation
    document.getElementById('editInvoiceForm').addEventListener('submit', function(e) {
        const checkedCheckboxes = document.querySelectorAll('.shipment-checkbox:checked');
        
        if (checkedCheckboxes.length === 0) {
            e.preventDefault();
            alert('<?= __('please_select_at_least_one_shipment') ?>');
            return false;
        }
        
        if (!customerSelect.value) {
            e.preventDefault();
            alert('<?= __('please_select_a_customer') ?>');
            return false;
        }
        
        return true;
    });
    
    // Initialize
    updateTotalAmount();
    updateSelectAllCheckbox();

    // ฟังก์ชันสำหรับเพิ่มค่าใช้จ่ายเพิ่มเติม
    let chargeIndex = <?= !empty($additionalCharges) ? count($additionalCharges) : 0 ?>;
    
    document.getElementById('addCharge').addEventListener('click', function() {
        const tbody = document.querySelector('#chargesTable tbody');
        const newRow = document.createElement('tr');
        
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control" name="charges[${chargeIndex}][description]" required>
            </td>
            <td>
                <select class="form-select" name="charges[${chargeIndex}][charge_type]">
                    <option value="fee"><?= __('fee') ?></option>
                    <option value="discount"><?= __('discount') ?></option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control" name="charges[${chargeIndex}][amount]" step="0.01" min="0" required>
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="charges[${chargeIndex}][is_percentage]" value="1">
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-charge">
                    <i class="fas fa-trash"></i> <?= __('remove') ?>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        chargeIndex++;
        
        // เพิ่ม event listener สำหรับปุ่มลบที่เพิ่งสร้าง
        newRow.querySelector('.remove-charge').addEventListener('click', function() {
            this.closest('tr').remove();
        });
    });
    
    // เพิ่ม event listener สำหรับปุ่มลบที่มีอยู่แล้ว
    document.querySelectorAll('.remove-charge').forEach(function(button) {
        button.addEventListener('click', function() {
            this.closest('tr').remove();
        });
    });
});
</script>

<style>
.dropdown-menu.show {
    display: block;
}
</style>

<?php include 'views/layout/footer.php'; ?>

