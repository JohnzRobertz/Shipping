<?php include 'views/layout/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-person-circle me-2"></i> <?php echo __('profile'); ?></h2>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="index.php?page=profile&action=update" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><?php echo __('name'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo __('email'); ?> <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label"><?php echo __('new_password'); ?></label>
                                    <input type="password" name="new_password" id="new_password" class="form-control">
                                    <div class="form-text"><?php echo __('leave_blank_password'); ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label"><?php echo __('confirm_password'); ?></label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> <?php echo __('save_changes'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

