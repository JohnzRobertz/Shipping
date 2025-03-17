<?php include 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= __('delete_invoice') ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard"><?= __('dashboard') ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=invoice"><?= __('invoices') ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=invoice&action=view&id=<?= $invoice['id'] ?>"><?= __('view_invoice') ?></a></li>
        <li class="breadcrumb-item active"><?= __('delete_invoice') ?></li>
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
            
            <form action="index.php?page=invoice&action=delete" method="post">
                <input type="hidden" name="id" value="<?= $invoice['id'] ?>">
                <input type="hidden" name="confirm" value="yes">
                <div class="mb-3">
                    <p><?= __('delete_invoice_confirmation') ?></p>
                    <button type="submit" class="btn btn-danger"><?= __('delete_invoice') ?></button>
                    <a href="index.php?page=invoice&action=view&id=<?= $invoice['id'] ?>" class="btn btn-secondary"><?= __('cancel') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

