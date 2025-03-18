<?php include 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-receipt me-1"></i>
            <?= __('create_new_invoice') ?> <small class="text-muted">(v4.2.2)</small>
        </div>
        <div class="card-body">
            <form action="index.php?page=invoice&action=store" method="post" id="createInvoiceForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label"><?= __('customer') ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="customerSearch" placeholder="<?= __('search_customer') ?>" autocomplete="off">
                                <select class="form-select d-none" id="customer_id" name="customer_id" required>
                                    <option value=""><?= __('select_customer') ?></option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>" data-name="<?= htmlspecialchars($customer['name']) ?>" data-code="<?= htmlspecialchars($customer['code'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($customer['name']) ?> (<?= htmlspecialchars($customer['code'] ?? 'N/A') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="input-group-text" id="selectedCustomerDisplay"><?= __('select_customer') ?></span>
                            </div>
                            <div id="customerSearchResults" class="dropdown-menu w-100" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="invoice_date" class="form-label"><?= __('invoice_date') ?></label>
                            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="due_date" class="form-label"><?= __('due_date') ?></label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><?= __('select_shipments') ?> <span class="text-danger">*</span></label>
                    <?php if (empty($unpaidShipments)): ?>
                        <div class="alert alert-warning">
                            <?= __('no_unpaid_shipments_available') ?>
                        </div>
                    <?php else: ?>
                        <!-- ส่วนควบคุมการค้นหาและกรอง -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="<?= __('search_by_customer_code_or_tracking') ?>" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary" id="clearSearch">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="filterCustomer">
                                    <option value=""><?= __('all_customers') ?></option>
                                    <?php 
                                    $uniqueCustomers = [];
                                    foreach ($unpaidShipments as $shipment) {
                                        $customerCode = $shipment['customer_code'] ?? 'N/A';
                                        $customerName = $shipment['customer_name'] ?? 'N/A';
                                        if (!isset($uniqueCustomers[$customerCode])) {
                                            $uniqueCustomers[$customerCode] = $customerName;
                                        }
                                    }
                                    foreach ($uniqueCustomers as $code => $name): 
                                    ?>
                                        <option value="<?= $code ?>"><?= htmlspecialchars($name) ?> (<?= htmlspecialchars($code) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="itemsPerPage">
                                    <option value="10">10 <?= __('per_page') ?></option>
                                    <option value="25" selected>25 <?= __('per_page') ?></option>
                                    <option value="50">50 <?= __('per_page') ?></option>
                                    <option value="100">100 <?= __('per_page') ?></option>
                                    <option value="all"><?= __('all_items') ?></option>
                                </select>
                            </div>
                            <div class="col-md-3 text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllVisible">
                                    <i class="bi bi-check-all"></i> <?= __('select_all_visible') ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                    <i class="bi bi-x-lg"></i> <?= __('deselect_all') ?>
                                </button>
                            </div>
                        </div>
                        
                        <!-- ส่วนแสดงข้อมูล shipments -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="shipmentsTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="50">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                                <label class="form-check-label" for="selectAll"></label>
                                            </div>
                                        </th>
                                        <th><?= __('tracking_number') ?></th>
                                        <th><?= __('customer_code') ?></th>
                                        <th><?= __('customer') ?></th>
                                        <th><?= __('origin') ?></th>
                                        <th><?= __('destination') ?></th>
                                        <th><?= __('status') ?></th>
                                        <th class="text-end"><?= __('amount') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="shipmentsTableBody">
                                    <!-- จะถูกเติมด้วย JavaScript -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-end"><?= __('total_selected') ?>:</th>
                                        <th id="totalAmount" class="text-end">0.00 <?= __('currency') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- ส่วนแสดงการแบ่งหน้า -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div id="paginationInfo" class="text-muted">
                                    <?= __('showing') ?> <span id="startItem">1</span> - <span id="endItem">10</span> <?= __('of') ?> <span id="totalItems"><?= count($unpaidShipments) ?></span> <?= __('items') ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-end" id="pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        
                        
                    <?php endif; ?>
                </div>
                
                <!-- ส่วนภาษีและค่าใช้จ่ายเพิ่มเติม -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-percent"></i> <?= __('tax_settings') ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="tax_rate" class="form-label"><?= __('tax_rate') ?></label>
                                    <div class="input-group">
                                        <select class="form-select" id="tax_rate_select" name="tax_rate_select">
                                            <option value="0">0%</option>
                                            <option value="0.07" selected>7%</option>
                                            <option value="0.1">10%</option>
                                            <option value="custom"><?= __('custom') ?></option>
                                        </select>
                                        <input type="number" class="form-control d-none" id="custom_tax_rate" placeholder="<?= __('enter_custom_tax_rate') ?>" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                        <input type="hidden" name="tax_rate" id="tax_rate" value="0.07">
                                    </div>
                                    <small class="form-text text-muted"><?= __('tax_rate_note') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-plus-circle"></i> <?= __('additional_charges') ?></span>
                                    <button type="button" class="btn btn-sm btn-primary" id="addChargeBtn">
                                        <i class="bi bi-plus"></i> <?= __('add_charge') ?>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0" id="additionalChargesTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th><?= __('type') ?></th>
                                                <th><?= __('description') ?></th>
                                                <th class="text-end"><?= __('amount') ?></th>
                                                <th width="50"><?= __('action') ?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="additionalChargesBody">
                                            <tr id="noAdditionalCharges">
                                                <td colspan="4" class="text-center"><?= __('no_additional_charges') ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label"><?= __('notes') ?></label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <?= __('invoice_creation_note') ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <?= __('invoice_summary') ?>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?= __('subtotal') ?>:</span>
                                    <span id="subtotalAmount">0.00 <?= __('currency') ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?= __('tax') ?> (<span id="taxRateDisplay">7</span>%):</span>
                                    <span id="taxAmount">0.00 <?= __('currency') ?></span>
                                </div>
                                <div id="additionalChargesSummary">
                                    <!-- Additional charges will be added here -->
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span><?= __('grand_total') ?>:</span>
                                    <span id="grandTotalAmount">0.00 <?= __('currency') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary" id="submitButton" <?= empty($unpaidShipments) ? 'disabled' : '' ?>>
                        <i class="bi bi-save"></i> <?= __('create_invoice') ?>
                    </button>
                    <a href="index.php?page=invoice" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> <?= __('cancel') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for adding additional charge -->
<div class="modal fade" id="addChargeModal" tabindex="-1" aria-labelledby="addChargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addChargeModalLabel"><?= __('add_additional_charge') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="chargeType" class="form-label"><?= __('charge_type') ?></label>
                    <select class="form-select" id="chargeType">
                        <option value="shipping"><?= __('shipping_fee') ?></option>
                        <option value="handling"><?= __('handling_fee') ?></option>
                        <option value="insurance"><?= __('insurance') ?></option>
                        <option value="discount"><?= __('discount') ?></option>
                        <option value="other"><?= __('other') ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="chargeDescription" class="form-label"><?= __('description') ?></label>
                    <input type="text" class="form-control" id="chargeDescription">
                </div>
                <div class="mb-3">
                    <label for="chargeAmount" class="form-label"><?= __('amount') ?></label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="chargeAmount" min="0" step="0.01">
                        <select class="form-select" id="chargeIsPercentage" style="max-width: 80px;">
                            <option value="0"><?= __('currency') ?></option>
                            <option value="1">%</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button type="button" class="btn btn-primary" id="saveChargeBtn"><?= __('add') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ข้อมูล shipments ทั้งหมด
    const rawShipments = <?= json_encode($unpaidShipments) ?>;
    console.log("Raw shipments count:", rawShipments.length);
    
    // กรองข้อมูลซ้ำตั้งแต่ต้นทาง
    const uniqueShipmentsMap = new Map();
    rawShipments.forEach(shipment => {
        // ใช้ ID เป็น key เพื่อกรองข้อมูลซ้ำ
        uniqueShipmentsMap.set(shipment.id.toString(), shipment);
    });
    
    // แปลง Map กลับเป็น Array
    const allShipments = Array.from(uniqueShipmentsMap.values());
    console.log("Unique shipments count:", allShipments.length);
    
    // ใช้ข้อมูลที่ไม่ซ้ำแล้ว
    let filteredShipments = [...allShipments];
    window.filteredShipments = filteredShipments;
    
    // ตัวแปรสำหรับการแบ่งหน้า
    let currentPage = 1;
    let itemsPerPage = 25;
    
    // ตัวแปรสำหรับการเลือก shipments
    const selectedShipments = new Map();
    console.log("Initial selectedShipments:", selectedShipments);
    
    // ตัวแปรสำหรับค่าใช้จ่ายเพิ่มเติม
    let additionalCharges = [];
    let chargeIndex = 0;
    
    // ฟังก์ชันสำหรับค้นหาลูกค้า
    initCustomerSearch();
    
    // ฟังก์ชันสำหรับการค้นหาและกรอง
    initSearchAndFilter();
    
    // ฟังก์ชันสำหรับการแบ่งหน้า
    initPagination();
    
    // ฟังก์ชันสำหรับการเลือก shipments
    initShipmentSelection();
    
    // ฟังก์ชันสำหรับการจัดการภาษี
    initTaxRateHandling();
    
    // ฟังก์ชันสำหรับการจัดการค่าใช้จ่ายเพิ่มเติม
    initAdditionalCharges();
    
    // ฟังก์ชันสำหรับการคำนวณยอดรวม
    initTotalCalculation();
    
    // ฟังก์ชันสำหรับการตรวจสอบฟอร์มก่อนส่ง
    initFormValidation();
    
    // แสดงข้อมูลเริ่มต้น
    renderShipments();
    updatePagination();
    
    // ฟังก์ชันสำหรับค้นหาลูกค้า
    function initCustomerSearch() {
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
    }
    
    // ฟังก์ชันสำหรับการค้นหาและกรอง
    function initSearchAndFilter() {
        const searchInput = document.getElementById('searchInput');
        const clearSearch = document.getElementById('clearSearch');
        const filterCustomer = document.getElementById('filterCustomer');
        const itemsPerPageSelect = document.getElementById('itemsPerPage');
        
        // ค้นหาเมื่อพิมพ์
        searchInput.addEventListener('input', debounce(function() {
            filterShipments();
        }, 300));
        
        // ล้างการค้นหา
        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            filterShipments();
        });
        
        // กรองตามลูกค้า
        filterCustomer.addEventListener('change', function() {
            filterShipments();
        });
        
        // เปลี่ยนจำนวนรายการต่อหน้า
        itemsPerPageSelect.addEventListener('change', function() {
            if (this.value === 'all') {
                itemsPerPage = filteredShipments.length || 1000; // ใช้จำนวนมากพอที่จะแสดงทั้งหมด
            } else {
                itemsPerPage = parseInt(this.value);
            }
            currentPage = 1;
            renderShipments();
            updatePagination();
        });
        
        // ปุ่มเลือกทั้งหมดที่แสดง
        document.getElementById('selectAllVisible').addEventListener('click', function() {
            const visibleRows = document.querySelectorAll('#shipmentsTableBody tr:not(.d-none)');
            visibleRows.forEach(row => {
                const checkbox = row.querySelector('.shipment-checkbox');
                if (checkbox) {
                    checkbox.checked = true;
                    const shipmentId = checkbox.value;
                    const index = parseInt(row.getAttribute('data-index'));
                    selectedShipments.set(shipmentId, filteredShipments[index]);
                }
            });
            updateSelectAllCheckbox();
            updateSelectedShipments();
        });
        
        // ปุ่มยกเลิกการเลือกทั้งหมด
        document.getElementById('deselectAll').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.shipment-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectedShipments.clear();
            document.getElementById('selectAll').checked = false;
            updateSelectedShipments();
        });
    }
    
    // ฟังก์ชัน debounce สำหรับลดการเรียกใช้ฟังก์ชันบ่อยเกินไป
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
    
    // ฟังก์ชันสำหรับกรอง shipments
    function filterShipments() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const customerFilter = document.getElementById('filterCustomer').value;
        
        // กรองข้อมูลตามเงื่อนไข
        const filtered = allShipments.filter(shipment => {
            // กรองตามการค้นหา
            const trackingNumber = (shipment.tracking_number || '').toLowerCase();
            const customerCode = (shipment.customer_code || 'N/A').toLowerCase();
            const customerName = (shipment.customer_name || 'N/A').toLowerCase();
            
            const matchesSearch = !searchValue || 
                trackingNumber.includes(searchValue) || 
                customerCode.includes(searchValue) || 
                customerName.includes(searchValue);
            
            // กรองตามลูกค้า
            const matchesCustomer = !customerFilter || shipment.customer_code === customerFilter;
            
            return matchesSearch && matchesCustomer;
        });
        
        console.log("Filtered shipments:", filtered.length);
        
        // อัพเดทข้อมูลที่กรองแล้ว
        filteredShipments = filtered;
        window.filteredShipments = filtered;
        
        // รีเซ็ตหน้าปัจจุบันเป็นหน้าแรก
        currentPage = 1;
        
        // อัพเดทการแสดงผล
        renderShipments();
        updatePagination();
    }
    
    // ฟังก์ชันสำหรับการแบ่งหน้า
    function initPagination() {
        const pagination = document.getElementById('pagination');
        
        // เพิ่ม event listener สำหรับการคลิกที่ปุ่มแบ่งหน้า
        pagination.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                e.preventDefault();
                const link = e.target.closest('a');
                if (link) {
                    const page = link.getAttribute('data-page');
                    if (page === 'prev') {
                        if (currentPage > 1) currentPage--;
                    } else if (page === 'next') {
                        if (currentPage < Math.ceil(filteredShipments.length / itemsPerPage)) currentPage++;
                    } else {
                        currentPage = parseInt(page);
                    }
                    renderShipments();
                    updatePagination();
                }
            }
        });
    }
    
    // ฟังก์ชันสำหรับอัพเดทการแบ่งหน้า
    function updatePagination() {
        const pagination = document.getElementById('pagination');
        const totalItems = filteredShipments.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        // อัพเดทข้อมูลการแสดงผล
        const startItem = totalItems > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
        const endItem = Math.min(currentPage * itemsPerPage, totalItems);
        
        document.getElementById('startItem').textContent = startItem;
        document.getElementById('endItem').textContent = endItem;
        document.getElementById('totalItems').textContent = totalItems;
        
        // สร้างปุ่มแบ่งหน้า
        let paginationHtml = '';
        
        // ปุ่มก่อนหน้า
        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="prev" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;
        
        // ปุ่มหมายเลขหน้า
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        if (startPage > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) {
                paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHtml += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
            }
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }
        
        // ปุ่มถัดไป
        paginationHtml += `
            <li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="next" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;
        
        pagination.innerHTML = paginationHtml;
    }
    
    // ฟังก์ชันสำหรับแสดง shipments ตามหน้าปัจจุบัน
    function renderShipments() {
        const tbody = document.getElementById('shipmentsTableBody');
        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, filteredShipments.length);
        
        // Debug: แสดงข้อมูลที่จะแสดงผล
        console.log("Rendering shipments:", start, end, filteredShipments.length);
        
        // ล้างข้อมูลเดิม
        tbody.innerHTML = '';
        
        // ถ้าไม่มีข้อมูล
        if (filteredShipments.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="8" class="text-center"><?= __('no_shipments_found') ?></td>`;
            tbody.appendChild(tr);
            return;
        }
        
        // แสดงข้อมูลตามหน้าปัจจุบัน
        for (let i = start; i < end; i++) {
            const shipment = filteredShipments[i];
            
            const tr = document.createElement('tr');
            tr.className = 'shipment-row';
            tr.setAttribute('data-index', i);
            tr.setAttribute('data-customer-code', shipment.customer_code || 'N/A');
            tr.setAttribute('data-id', shipment.id);
            
            // ตรวจสอบว่า shipment นี้ถูกเลือกไว้หรือไม่
            const isSelected = selectedShipments.has(shipment.id.toString());
            
            tr.innerHTML = `
                <td>
                    <div class="form-check">
                        <input class="form-check-input shipment-checkbox" type="checkbox" name="shipment_ids[]" 
                            value="${shipment.id}" data-price="${shipment.total_price}" 
                            data-customer-code="${shipment.customer_code || 'N/A'}" 
                            ${isSelected ? 'checked' : ''}>
                    </div>
                </td>
                <td>${shipment.tracking_number || 'N/A'}</td>
                <td>${shipment.customer_code || 'N/A'}</td>
                <td>${shipment.customer_name || 'N/A'}</td>
                <td>${shipment.origin || 'N/A'}</td>
                <td>${shipment.destination || 'N/A'}</td>
                <td><span class="badge bg-info">${shipment.status || 'N/A'}</span></td>
                <td class="text-end">${parseFloat(shipment.total_price || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <?= __('currency') ?></td>
            `;
            
            tbody.appendChild(tr);
        }
        
        // เพิ่ม event listener สำหรับ checkbox
        const checkboxes = tbody.querySelectorAll('.shipment-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const shipmentId = this.value;
                const index = parseInt(this.closest('tr').getAttribute('data-index'));
                
                if (this.checked) {
                    // ตรวจสอบว่ามีข้อมูลใน filteredShipments หรือไม่
                    if (index >= 0 && index < filteredShipments.length) {
                        selectedShipments.set(shipmentId, filteredShipments[index]);
                        console.log("Added shipment to selection:", shipmentId);
                    }
                } else {
                    selectedShipments.delete(shipmentId);
                    console.log("Removed shipment from selection:", shipmentId);
                }
                
                updateSelectAllCheckbox();
                updateSelectedShipments();
            });
        });
        
        updateSelectAllCheckbox();
    }
    
    // ฟังก์ชันสำหรับการเลือก shipments
    function initShipmentSelection() {
        // Checkbox เลือกทั้งหมด
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('#shipmentsTableBody .shipment-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            
                const shipmentId = checkbox.value;
                const index = parseInt(checkbox.closest('tr').getAttribute('data-index'));
            
                if (this.checked) {
                    selectedShipments.set(shipmentId, filteredShipments[index]);
                } else {
                    selectedShipments.delete(shipmentId);
                }
            });
        
            updateSelectedShipments();
        });
    }
    
    // ฟังก์ชันสำหรับอัพเดทสถานะของ checkbox "เลือกทั้งหมด"
    function updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('#shipmentsTableBody .shipment-checkbox');
        
        if (checkboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.disabled = true;
            return;
        }
        
        selectAllCheckbox.disabled = false;
        
        let allChecked = true;
        let allUnchecked = true;
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                allUnchecked = false;
            } else {
                allChecked = false;
            }
        });
        
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = !allChecked && !allUnchecked;
    }
    
    // ฟังก์ชันสำหรับอัพเดทรายการ shipments ที่เลือก
    function updateSelectedShipments() {
        // แสดงจำนวนที่เลือกในปุ่ม
        const selectedCount = selectedShipments.size;
        document.getElementById('deselectAll').innerHTML = `<i class="bi bi-x-lg"></i> <?= __('deselect_all') ?> (${selectedCount})`;
        
        // อัพเดทยอดรวม
        updateTotalAmount();
    }
    
    // ฟังก์ชันสำหรับการจัดการภาษี
    function initTaxRateHandling() {
        const taxRateSelect = document.getElementById('tax_rate_select');
        const customTaxRate = document.getElementById('custom_tax_rate');
        const taxRateInput = document.getElementById('tax_rate');
        
        taxRateSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customTaxRate.classList.remove('d-none');
                customTaxRate.focus();
            } else {
                customTaxRate.classList.add('d-none');
                taxRateInput.value = this.value;
                updateTotalAmount();
            }
        });
        
        customTaxRate.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                taxRateInput.value = value / 100;
                updateTotalAmount();
            }
        });
    }
    
    // ฟังก์ชันสำหรับการจัดการค่าใช้จ่ายเพิ่มเติม
    function initAdditionalCharges() {
        const addChargeBtn = document.getElementById('addChargeBtn');
        const addChargeModal = new bootstrap.Modal(document.getElementById('addChargeModal'));
        const saveChargeBtn = document.getElementById('saveChargeBtn');
        const chargeType = document.getElementById('chargeType');
        const chargeDescription = document.getElementById('chargeDescription');
        const chargeAmount = document.getElementById('chargeAmount');
        const chargeIsPercentage = document.getElementById('chargeIsPercentage');
        
        // เปิด modal เพื่อเพิ่มค่าใช้จ่าย
        addChargeBtn.addEventListener('click', function() {
            // รีเซ็ตฟอร์ม
            chargeType.value = 'shipping';
            chargeDescription.value = '';
            chargeAmount.value = '';
            chargeIsPercentage.value = '0';
            
            // แสดง modal
            addChargeModal.show();
        });
        
        // บันทึกค่าใช้จ่ายเพิ่มเติม
        saveChargeBtn.addEventListener('click', function() {
            // ตรวจสอบข้อมูล
            if (!chargeAmount.value || isNaN(parseFloat(chargeAmount.value))) {
                alert('<?= __('please_enter_valid_amount') ?>');
                return;
            }
            
            // สร้างข้อมูลค่าใช้จ่ายเพิ่มเติม
            const charge = {
                id: 'charge_' + chargeIndex++,
                type: chargeType.value,
                description: chargeDescription.value || chargeType.options[chargeType.selectedIndex].textContent,
                amount: parseFloat(chargeAmount.value),
                is_percentage: chargeIsPercentage.value === '1'
            };
            
            // เพิ่มลงในอาร์เรย์
            additionalCharges.push(charge);
            
            // อัพเดทการแสดงผล
            updateAdditionalCharges();
            
            // ปิด modal
            addChargeModal.hide();
        });
        
        // ลบค่าใช้จ่ายเพิ่มเติม - ใช้ event delegation ที่ parent element ที่ไม่ถูกสร้างใหม่
        document.getElementById('additionalChargesBody').addEventListener('click', function(e) {
            // ตรวจสอบว่าคลิกที่ปุ่มลบหรือไม่
            if (e.target.classList.contains('remove-charge') || e.target.closest('.remove-charge')) {
                const button = e.target.classList.contains('remove-charge') ? e.target : e.target.closest('.remove-charge');
                const chargeId = button.getAttribute('data-id');
                
                console.log("Removing charge with ID:", chargeId);
                
                // ลบออกจากอาร์เรย์
                const index = additionalCharges.findIndex(charge => charge.id === chargeId);
                if (index !== -1) {
                    additionalCharges.splice(index, 1);
                    console.log("Charge removed, remaining charges:", additionalCharges.length);
                    // อัพเดทการแสดงผลหลังจากลบ
                    updateAdditionalCharges();
                }
            }
        });
    }
    
    // ฟังก์ชันสำหรับอัพเดทการแสดงผลค่าใช้จ่ายเพิ่มเติม
    function updateAdditionalCharges() {
        const additionalChargesBody = document.getElementById('additionalChargesBody');
        const noAdditionalCharges = document.getElementById('noAdditionalCharges');
        
        console.log("Updating additional charges display, count:", additionalCharges.length);
        
        // ถ้าไม่มีค่าใช้จ่ายเพิ่มเติม
        if (additionalCharges.length === 0) {
            noAdditionalCharges.style.display = '';
            // ลบทุกแถวยกเว้น noAdditionalCharges
            while (additionalChargesBody.firstChild) {
                if (additionalChargesBody.firstChild.id !== 'noAdditionalCharges') {
                    additionalChargesBody.removeChild(additionalChargesBody.firstChild);
                } else {
                    break;
                }
            }
            updateTotalAmount();
            return;
        }
        
        // ซ่อนข้อความ "ไม่มีค่าใช้จ่ายเพิ่มเติม"
        noAdditionalCharges.style.display = 'none';
        
        // ลบทุกแถวยกเว้น noAdditionalCharges
        while (additionalChargesBody.firstChild) {
            if (additionalChargesBody.firstChild.id !== 'noAdditionalCharges') {
                additionalChargesBody.removeChild(additionalChargesBody.firstChild);
            } else {
                break;
            }
        }
        
        // สร้าง HTML สำหรับค่าใช้จ่ายเพิ่มเติม
        additionalCharges.forEach(charge => {
            const typeText = document.querySelector(`#chargeType option[value="${charge.type}"]`).textContent;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${typeText}</td>
                <td>${charge.description}</td>
                <td class="text-end">
                    ${charge.amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} 
                    ${charge.is_percentage ? '%' : '<?= __('currency') ?>'}
                    <input type="hidden" name="additional_charges[${charge.id}][type]" value="${charge.type}">
                    <input type="hidden" name="additional_charges[${charge.id}][description]" value="${charge.description}">
                    <input type="hidden" name="additional_charges[${charge.id}][amount]" value="${charge.amount}">
                    <input type="hidden" name="additional_charges[${charge.id}][is_percentage]" value="${charge.is_percentage ? 1 : 0}">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-charge" data-id="${charge.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            additionalChargesBody.insertBefore(tr, noAdditionalCharges);
        });
        
        // อัพเดทยอดรวม
        updateTotalAmount();
    }
    
    // ฟังก์ชันสำหรับการคำนวณยอดรวม
    function initTotalCalculation() {
        // ไม่ต้องทำอะไรเพิ่มเติม เพราะเราเรียกใช้ updateTotalAmount() เมื่อมีการเปลี่ยนแปลงการเลือก
    }
    
    // ฟังก์ชันสำหรับอัพเดทยอดรวม
    function updateTotalAmount() {
        let subtotal = 0;
        
        // คำนวณยอดรวมจาก shipments ที่เลือก
        selectedShipments.forEach(shipment => {
            subtotal += parseFloat(shipment.total_price);
        });
        
        // ดึงอัตราภาษี
        let taxRate = 0.07; // ค่าเริ่มต้น 7%
        const taxRateSelect = document.getElementById('tax_rate_select');
        const customTaxRate = document.getElementById('custom_tax_rate');
        const taxRateInput = document.getElementById('tax_rate');
        
        if (taxRateSelect.value === 'custom') {
            const customRate = parseFloat(customTaxRate.value);
            if (!isNaN(customRate)) {
                taxRate = customRate / 100;
            }
        } else {
            taxRate = parseFloat(taxRateSelect.value);
        }
        
        // อัพเดทค่าใน hidden input
        taxRateInput.value = taxRate;
        
        // คำนวณภาษี
        const taxAmount = subtotal * taxRate;
        
        // แสดงอัตราภาษีในหน้าสรุป
        document.getElementById('taxRateDisplay').textContent = (taxRate * 100).toFixed(0);
        
        // คำนวณค่าใช้จ่ายเพิ่มเติม
        let additionalChargesTotal = 0;
        let additionalChargesSummaryHtml = '';
        
        additionalCharges.forEach(charge => {
            let chargeAmount = 0;
            
            if (charge.is_percentage) {
                chargeAmount = subtotal * (charge.amount / 100);
            } else {
                chargeAmount = charge.amount;
            }
            
            // ถ้าเป็นส่วนลด ให้เป็นค่าลบ
            if (charge.type === 'discount') {
                chargeAmount = -Math.abs(chargeAmount);
            }
            
            additionalChargesTotal += chargeAmount;
            
            // เพิ่มรายการในสรุป
            additionalChargesSummaryHtml += `
                <div class="d-flex justify-content-between mb-2">
                    <span>${charge.description}:</span>
                    <span>${chargeAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <?= __('currency') ?></span>
                </div>
            `;
        });
        
        // อัพเดทสรุปค่าใช้จ่ายเพิ่มเติม
        document.getElementById('additionalChargesSummary').innerHTML = additionalChargesSummaryHtml;
        
        // คำนวณยอดรวมทั้งหมด
        const grandTotal = subtotal + taxAmount + additionalChargesTotal;
        
        // อัพเดทการแสดงผล
        document.getElementById('totalAmount').textContent = subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' <?= __('currency') ?>';
        document.getElementById('subtotalAmount').textContent = subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' <?= __('currency') ?>';
        document.getElementById('taxAmount').textContent = taxAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' <?= __('currency') ?>';
        document.getElementById('grandTotalAmount').textContent = grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' <?= __('currency') ?>';
        
        // อัพเดทสถานะปุ่มส่งฟอร์ม
        document.getElementById('submitButton').disabled = selectedShipments.size === 0;
    }
    
    // ฟังก์ชันสำหรับการตรวจสอบฟอร์มก่อนส่ง
    function initFormValidation() {
        document.getElementById('createInvoiceForm').addEventListener('submit', function(e) {
            // ตรวจสอบว่ามีการเลือก shipment หรือไม่
            if (selectedShipments.size === 0) {
                e.preventDefault();
                alert('<?= __('please_select_at_least_one_shipment') ?>');
                return false;
            }
            
            // ตรวจสอบว่ามีการเลือกลูกค้าหรือไม่
            const customerSelect = document.getElementById('customer_id');
            if (!customerSelect.value) {
                e.preventDefault();
                alert('<?= __('please_select_a_customer') ?>');
                return false;
            }
            
            // ตรวจสอบอัตราภาษี
            const taxRateSelect = document.getElementById('tax_rate_select');
            if (taxRateSelect.value === 'custom') {
                const customTaxRate = document.getElementById('custom_tax_rate');
                const value = parseFloat(customTaxRate.value);
                if (isNaN(value) || value < 0 || value > 100) {
                    e.preventDefault();
                    alert('<?= __('please_enter_valid_tax_rate') ?>');
                    return false;
                }
            }
            
            return true;
        });
    }
});
</script>

<style>
.dropdown-menu.show {
    display: block;
}
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 1020;
}
.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}
#selectedShipmentsTable {
    max-height: 200px;
    overflow-y: auto;
}
/* เพิ่ม CSS เพื่อปรับปรุง UI */
.shipment-row:hover {
    background-color: #f8f9fa;
}
.form-check-input.shipment-checkbox {
    cursor: pointer;
}
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}
.btn-outline-primary:hover, .btn-primary:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.badge {
    font-weight: 500;
}
.table th {
    background-color: #f8f9fa;
}
</style>

<?php include 'views/layout/footer.php'; ?>

