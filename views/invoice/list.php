<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $lang['invoices']; ?></h1>
        <a href="index.php?page=invoice&action=create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> <?php echo $lang['create_invoice']; ?>
        </a>
    </div>
    
    <!-- ฟิลเตอร์การค้นหา -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="index.php" class="row g-3">
                <input type="hidden" name="page" value="invoice">
                
                <div class="col-md-3">
                    <label for="customer_code" class="form-label"><?php echo $lang['customer']; ?></label>
                    <select class="form-select" id="customer_code" name="customer_code">
                        <option value=""><?php echo $lang['all_customers']; ?></option>
                        <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['customer_code']; ?>" <?php echo ($filters['customer_code'] == $customer['customer_code']) ? 'selected' : ''; ?>>
                            <?php echo $customer['customer_code'] . ' - ' . $customer['customer_name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?php echo $lang['status']; ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?php echo $lang['all_statuses']; ?></option>
                        <option value="pending" <?php echo ($filters['status'] == 'pending') ? 'selected' : ''; ?>><?php echo $lang['pending']; ?></option>
                        <option value="sent" <?php echo ($filters['status'] == 'sent') ? 'selected' : ''; ?>><?php echo $lang['sent']; ?></option>
                        <option value="partially_paid" <?php echo ($filters['status'] == 'partially_paid') ? 'selected' : ''; ?>><?php echo $lang['partially_paid']; ?></option>
                        <option value="paid" <?php echo ($filters['status'] == 'paid') ? 'selected' : ''; ?>><?php echo $lang['paid']; ?></option>
                        <option value="overdue" <?php echo ($filters['status'] == 'overdue') ? 'selected' : ''; ?>><?php echo $lang['overdue']; ?></option>
                        <option value="cancelled" <?php echo ($filters['status'] == 'cancelled') ? 'selected' : ''; ?>><?php echo $lang['cancelled']; ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label"><?php echo $lang['date_from']; ?></label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $filters['date_from']; ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label"><?php echo $lang['date_to']; ?></label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $filters['date_to']; ?>">
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> <?php echo $lang['search']; ?>
                    </button>
                    <a href="index.php?page=invoice" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> <?php echo $lang['clear']; ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- รายการใบแจ้งหนี้ -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $lang['invoice_number']; ?></th>
                            <th><?php echo $lang['customer']; ?></th>
                            <th><?php echo $lang['invoice_date']; ?></th>
                            <th><?php echo $lang['due_date']; ?></th>
                            <th><?php echo $lang['total_amount']; ?></th>
                            <th><?php echo $lang['paid_amount']; ?></th>
                            <th><?php echo $lang['status']; ?></th>
                            <th><?php echo $lang['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($result['invoices']) > 0): ?>
                            <?php foreach ($result['invoices'] as $invoice): ?>
                            <tr>
                                <td>
                                    <a href="index.php?page=invoice&action=view&invoice_number=<?php echo $invoice['invoice_number']; ?>">
                                        <?php echo $invoice['invoice_number']; ?>
                                    </a>
                                </td>
                                <td><?php echo $invoice['customer_name']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($invoice['due_date'])); ?></td>
                                <td>฿<?php echo number_format($invoice['total_amount'], 2); ?></td>
                                <td>฿<?php echo number_format($invoice['paid_amount'], 2); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    
                                    switch ($invoice['status']) {
                                        case 'pending':
                                            $statusClass = 'bg-warning text-dark';
                                            $statusText = $lang['pending'];
                                            break;
                                        case 'sent':
                                            $statusClass = 'bg-info text-dark';
                                            $statusText = $lang['sent'];
                                            break;
                                        case 'partially_paid':
                                            $statusClass = 'bg-primary';
                                            $statusText = $lang['partially_paid'];
                                            break;
                                        case 'paid':
                                            $statusClass = 'bg-success';
                                            $statusText = $lang['paid'];
                                            break;
                                        case 'overdue':
                                            $statusClass = 'bg-danger';
                                            $statusText = $lang['overdue'];
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'bg-secondary';
                                            $statusText = $lang['cancelled'];
                                            break;
                                        default:
                                            $statusClass = 'bg-secondary';
                                            $statusText = $invoice['status'];
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $invoice['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $invoice['id']; ?>">
                                            <li>
                                                <a class="dropdown-item" href="index.php?page=invoice&action=view&invoice_number=<?php echo $invoice['invoice_number']; ?>">
                                                    <i class="bi bi-eye"></i> <?php echo $lang['view']; ?>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="index.php?page=invoice&action=print&invoice_number=<?php echo $invoice['invoice_number']; ?>" target="_blank">
                                                    <i class="bi bi-printer"></i> <?php echo $lang['print']; ?>
                                                </a>
                                            </li>
                                            <?php if ($invoice['status'] != 'paid' && $invoice['status'] != 'cancelled'): ?>
                                            <li>
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#recordPaymentModal" data-invoice-id="<?php echo $invoice['id']; ?>" data-invoice-number="<?php echo $invoice['invoice_number']; ?>" data-remaining="<?php echo $invoice['total_amount'] - $invoice['paid_amount']; ?>">
                                                    <i class="bi bi-cash"></i> <?php echo $lang['record_payment']; ?>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($invoice['status'] == 'pending'): ?>
                                            <li>
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#sendEmailModal" data-invoice-id="<?php echo $invoice['id']; ?>" data-invoice-number="<?php echo $invoice['invoice_number']; ?>" data-customer-email="<?php echo $invoice['contact_email']; ?>">
                                                    <i class="bi bi-envelope"></i> <?php echo $lang['send_email']; ?>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($invoice['status'] != 'cancelled'): ?>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#cancelInvoiceModal" data-invoice-id="<?php echo $invoice['id']; ?>" data-invoice-number="<?php echo $invoice['invoice_number']; ?>">
                                                    <i class="bi bi-x-circle"></i> <?php echo $lang['cancel']; ?>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-file-earmark-text fs-1 text-muted mb-3 d-block"></i>
                                    <p class="text-muted"><?php echo $lang['no_invoices_found']; ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- การแบ่งหน้า -->
        <?php if ($result['total_pages'] > 1): ?>
        <div class="card-footer bg-white border-0">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo ($result['page'] <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?page=invoice&page_num=<?php echo $result['page'] - 1; ?>&customer_code=<?php echo $filters['customer_code']; ?>&status=<?php echo $filters['status']; ?>&date_from=<?php echo $filters['date_from']; ?>&date_to=<?php echo $filters['date_to']; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
                    <li class="page-item <?php echo ($result['page'] == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="index.php?page=invoice&page_num=<?php echo $i; ?>&customer_code=<?php echo $filters['customer_code']; ?>&status=<?php echo $filters['status']; ?>&date_from=<?php echo $filters['date_from']; ?>&date_to=<?php echo $filters['date_to']; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($result['page'] >= $result['total_pages']) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?page=invoice&page_num=<?php echo $result['page'] + 1; ?>&customer_code=<?php echo $filters['customer_code']; ?>&status=<?php echo $filters['status']; ?>&date_from=<?php echo $filters['date_from']; ?>&date_to=<?php echo $filters['date_to']; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal บันทึกการชำระเงิน -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="index.php?page=invoice&action=recordPayment">
                <div class="modal-header">
                    <h5 class="modal-title" id="recordPaymentModalLabel"><?php echo $lang['record_payment']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" id="paymentInvoiceId">
                    
                    <div class="mb-3">
                        <label for="paymentInvoiceNumber" class="form-label"><?php echo $lang['invoice_number']; ?></label>
                        <input type="text" class="form-control" id="paymentInvoiceNumber" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label"><?php echo $lang['amount']; ?></label>
                        <div class="input-group">
                            <span class="input-group-text">฿</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text" id="remainingAmount"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_date" class="form-label"><?php echo $lang['payment_date']; ?></label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label"><?php echo $lang['payment_method']; ?></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="bank_transfer"><?php echo $lang['bank_transfer']; ?></option>
                            <option value="credit_card"><?php echo $lang['credit_card']; ?></option>
                            <option value="cash"><?php echo $lang['cash']; ?></option>
                            <option value="cheque"><?php echo $lang['cheque']; ?></option>
                            <option value="other"><?php echo $lang['other']; ?></option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reference_number" class="form-label"><?php echo $lang['reference_number']; ?></label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number">
                        <div class="form-text"><?php echo $lang['reference_number_help']; ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label"><?php echo $lang['notes']; ?></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo $lang['cancel']; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $lang['save']; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ส่งอีเมล -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="index.php?page=invoice&action=sendEmail">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendEmailModalLabel"><?php echo $lang['send_invoice_by_email']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" id="emailInvoiceId">
                    
                    <div class="mb-3">
                        <label for="emailInvoiceNumber" class="form-label"><?php echo $lang['invoice_number']; ?></label>
                        <input type="text" class="form-control" id="emailInvoiceNumber" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><?php echo $lang['email']; ?></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label"><?php echo $lang['subject']; ?></label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label"><?php echo $lang['message']; ?></label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo $lang['cancel']; ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $lang['send']; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ยกเลิกใบแจ้งหนี้ -->
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1" aria-labelledby="cancelInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="index.php?page=invoice&action=updateStatus">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelInvoiceModalLabel"><?php echo $lang['cancel_invoice']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" id="cancelInvoiceId">
                    <input type="hidden" name="status" value="cancelled">
                    
                    <p><?php echo $lang['cancel_invoice_confirmation']; ?> <strong id="cancelInvoiceNumber"></strong>?</p>
                    <p class="text-danger"><?php echo $lang['cancel_invoice_warning']; ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo $lang['no']; ?></button>
                    <button type="submit" class="btn btn-danger"><?php echo $lang['yes_cancel']; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal บันทึกการชำระเงิน
    const recordPaymentModal = document.getElementById('recordPaymentModal');
    if (recordPaymentModal) {
        recordPaymentModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const invoiceId = button.getAttribute('data-invoice-id');
            const invoiceNumber = button.getAttribute('data-invoice-number');
            const remaining = parseFloat(button.getAttribute('data-remaining'));
            
            document.getElementById('paymentInvoiceId').value = invoiceId;
            document.getElementById('paymentInvoiceNumber').value = invoiceNumber;
            document.getElementById('amount').value = remaining.toFixed(2);
            document.getElementById('remainingAmount').textContent = '<?php echo $lang['remaining_amount']; ?>: ฿' + remaining.toFixed(2);
        });
    }
    
    // Modal ส่งอีเมล
    const sendEmailModal = document.getElementById('sendEmailModal');
    if (sendEmailModal) {
        sendEmailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const invoiceId = button.getAttribute('data-invoice-id');
            const invoiceNumber = button.getAttribute('data-invoice-number');
            const customerEmail = button.getAttribute('data-customer-email');
            
            document.getElementById('emailInvoiceId').value = invoiceId;
            document.getElementById('emailInvoiceNumber').value = invoiceNumber;
            document.getElementById('email').value = customerEmail;
            document.getElementById('subject').value = '<?php echo $lang['invoice']; ?> #' + invoiceNumber;
            document.getElementById('message').value = '<?php echo $lang['default_invoice_email_message']; ?>';
        });
    }
    
    // Modal ยกเลิกใบแจ้งหนี้
    const cancelInvoiceModal = document.getElementById('cancelInvoiceModal');
    if (cancelInvoiceModal) {
        cancelInvoiceModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const invoiceId = button.getAttribute('data-invoice-id');
            const invoiceNumber = button.getAttribute('data-invoice-number');
            
            document.getElementById('cancelInvoiceId').value = invoiceId;
            document.getElementById('cancelInvoiceNumber').textContent = invoiceNumber;
        });
    }
});
</script>