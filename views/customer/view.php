<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <main class="col-lg-10 col-md-12 mx-auto px-md-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-person-badge me-2"></i><?= __('customer_details') ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="index.php?page=customer&action=edit&id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> <?= __('edit') ?>
                        </a>
                        <a href="index.php?page=invoice&action=create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-file-earmark-plus me-1"></i> <?= __('create_invoice') ?>
                        </a>
                        <a href="index.php?page=shipment&action=create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-box-seam me-1"></i> <?= __('create_shipment') ?>
                        </a>
                    </div>
                    <a href="index.php?page=customer" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> <?= __('back_to_list') ?>
                    </a>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Customer Information -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i><?= __('customer_information') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold"><?= __('customer_code') ?>:</div>
                                <div class="col-md-8">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($customer['code']) ?></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold"><?= __('name') ?>:</div>
                                <div class="col-md-8"><?= htmlspecialchars($customer['name']) ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold"><?= __('contact_person') ?>:</div>
                                <div class="col-md-8"><?= htmlspecialchars($customer['contact_person'] ?? '-') ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold"><?= __('email') ?>:</div>
                                <div class="col-md-8">
                                    <?php if (!empty($customer['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($customer['email']) ?>">
                                            <?= htmlspecialchars($customer['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold"><?= __('phone') ?>:</div>
                                <div class="col-md-8">
                                    <?php if (!empty($customer['phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($customer['phone']) ?>">
                                            <?= htmlspecialchars($customer['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold"><?= __('created_at') ?>:</div>
                                <div class="col-md-8"><?= date('d/m/Y H:i', strtotime($customer['created_at'])) ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 fw-bold"><?= __('updated_at') ?>:</div>
                                <div class="col-md-8"><?= date('d/m/Y H:i', strtotime($customer['updated_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="bi bi-geo-alt me-2"></i><?= __('address_information') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold"><?= __('address') ?>:</div>
                                <div class="col-md-8"><?= nl2br(htmlspecialchars($customer['address'] ?? '-')) ?></div>
                            </div>
                            
                            <?php if (!empty($customer['address'])): ?>
                            <div class="mt-4">
                                <a href="https://www.google.com/maps/search/<?= urlencode($customer['address'] . ', ' . ($customer['city'] ?? '') . ', ' . ($customer['state'] ?? '') . ' ' . ($customer['postal_code'] ?? '') . ', ' . ($customer['country'] ?? '')) ?>" 
                                   class="btn btn-sm btn-outline-secondary" target="_blank">
                                    <i class="bi bi-map me-1"></i> <?= __('view_on_map') ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-primary h-100 py-2 shadow-sm">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        <?= __('total_invoices') ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= number_format($statistics['total_invoices'] ?? 0) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-file-earmark-text fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-success h-100 py-2 shadow-sm">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        <?= __('total_shipments') ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= number_format($statistics['total_shipments'] ?? 0) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-box-seam fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-info h-100 py-2 shadow-sm">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        <?= __('total_amount') ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= number_format($statistics['total_amount'] ?? 0, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-cash-stack fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-warning h-100 py-2 shadow-sm">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        <?= __('unpaid_amount') ?>
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold"><?= number_format($statistics['unpaid_amount'] ?? 0, 2) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-cash fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="card shadow-sm mb-4" id="recent-invoices">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-file-earmark-text me-2"></i><?= __('recent_invoices') ?></h5>
                        <a href="index.php?page=invoice&customer=<?= urlencode($customer['name']) ?>" class="btn btn-sm btn-outline-primary">
                            <?= __('view_all_invoices') ?>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><?= __('invoice_number') ?></th>
                                    <th><?= __('date') ?></th>
                                    <th><?= __('due_date') ?></th>
                                    <th class="text-end"><?= __('amount') ?></th>
                                    <th><?= __('status') ?></th>
                                    <th class="text-center"><?= __('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($invoices)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                            <?= __('no_invoices_found') ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($invoices as $invoice): ?>
                                        <tr>
                                            <td>
                                                <a href="index.php?page=invoice&action=view&id=<?= $invoice['id'] ?>" class="fw-bold text-decoration-none">
                                                    <?= htmlspecialchars($invoice['invoice_number']) ?>
                                                </a>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($invoice['due_date'])) ?>
                                                <?php if ($invoice['status'] === 'unpaid' && strtotime($invoice['due_date']) < time()): ?>
                                                    <span class="badge bg-danger ms-1"><?= __('overdue') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold"><?= number_format($invoice['total_amount'], 2) ?></td>
                                            <td>
                                                <?php if ($invoice['status'] === 'paid'): ?>
                                                    <span class="badge bg-success"><?= __('paid') ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning"><?= __('unpaid') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="index.php?page=invoice&action=view&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-secondary" title="<?= __('view') ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <?php if ($invoice['status'] === 'unpaid'): ?>
                                                        <a href="index.php?page=invoice&action=edit&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-primary" title="<?= __('edit') ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="index.php?page=invoice&action=print&id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-info" target="_blank" title="<?= __('print') ?>">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination for Invoices -->
                <?php if (($totalInvoicePages ?? 0) > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Invoice page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?= ($invoicePage ?? 1) <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="index.php?page=customer&action=view&id=<?= $customer['id'] ?>&invoice_page=<?= ($invoicePage ?? 1) - 1 ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= ($totalInvoicePages ?? 1); $i++): ?>
                                    <li class="page-item <?= $i === ($invoicePage ?? 1) ? 'active' : '' ?>">
                                        <a class="page-link" href="index.php?page=customer&action=view&id=<?= $customer['id'] ?>&invoice_page=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= ($invoicePage ?? 1) >= ($totalInvoicePages ?? 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="index.php?page=customer&action=view&id=<?= $customer['id'] ?>&invoice_page=<?= ($invoicePage ?? 1) + 1 ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>

