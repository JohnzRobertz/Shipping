<?php include 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= __('delete_customer') ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href  ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard"><?= __('dashboard') ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=customer"><?= __('customers') ?></a></li>
        <li class="breadcrumb-item active"><?= __('delete_customer') ?></li>
    </ol>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= __('confirm_deletion') ?>
        </div>
        <div class="card-body">
            <div class="alert alert-danger">
                <h4 class="alert-heading"><?= __('warning') ?>!</h4>
                <p><?= __('delete_customer_confirmation') ?></p>
                <hr>
                <p class="mb-0"><?= __('customer') ?>: <strong><?= htmlspecialchars($customer['name']) ?></strong> (<?= htmlspecialchars($customer['code'] ?? __('not_assigned')) ?>)</p>
            </div>
            
            <form action="index.php?page=customer&action=delete&id=<?= $customer['id'] ?>" method="post">
                <input type="hidden" name="confirm" value="yes">
                <div class="d-flex justify-content-end mt-4">
                    <a href="index.php?page=customer&action=view&id=<?= $customer['id'] ?>" class="btn btn-secondary me-2">
                        <i class="bi bi-x-circle me-1"></i> <?= __('cancel') ?>
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> <?= __('yes_delete_customer') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

