<?php
/**
 * Alert messages component
 * 
 * This file displays success and error messages stored in session
 * Include this file in any page where you want to show alert messages
 * 
 * Usage:
 * $_SESSION['success'] = 'Operation completed successfully';
 * $_SESSION['error'] = 'An error occurred';
 * 
 * Then include this file: include 'views/layout/alerts.php';
 */
?>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['warning'])): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['warning']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['warning']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['info'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['info']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['info']); ?>
<?php endif; ?>

