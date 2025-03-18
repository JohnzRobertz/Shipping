<?php include 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= __('mark_invoice_as_paid') ?></h1>

    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-check me-1"></i>
            <?= __('mark_invoice_as_paid') ?> #<?= htmlspecialchars($invoice['invoice_number']) ?>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5><?= __('invoice_information') ?></h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%"><?= __('invoice_number') ?></th>
                            <td><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('customer') ?></th>
                            <td><?= htmlspecialchars($customer['name']) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('invoice_date') ?></th>
                            <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('due_date') ?></th>
                            <td><?= date('d/m/Y', strtotime($invoice['due_date'])) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('total_amount') ?></th>
                            <td><?= number_format($invoice['total_amount'], 2) ?> <?= __('currency') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <form action="index.php?page=invoice&action=markAsPaid" method="post" class="mt-4">
                <input type="hidden" name="id" value="<?= htmlspecialchars($invoice['id']) ?>">
                
                <div class="mb-3">
                    <label for="payment_date" class="form-label"><?= __('payment_date') ?></label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="payment_method" class="form-label"><?= __('payment_method') ?></label>
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <option value="bank_transfer"><?= __('bank_transfer') ?></option>
                        <option value="credit_card"><?= __('credit_card') ?></option>
                        <option value="cash"><?= __('cash') ?></option>
                        <option value="check"><?= __('check') ?></option>
                        <option value="other"><?= __('other') ?></option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="payment_reference" class="form-label"><?= __('payment_reference') ?></label>
                    <input type="text" class="form-control" id="payment_reference" name="payment_reference" placeholder="<?= __('payment_reference_placeholder') ?>">
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong><?= __('warning') ?>:</strong> <?= __('mark_as_paid_warning') ?>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-success"><?= __('mark_as_paid') ?></button>
                    <a href="index.php?page=invoice&action=view&id=<?= htmlspecialchars($invoice['id']) ?>" class="btn btn-secondary"><?= __('cancel') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

