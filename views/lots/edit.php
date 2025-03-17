<?php include 'views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil me-2"></i> <?php echo __('edit_lot'); ?>: <?php echo htmlspecialchars($lot['lot_number']); ?></h2>
    <a href="index.php?page=lots&action=view&id=<?php echo $lot['id']; ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i> <?php echo __('back'); ?>
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="index.php?page=lots&action=update" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="id" value="<?php echo $lot['id']; ?>">
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="lot_number" class="form-label"><?php echo __('lot_number'); ?></label>
                        <input type="text" id="lot_number" class="form-control" value="<?php echo htmlspecialchars($lot['lot_number']); ?>" readonly>
                        <div class="form-text"><?php echo __('lot_number_cannot_be_changed'); ?></div>
                    </div>
                    <div class="mb-3">
                        <label for="lot_type" class="form-label"><?php echo __('lot_type'); ?></label>
                        <input type="text" id="lot_type" class="form-control" value="<?php echo __($lot['lot_type'] . '_freight'); ?>" readonly>
                        <div class="form-text"><?php echo __('lot_type_cannot_be_changed'); ?></div>
                    </div>
                    <div class="mb-3">
                        <label for="origin" class="form-label"><?php echo __('origin'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="origin" id="origin" class="form-control" value="<?php echo htmlspecialchars($lot['origin']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="destination" class="form-label"><?php echo __('destination'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="destination" id="destination" class="form-control" value="<?php echo htmlspecialchars($lot['destination']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="departure_date" class="form-label"><?php echo __('departure_date'); ?> <span class="text-danger">*</span></label>
                        <input type="date" name="departure_date" id="departure_date" class="form-control" value="<?php echo date('Y-m-d', strtotime($lot['departure_date'])); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="arrival_date" class="form-label"><?php echo __('arrival_date'); ?> <span class="text-danger">*</span></label>
                        <input type="date" name="arrival_date" id="arrival_date" class="form-control" value="<?php echo date('Y-m-d', strtotime($lot['arrival_date'])); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label"><?php echo __('status'); ?> <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="received" <?php echo $lot['status'] === 'received' ? 'selected' : ''; ?>><?php echo __('received'); ?></option>
                            <option value="in_transit" <?php echo $lot['status'] === 'in_transit' ? 'selected' : ''; ?>><?php echo __('in_transit'); ?></option>
                            <option value="arrived_destination" <?php echo $lot['status'] === 'arrived_destination' ? 'selected' : ''; ?>><?php echo __('arrived_destination'); ?></option>
                            <option value="local_delivery" <?php echo $lot['status'] === 'local_delivery' ? 'selected' : ''; ?>><?php echo __('local_delivery'); ?></option>
                            <option value="delivered" <?php echo $lot['status'] === 'delivered' ? 'selected' : ''; ?>><?php echo __('delivered'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="index.php?page=lots&action=view&id=<?php echo $lot['id']; ?>" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-2"></i> <?php echo __('cancel'); ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i> <?php echo __('save'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure arrival date is after departure date
    document.getElementById('departure_date').addEventListener('change', function() {
        const departureDate = this.value;
        document.getElementById('arrival_date').setAttribute('min', departureDate);
        
        // If arrival date is before departure date, reset it
        if (document.getElementById('arrival_date').value < departureDate) {
            document.getElementById('arrival_date').value = departureDate;
        }
    });
});
</script>

<?php include 'views/layout/footer.php'; ?>

