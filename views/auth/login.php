<?php include 'views/layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <?php if (isLoggedIn()): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo __('already_logged_in'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="text-center mb-4">
                    <i class="bi bi-box-arrow-in-right me-2"></i><?php echo __('login'); ?>
                </h4>
                
                <form action="index.php?page=auth&action=authenticate" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><?php echo __('email'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email" name="email" id="email" class="form-control border-start-0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label"><?php echo __('password'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" name="password" id="password" class="form-control border-start-0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="remember_me" id="remember_me" class="form-check-input">
                            <label for="remember_me" class="form-check-label"><?php echo __('remember_me'); ?></label>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i><?php echo __('login'); ?>
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p class="mb-0">
                        <?php echo __('dont_have_account'); ?> 
                        <a href="index.php?page=auth&action=register" class="text-primary text-decoration-none">
                            <?php echo __('register'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

