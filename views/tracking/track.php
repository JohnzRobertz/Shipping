<?php require_once 'views/layout/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= getTranslation('track_shipment') ?></h4>
                </div>
                <div class="card-body">
                    <form action="index.php?page=tracking&action=track" method="post" class="mb-4">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="tracking_number" class="form-control form-control-lg" placeholder="<?= getTranslation('enter_tracking_number') ?>" value="<?= isset($tracking_number) ? htmlspecialchars($tracking_number) : '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-box-arrow-right me-2"></i><?= getTranslation('track') ?>
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($shipment)): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-primary bg-opacity-25">
                                <h5 class="mb-0"><?= getTranslation('shipment_information') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><?= getTranslation('tracking_number') ?></h4>
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="badge bg-primary fs-5 p-2"><?= htmlspecialchars($shipment['tracking_number']) ?></span>
                                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('<?= htmlspecialchars($shipment['tracking_number']) ?>')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong><?= getTranslation('status') ?>:</strong>
                                            <?php
                                            $statusClass = 'bg-secondary';
                                            $statusText = getTranslation('processing');
                                            
                                            if (isset($shipment['status'])) {
                                                switch ($shipment['status']) {
                                                    case 'received':
                                                        $statusClass = 'bg-secondary';
                                                        $statusText = getTranslation('received');
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'bg-secondary';
                                                        $statusText = getTranslation('processing');
                                                        break;
                                                    case 'in_transit':
                                                        $statusClass = 'bg-primary';
                                                        $statusText = getTranslation('in_transit');
                                                        break;
                                                    case 'arrived_destination':
                                                        $statusClass = 'bg-info';
                                                        $statusText = getTranslation('arrived_destination');
                                                        break;
                                                    case 'out_for_delivery':
                                                        $statusClass = 'bg-warning';
                                                        $statusText = getTranslation('out_for_delivery');
                                                        break;
                                                    case 'delivered':
                                                        $statusClass = 'bg-success';
                                                        $statusText = getTranslation('delivered');
                                                        break;
                                                }
                                            }
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong><?= getTranslation('created_at') ?>:</strong>
                                            <?= date('d/m/Y H:i', strtotime($shipment['created_at'])) ?>
                                        </div>
                                        
                                        <?php if (isset($shipment['estimated_delivery'])): ?>
                                        <div class="mb-3">
                                            <strong><?= getTranslation('estimated_delivery') ?>:</strong>
                                            <?= date('d/m/Y', strtotime($shipment['estimated_delivery'])) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h4><?= getTranslation('package_details') ?></h4>
                                        <div class="mb-3">
                                            <strong><?= getTranslation('weight') ?>:</strong>
                                            <?= number_format($shipment['weight'], 2) ?> <?= getTranslation('kg') ?>
                                        </div>
                                        
                                        <?php if (isset($shipment['length']) && isset($shipment['width']) && isset($shipment['height'])): ?>
                                        <div class="mb-3">
                                            <strong><?= getTranslation('dimensions') ?>:</strong>
                                            <?= number_format($shipment['length'], 2) ?> × 
                                            <?= number_format($shipment['width'], 2) ?> × 
                                            <?= number_format($shipment['height'], 2) ?> 
                                            <?= getTranslation('cm') ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($lot)): ?>
                                        <div class="mb-3">
                                            <strong><?= getTranslation('lot') ?>:</strong>
                                            <span class="badge bg-info"><?= htmlspecialchars($lot['lot_number']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- เพิ่มส่วนแสดงข้อมูลการขนส่งภายในประเทศ -->
                        <?php if (!empty($shipment['domestic_carrier']) && !empty($shipment['domestic_tracking_number'])): ?>
                        <div class="card mb-4 border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-truck me-2"></i><?= getTranslation('domestic_shipping_information') ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <?= getTranslation('package_transferred_to_domestic_carrier') ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <strong><?= getTranslation('domestic_carrier') ?>:</strong>
                                            <?= htmlspecialchars($shipment['domestic_carrier']) ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong><?= getTranslation('domestic_tracking_number') ?>:</strong>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2"><?= htmlspecialchars($shipment['domestic_tracking_number']) ?></span>
                                                <button class="btn btn-sm btn-outline-secondary me-2" onclick="copyToClipboard('<?= htmlspecialchars($shipment['domestic_tracking_number']) ?>')">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($shipment['handover_date'])): ?>
                                        <div class="mb-3">
                                            <strong><?= getTranslation('handover_date') ?>:</strong>
                                            <?= date('d/m/Y', strtotime($shipment['handover_date'])) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h5 class="mb-3"><?= getTranslation('track_with_domestic_carrier') ?></h5>
                                                <p class="mb-3"><?= getTranslation('continue_tracking_with_carrier') ?></p>
                                                <a href="#" class="btn btn-primary btn-lg" onclick="openTrackingLink('<?= htmlspecialchars($shipment['domestic_carrier']) ?>', '<?= htmlspecialchars($shipment['domestic_tracking_number']) ?>')">
                                                    <i class="bi bi-box-arrow-up-right me-2"></i><?= getTranslation('track_with') ?> <?= htmlspecialchars($shipment['domestic_carrier']) ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-header bg-primary bg-opacity-25">
                                <h5 class="mb-0"><?= getTranslation('tracking_history') ?></h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if (isset($tracking_history) && !empty($tracking_history)): ?>
                                <div class="timeline p-4">
                                    <?php foreach ($tracking_history as $index => $history): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker <?= $index === 0 ? 'timeline-marker-current' : '' ?>"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between mb-1">
                                                <h6 class="mb-0 fw-bold">
                                                    <?php
                                                    $statusText = $history['status'];
                                                    switch ($history['status']) {
                                                        case 'received':
                                                            $statusText = getTranslation('received');
                                                            break;
                                                        case 'processing':
                                                            $statusText = getTranslation('processing');
                                                            break;
                                                        case 'in_transit':
                                                            $statusText = getTranslation('in_transit');
                                                            break;
                                                        case 'arrived_destination':
                                                            $statusText = getTranslation('arrived_destination');
                                                            break;
                                                        case 'out_for_delivery':
                                                            $statusText = getTranslation('out_for_delivery');
                                                            break;
                                                        case 'delivered':
                                                            $statusText = getTranslation('delivered');
                                                            break;
                                                    }
                                                    echo $statusText;
                                                    ?>
                                                </h6>
                                                <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($history['timestamp'])) ?></span>
                                            </div>
                                            <p class="mb-0"><?= htmlspecialchars($history['description']) ?></p>
                                            <?php if (!empty($history['location'])): ?>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($history['location']) ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="p-4 text-center">
                                    <p class="text-muted"><?= getTranslation('no_tracking_history') ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
}
.timeline:before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-marker {
    position: absolute;
    left: -1.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #adb5bd;
    border: 2px solid #fff;
}
.timeline-marker-current {
    background: #0d6efd;
}
.timeline-content {
    padding-left: 1rem;
}
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('<?= getTranslation('copied_to_clipboard') ?>');
    }, function(err) {
        console.error('<?= getTranslation('could_not_copy') ?>', err);
    });
}

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
        default:
            trackingUrl = 'https://www.google.com/search?q=' + encodeURIComponent(carrier + ' ' + trackingNumber + ' tracking');
    }
    
    window.open(trackingUrl, '_blank');
}
</script>

<?php require_once 'views/layout/footer.php'; ?>

