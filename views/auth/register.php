<?php include 'views/layout/header.php'; ?>

<?php if (isLoggedIn()): ?>
    <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title h4 mb-0">
                    <i class="bi bi-person-plus me-2"></i> <?php echo __('register'); ?>
                </h2>
            </div>
            <div class="card-body p-4">
                <form action="index.php?page=auth&action=store" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label"><?php echo __('name'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><?php echo __('email'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label"><?php echo __('password'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label"><?php echo __('confirm_password'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i> <?php echo __('register'); ?>
                        </button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p><?php echo __('already_have_account'); ?> <a href="index.php?page=auth&action=login"><?php echo __('login'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<?php include 'views/layout/footer.php'; ?>

