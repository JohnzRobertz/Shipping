<?php
// Include header
include 'views/layout/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?= __('invoice_details') ?>: <?= htmlspecialchars($invoice['invoice_number']) ?>
                    </h5>
                    <div>
                        <a href="index.php?page=invoice" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?= __('back_to_list') ?>
                        </a>
                        
                        <?php if ($invoice['status'] === 'unpaid'): ?>
                            <a href="index.php?page=invoice&action=edit&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> <?= __('edit') ?>
                            </a>
                            <a href="index.php?page=invoice&action=markAsPaid&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-check"></i> <?= __('mark_as_paid') ?>
                            </a>
                        <?php endif; ?>
                        
                        <a href="index.php?page=invoice&action=print&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-info" target="_blank">
                            <i class="fas fa-print"></i> <?= __('print') ?>
                        </a>
                        
                        <!-- เพิ่มปุ่มดาวน์โหลด PDF พร้อมตัวเลือกภาษา -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-file-pdf"></i> <?= __('download_pdf') ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="invoice_pdf.php?id=<?= $invoice['id'] ?>&lang=th" target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i> <?= __('download_pdf_thai') ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="invoice_pdf.php?id=<?= $invoice['id'] ?>&lang=en" target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i> <?= __('download_pdf_english') ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <a href="index.php?page=invoice&action=delete&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> <?= __('delete') ?>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-2"><?= __('invoice_information') ?></h6>
                            <div><strong><?= __('invoice_number') ?>:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></div>
                            <div><strong><?= __('date') ?>:</strong> <?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></div>
                            <div><strong><?= __('due_date') ?>:</strong> <?= date('d/m/Y', strtotime($invoice['due_date'])) ?></div>
                            <div>
                                <strong><?= __('status') ?>:</strong> 
                                <?php if ($invoice['status'] === 'paid'): ?>
                                    <span class="badge bg-success"><?= __('paid') ?></span>
                                    <?php if (!empty($invoice['payment_date'])): ?>
                                        (<?= date('d/m/Y', strtotime($invoice['payment_date'])) ?>)
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning"><?= __('unpaid') ?></span>
                                    <?php if (strtotime($invoice['due_date']) < time()): ?>
                                        <span class="badge bg-danger"><?= __('overdue') ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($invoice['status'] === 'paid' && !empty($invoice['payment_method'])): ?>
                                <div><strong><?= __('payment_method') ?>:</strong> <?= htmlspecialchars($invoice['payment_method']) ?></div>
                            <?php endif; ?>
                            <?php if ($invoice['status'] === 'paid' && !empty($invoice['payment_reference'])): ?>
                                <div><strong><?= __('payment_reference') ?>:</strong> <?= htmlspecialchars($invoice['payment_reference']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2"><?= __('customer_information') ?></h6>
                            <div><strong><?= __('name') ?>:</strong> <?= htmlspecialchars($customer['name']) ?></div>
                            <div><strong><?= __('address') ?>:</strong> <?= nl2br(htmlspecialchars($customer['address'])) ?></div>
                            <div><strong><?= __('phone') ?>:</strong> <?= htmlspecialchars($customer['phone']) ?></div>
                            <div><strong><?= __('email') ?>:</strong> <?= htmlspecialchars($customer['email']) ?></div>
                            <?php if (!empty($customer['tax_id'])): ?>
                                <div><strong><?= __('tax_id') ?>:</strong> <?= htmlspecialchars($customer['tax_id']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h6 class="mb-3"><?= __('shipments') ?></h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><?= __('tracking_number') ?></th>
                                    <th><?= __('origin') ?></th>
                                    <th><?= __('destination') ?></th>
                                    <th><?= __('weight') ?> (<?= __('kg') ?>)</th>
                                    <th><?= __('dimensions') ?> (<?= __('cm') ?>)</th>
                                    <th><?= __('status') ?></th>
                                    <th><?= __('payment_status') ?></th>
                                    <th class="text-end"><?= __('amount') ?> (<?= __('currency') ?>)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($shipments)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center"><?= __('no_shipments_found') ?></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($shipments as $shipment): ?>
                                        <tr>
                                            <td>
                                                <a href="index.php?page=shipments&action=view&id=<?= $shipment['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($shipment['tracking_number']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($shipment['origin'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($shipment['destination'] ?? 'N/A') ?></td>
                                            <td><?= number_format($shipment['weight'], 2) ?></td>
                                            <td>
                                                <?php if (!empty($shipment['length']) && !empty($shipment['width']) && !empty($shipment['height'])): ?>
                                                    <?= number_format($shipment['length'], 1) ?> × 
                                                    <?= number_format($shipment['width'], 1) ?> × 
                                                    <?= number_format($shipment['height'], 1) ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                switch ($shipment['status']) {
                                                    case 'pending':
                                                        $statusClass = 'bg-warning';
                                                        break;
                                                    case 'in_transit':
                                                        $statusClass = 'bg-info';
                                                        break;
                                                    case 'delivered':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= __($shipment['status']) ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $paymentStatusClass = 'bg-secondary';
                                                switch ($shipment['payment_status']) {
                                                    case 'invoiced':
                                                        $paymentStatusClass = 'bg-warning';
                                                        break;
                                                    case 'paid':
                                                        $paymentStatusClass = 'bg-success';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $paymentStatusClass ?>"><?= __($shipment['payment_status']) ?></span>
                                            </td>
                                            <td class="text-end"><?= number_format($shipment['total_price'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end"><strong><?= __('subtotal') ?>:</strong></td>
                                    <td class="text-end"><strong><?= number_format($invoice['subtotal'] ?? $invoice['total_amount'], 2) ?></strong></td>
                                </tr>
                                
                                <?php if (!empty($additionalCharges)): ?>
                                    <?php foreach ($additionalCharges as $charge): ?>
                                        <tr>
                                            <td colspan="7" class="text-end">
                                                <?= htmlspecialchars($charge['description']) ?>
                                                <?php if ($charge['is_percentage'] == 1): ?>
                                                    (<?= number_format($charge['amount'], 2) ?>%)
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php
                                                $chargeAmount = $charge['is_percentage'] == 1 
                                                    ? ($invoice['subtotal'] ?? $invoice['total_amount']) * ($charge['amount'] / 100) 
                                                    : $charge['amount'];
                                                
                                                if ($charge['charge_type'] == 'discount') {
                                                    echo '-';
                                                    $chargeAmount = abs($chargeAmount);
                                                }
                                                
                                                echo number_format($chargeAmount, 2);
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (isset($invoice['tax_rate']) && $invoice['tax_rate'] > 0): ?>
                                        <tr>
                                            <td colspan="7" class="text-end"><?= __('tax') ?> (<?= number_format($invoice['tax_rate'] * 100, 0) ?>%):</td>
                                            <td class="text-end"><?= number_format($invoice['tax_amount'], 2) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <tr class="table-primary">
                                    <td colspan="7" class="text-end"><strong><?= __('total') ?>:</strong></td>
                                    <td class="text-end"><strong><?= number_format($invoice['total_amount'], 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <?php if (!empty($invoice['notes'])): ?>
                        <div class="mt-4">
                            <h6><?= __('notes') ?></h6>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(htmlspecialchars($invoice['notes'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'views/layout/footer.php';
?>

