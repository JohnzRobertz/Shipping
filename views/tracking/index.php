<?php
/**
 * Tracking Index Page
 * Version: 1.2.1
 * Last Updated: 2025-03-14
 * Changes: Improved mobile responsiveness
 */
include 'views/layout/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-3 p-md-4">
                    <h2 class="text-center mb-3 mb-md-4">
                        <i class="bi bi-search me-2"></i> <?php echo __('track_shipment'); ?>
                    </h2>

                    <!-- Tracking Form -->
                    
                <div class="card-body p-4">
                    <form action="index.php" method="get" class="mb-0">
                        <input type="hidden" name="page" value="tracking">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="tracking_number" id="tracking_number" 
                                   placeholder="<?php echo __('enter_tracking_number'); ?>" 
                                   value="<?php echo isset($_GET['tracking_number']) ? htmlspecialchars($_GET['tracking_number']) : ''; ?>" required
                                   autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search me-2 d-none d-sm-inline"></i><?php echo __('track'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($shipment) && $shipment): ?>
                <!-- Tracking Results -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <h4 class="mb-2 mb-md-0">
                                <i class="bi bi-info-circle me-2"></i> <?php echo __('tracking_result'); ?>
                            </h4>
                            <div>
                                <button type="button" class="btn btn-sm btn-light" onclick="printTrackingDetails()">
                                    <i class="bi bi-printer me-1"></i> <?php echo __('print'); ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-light ms-2" onclick="shareTracking()">
                                    <i class="bi bi-share me-1"></i> <?php echo __('share_tracking'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <!-- Current Status -->
                        <div class="mb-4">
                            <div class="tracking-status-card p-3 bg-light rounded">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <h5 class="mb-1"><?php echo __('tracking_number'); ?>:</h5>
                                        <div class="d-flex align-items-center">
                                            <h3 class="mb-0 fs-4 fs-md-3"><?php echo htmlspecialchars($shipment['tracking_number']); ?></h3>
                                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?php echo htmlspecialchars($shipment['tracking_number']); ?>')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 text-start text-md-end">
                                        <h5 class="mb-1"><?php echo __('current_status'); ?>:</h5>
                                        <span class="badge bg-<?php echo getStatusBadgeClass($shipment['status']); ?> fs-6 p-2">
                                            <i class="bi bi-<?php echo getStatusIcon($shipment['status']); ?> me-1"></i>
                                            <?php echo __('status_' . strtolower($shipment['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Domestic Shipping Information -->
                        <?php if (!empty($shipment['domestic_carrier']) && !empty($shipment['domestic_tracking_number'])): ?>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-truck me-2"></i><?php echo __('domestic_shipping_information'); ?></h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <?php echo __('package_transferred_to_domestic_carrier'); ?>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="text-muted"><?php echo __('domestic_carrier'); ?></h6>
                                            <h5 class="mb-0"><?php echo htmlspecialchars($shipment['domestic_carrier']); ?></h5>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="text-muted"><?php echo __('domestic_tracking_number'); ?></h6>
                                            <div class="d-flex align-items-center">
                                                <h5 class="mb-0 me-2 text-break"><?php echo htmlspecialchars($shipment['domestic_tracking_number']); ?></h5>
                                                <button class="btn btn-sm btn-outline-secondary flex-shrink-0" onclick="copyToClipboard('<?php echo htmlspecialchars($shipment['domestic_tracking_number']); ?>')">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mt-1">
                                    <?php if (!empty($shipment['handover_date'])): ?>
                                    <div class="col-12 col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="text-muted"><?php echo __('handover_date'); ?></h6>
                                            <h5 class="mb-0"><?php echo date('d M Y', strtotime($shipment['handover_date'])); ?></h5>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-12 col-md-<?php echo !empty($shipment['handover_date']) ? '6' : '12'; ?>">
                                        <div class="card bg-light h-100">
                                            <div class="card-body text-center p-3">
                                                <h5 class="mb-3"><?php echo __('track_with_domestic_carrier'); ?></h5>
                                                <a href="#" class="btn btn-primary" onclick="openTrackingLink('<?php echo htmlspecialchars($shipment['domestic_carrier']); ?>', '<?php echo htmlspecialchars($shipment['domestic_tracking_number']); ?>')">
                                                    <i class="bi bi-box-arrow-up-right me-2"></i><?php echo __('track_with'); ?> <?php echo htmlspecialchars($shipment['domestic_carrier']); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Status Timeline -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-clock-history me-2"></i><?php echo __('tracking_history'); ?>
                            </h5>
                            
                            <?php if (isset($trackingHistory) && !empty($trackingHistory)): ?>
                                <div class="timeline">
                                    <?php foreach ($trackingHistory as $index => $history): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-<?php echo getStatusBadgeClass($history['status']); ?>">
                                                <i class="bi bi-<?php echo getStatusIcon($history['status']); ?> text-white"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-heading">
                                                    <div class="d-flex flex-column flex-md-row justify-content-md-between">
                                                        <h6 class="mb-1 mb-md-0 fw-bold"><?php echo __('status_' . strtolower($history['status'])); ?></h6>
                                                        <span class="text-muted"><?php echo date('d M Y, H:i', strtotime($history['timestamp'])); ?></span>
                                                    </div>
                                                </div>
                                                <div class="timeline-body">
                                                    <p class="mb-0">
                                                        <?php if (!empty($history['location'])): ?>
                                                            <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($history['location']); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                    <p class="mb-0"><?php echo htmlspecialchars($history['description']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i> <?php echo __('no_tracking_history'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Sender & Receiver Info -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="bi bi-person me-2"></i><?php echo __('sender_information'); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong><?php echo __('name'); ?>:</strong> <?php echo htmlspecialchars($shipment['sender_name']); ?></p>
                                        <p class="mb-1"><strong><?php echo __('contact'); ?>:</strong> <?php echo htmlspecialchars($shipment['sender_contact']); ?></p>
                                        <?php if (!empty($shipment['sender_phone'])): ?>
                                            <p class="mb-0"><strong><?php echo __('phone'); ?>:</strong> <?php echo htmlspecialchars($shipment['sender_phone']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="bi bi-person me-2"></i><?php echo __('receiver_information'); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong><?php echo __('name'); ?>:</strong> <?php echo htmlspecialchars($shipment['receiver_name']); ?></p>
                                        <p class="mb-1"><strong><?php echo __('contact'); ?>:</strong> <?php echo htmlspecialchars($shipment['receiver_contact']); ?></p>
                                        <?php if (!empty($shipment['receiver_phone'])): ?>
                                            <p class="mb-0"><strong><?php echo __('phone'); ?>:</strong> 
                                                <a href="tel:<?php echo htmlspecialchars($shipment['receiver_phone']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($shipment['receiver_phone']); ?>
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Details -->
                        <!-- <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-box me-2"></i><?php echo __('package_details'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="border rounded p-3 text-center h-100">
                                            <h6 class="text-muted"><?php echo __('weight'); ?></h6>
                                            <h4 class="mb-0 fs-5 fs-md-4"><?php echo number_format($shipment['weight'], 2); ?> <?php echo __('kg'); ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="border rounded p-3 text-center h-100">
                                            <h6 class="text-muted"><?php echo __('dimensions'); ?></h6>
                                            <h4 class="mb-0 fs-5 fs-md-4"><?php echo number_format($shipment['length'], 2); ?> × <?php echo number_format($shipment['width'], 2); ?> × <?php echo number_format($shipment['height'], 2); ?> <?php echo __('cm'); ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="border rounded p-3 text-center h-100">
                                            <h6 class="text-muted"><?php echo __('volumetric_weight'); ?></h6>
                                            <h4 class="mb-0 fs-5 fs-md-4"><?php echo number_format($shipment['volumetric_weight'], 2); ?> <?php echo __('kg'); ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="border rounded p-3 text-center h-100">
                                            <h6 class="text-muted"><?php echo __('chargeable_weight'); ?></h6>
                                            <h4 class="mb-0 fs-5 fs-md-4"><?php echo number_format($shipment['chargeable_weight'], 2); ?> <?php echo __('kg'); ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <?php if (isset($lot) && $lot): ?>
                            <!-- Lot Information -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-truck me-2"></i><?php echo __('shipping_information'); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <h6 class="text-muted"><?php echo __('lot_number'); ?></h6>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($lot['lot_number']); ?></h5>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <h6 class="text-muted"><?php echo __('transport_type'); ?></h6>
                                                <h5 class="mb-0">
                                                    <?php 
                                                    $typeIcon = '';
                                                    switch ($lot['lot_type']) {
                                                        case 'sea':
                                                            $typeIcon = '<i class="bi bi-water me-1"></i>';
                                                            break;
                                                        case 'air':
                                                            $typeIcon = '<i class="bi bi-airplane me-1"></i>';
                                                            break;
                                                        case 'land':
                                                            $typeIcon = '<i class="bi bi-truck me-1"></i>';
                                                            break;
                                                    }
                                                    echo $typeIcon . __($lot['lot_type'] . '_freight'); 
                                                    ?>
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <h6 class="text-muted"><?php echo __('status'); ?></h6>
                                                <h5 class="mb-0">
                                                    <span class="badge bg-<?php echo getStatusBadgeClass($lot['status']); ?>">
                                                        <i class="bi bi-<?php echo getStatusIcon($lot['status']); ?> me-1"></i>
                                                        <?php echo __('status_' . strtolower($lot['status'])); ?>
                                                    </span>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-12 col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <h6 class="text-muted"><?php echo __('origin'); ?></h6>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($lot['origin']); ?></h5>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <h6 class="text-muted"><?php echo __('destination'); ?></h6>
                                                <h5 class="mb-0"><?php echo htmlspecialchars($lot['destination']); ?></h5>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <h6 class="text-muted"><?php echo __('estimated_delivery'); ?></h6>
                                                <h5 class="mb-0"><?php echo date('d M Y', strtotime($lot['arrival_date'])); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Share Tracking -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-share me-2"></i><?php echo __('share_tracking'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-8">
                                        <div class="input-group">
                                            <input type="text" id="trackingLink" class="form-control" value="<?php echo APP_URL . '/index.php?page=tracking&action=track&tracking_number=' . urlencode($shipment['tracking_number']); ?>" readonly>
                                            <button class="btn btn-primary" onclick="copyTrackingLink()">
                                                <i class="bi bi-clipboard me-1"></i> <span class="d-none d-md-inline"><?php echo __('copy_link'); ?></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4 text-center">
                                        <div id="trackingQRCode" class="d-inline-block"></div>
                                        <p class="mt-2 mb-0"><?php echo __('scan_qr'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode.js/qrcode.min.js"></script>

<script>
// Helper functions for status badge colors and icons
function getStatusBadgeClass(status) {
    switch (status.toLowerCase()) {
        case 'received':
            return 'info';
        case 'processing':
            return 'secondary';
        case 'in_transit':
            return 'primary';
        case 'out_for_delivery':
            return 'warning';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'on_hold':
            return 'dark';
        case 'exception':
            return 'danger';
        case 'customs_clearance':
            return 'info';
        case 'arrived_destination':
            return 'info';
        default:
            return 'secondary';
    }
}

function getStatusIcon(status) {
    switch (status.toLowerCase()) {
        case 'received':
            return 'box-seam';
        case 'processing':
            return 'gear';
        case 'in_transit':
            return 'truck';
        case 'out_for_delivery':
            return 'bicycle';
        case 'delivered':
            return 'check-circle';
        case 'cancelled':
            return 'x-circle';
        case 'on_hold':
            return 'pause-circle';
        case 'exception':
            return 'exclamation-triangle';
        case 'customs_clearance':
            return 'clipboard-check';
        case 'arrived_destination':
            return 'geo-alt';
        default:
            return 'circle';
    }
}

// Copy tracking number to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            alert('<?php echo __('copied_to_clipboard'); ?>');
        }, function(err) {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

// Fallback copy method for older browsers
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            alert('<?php echo __('copied_to_clipboard'); ?>');
        } else {
            console.error('<?php echo __('could_not_copy'); ?>');
        }
    } catch (err) {
        console.error('<?php echo __('could_not_copy'); ?>', err);
    }
    
    document.body.removeChild(textArea);
}

// Copy tracking link to clipboard
function copyTrackingLink() {
    const trackingLink = document.getElementById('trackingLink');
    copyToClipboard(trackingLink.value);
}

// Print tracking details
function printTrackingDetails() {
    window.print();
}

// Share tracking
function shareTracking() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo __('track_shipment'); ?>',
            text: '<?php echo __('tracking_number'); ?>: <?php echo isset($shipment) ? $shipment['tracking_number'] : ''; ?>',
            url: document.getElementById('trackingLink').value
        })
        .catch(console.error);
    } else {
        copyTrackingLink();
    }
}

// Open tracking link for domestic carriers
function openTrackingLink(carrier, trackingNumber) {
    let trackingUrl = '';
    
    switch(carrier.toLowerCase()) {
        case 'thailand post':
        case 'thailandpost':
        case 'ไปรษณีย์ไทย':
            trackingUrl = 'https://track.thailandpost.co.th/?trackNumber=' + trackingNumber;
            break;
        case 'kerry':
        case 'kerry express':
        case 'เคอรี่':
            trackingUrl = 'https://th.kerryexpress.com/th/track/?track=' + trackingNumber;
            break;
        case 'flash':
        case 'flash express':
        case 'แฟลช':
            trackingUrl = 'https://www.flashexpress.co.th/tracking/?se=' + trackingNumber;
            break;
        case 'j&t':
        case 'j&t express':
        case 'เจแอนด์ที':
            trackingUrl = 'https://www.jtexpress.co.th/index/query/gzquery.html?bills=' + trackingNumber;
            break;
        case 'dhl':
            trackingUrl = 'https://www.dhl.com/th-th/home/tracking.html?tracking-id=' + trackingNumber;
            break;
        case 'lalamove':
        case 'ลาล่ามูฟ':
            trackingUrl = 'https://www.lalamove.com/thailand/bangkok/th/track?id=' + trackingNumber;
            break;
        default:
            trackingUrl = 'https://www.google.com/search?q=' + encodeURIComponent(carrier + ' ' + trackingNumber + ' tracking');
    }
    
    window.open(trackingUrl, '_blank');
}

// Generate QR code
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($shipment) && $shipment): ?>
    const trackingUrl = '<?php echo APP_URL . '/index.php?page=tracking&action=track&tracking_number=' . urlencode($shipment['tracking_number']); ?>';
    
    // Generate QR code
    new QRCode(document.getElementById('trackingQRCode'), {
        text: trackingUrl,
        width: 128,
        height: 128,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
    <?php endif; ?>
});

// Sync tracking number inputs between desktop and mobile
document.addEventListener('DOMContentLoaded', function() {
    const desktopTracking = document.querySelector('.desktop-tracking');
    const mobileTracking = document.querySelector('.mobile-tracking');
    
    if (desktopTracking && mobileTracking) {
        desktopTracking.addEventListener('input', function() {
            mobileTracking.value = this.value;
        });
        
        mobileTracking.addEventListener('input', function() {
            desktopTracking.value = this.value;
        });
    }
});
</script>

<style>
/* Timeline styling */
.timeline {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline:before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: 19px;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    top: 0;
    left: -40px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    z-index: 1;
}

.timeline-content {
    padding-bottom: 15px;
}

.timeline-item:last-child .timeline-content {
    padding-bottom: 0;
}

/* Print styles */
@media print {
    .navbar, .footer, form, .card-header button, #emailSubscriptionForm, #smsSubscriptionForm {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    
    .badge {
        border: 1px solid #000 !important;
        color: #000 !important;
        background-color: transparent !important;
    }
}

/* Tracking status card */
.tracking-status-card {
    border-left: 5px solid #0d6efd;
    background-color: #f8f9fa;
}

/* Mobile specific styles */
@media (max-width: 767.98px) {
    .timeline {
        padding-left: 30px;
    }
    
    .timeline:before {
        left: 14px;
    }
    
    .timeline-marker {
        left: -30px;
        width: 16px;
        height: 16px;
        font-size: 8px;
    }
    
    .tracking-status-card {
        padding: 10px !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    h3 {
        font-size: 1.5rem;
    }
    
    h4 {
        font-size: 1.25rem;
    }
    
    h5 {
        font-size: 1.1rem;
    }
    
    .btn {
        padding: 0.375rem 0.75rem;
    }
    
    .text-break {
        word-break: break-all;
    }
}

/* Mobile tracking form */
@media (max-width: 767.98px) {
    .form-control-lg {
        font-size: 18px !important;
    }
    
    .input-group-text {
        font-size: 1.25rem;
    }
    
    .btn-lg {
        font-size: 1.25rem;
    }
}
</style>

<style>
@media (max-width: 576px) {
    .input-group-lg > .form-control {
        font-size: 1rem;
    }
    .input-group-lg > .btn {
        font-size: 1rem;
        padding: 0.375rem 0.75rem;
    }
}
</style>

<?php include 'views/layout/footer.php'; ?>

