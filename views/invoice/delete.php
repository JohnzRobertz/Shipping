<?php include 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= __('delete_invoice') ?></h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-trash me-1"></i>
            <?= __('delete_invoice') ?> #<?= htmlspecialchars($invoice['invoice_number']) ?>
        </div>
        <div class="card-body">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong><?= __('warning') ?>:</strong> <?= __('delete_invoice_warning') ?>
            </div>
            
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
            
            <form action="index.php?page=invoice&action=delete" method="post" class="mt-4">
                <input type="hidden" name="id" value="<?= htmlspecialchars($invoice['id']) ?>">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirm" name="confirm" value="yes" required>
                    <label class="form-check-label" for="confirm">
                        <?= __('confirm_delete_invoice') ?>
                    </label>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-danger"><?= __('delete_invoice') ?></button>
                    <a href="index.php?page=invoice&action=view&id=<?= htmlspecialchars($invoice['id']) ?>" class="btn btn-secondary"><?= __('cancel') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

