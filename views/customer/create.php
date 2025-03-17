<?php include 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= __('add_new_customer') ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard"><?= __('dashboard') ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=customer"><?= __('customers') ?></a></li>
        <li class="breadcrumb-item active"><?= __('add_new_customer') ?></li>
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
            <i class="bi bi-person-plus me-1"></i>
            <?= __('customer_information') ?>
        </div>
        <div class="card-body">
            <form action="index.php?page=customer&action=create" method="post" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label"><?= __('customer_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">
                                <?= __('please_enter_customer_name') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="code" class="form-label"><?= __('customer_code') ?></label>
                            <input type="text" class="form-control" id="code" name="code">
                            <div class="form-text"><?= __('customer_code_help') ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label"><?= __('email') ?></label>
                            <input type="email" class="form-control" id="email" name="email">
                            <div class="invalid-feedback">
                                <?= __('please_enter_valid_email') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label"><?= __('phone') ?></label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label"><?= __('address') ?></label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tax_id" class="form-label"><?= __('tax_id') ?></label>
                    <input type="text" class="form-control" id="tax_id" name="tax_id">
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <a href="index.php?page=customer" class="btn btn-secondary me-2">
                        <i class="bi bi-x-circle me-1"></i> <?= __('cancel') ?>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> <?= __('save_customer') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Form validation
    (function () {
        'use strict'
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>

<?php include 'views/layout/footer.php'; ?>

