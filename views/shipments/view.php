<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <!-- Header section -->
    <div class="d-flex justify-content-between align-items-center bg-primary text-white p-3 rounded-top">
        <h5 class="mb-0">รายละเอียดพัสดุ</h5>
        <div>
            <a href="index.php?page=shipments" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> กลับ
            </a>
            <a href="index.php?page=shipments&action=edit&id=<?= $shipment['id'] ?>" class="btn btn-warning btn-sm ms-2">
                <i class="bi bi-pencil"></i> แก้ไข
            </a>
            <button class="btn btn-danger btn-sm ms-2" onclick="confirmDelete(<?= $shipment['id'] ?>)">
                <i class="bi bi-trash"></i> ลบ
            </button>
        </div>
    </div>

    <!-- Main content -->
    <div class="bg-white p-4 rounded-bottom shadow-sm">
        <!-- Tracking section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">ข้อมูลการติดตาม</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">หมายเลขติดตาม</label>
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0 me-2"><?= htmlspecialchars($shipment['tracking_number']) ?></h5>
                                <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('<?= htmlspecialchars($shipment['tracking_number']) ?>')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">สถานะ</label>
                                    <div>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        $statusText = 'กำลังดำเนินการ';
                                        
                                        if (isset($shipment['status'])) {
                                            switch ($shipment['status']) {
                                                case 'received':
                                                    $statusClass = 'bg-secondary';
                                                    $statusText = 'รับเข้าระบบแล้ว';
                                                    break;
                                                case 'processing':
                                                    $statusClass = 'bg-secondary';
                                                    $statusText = 'กำลังดำเนินการ';
                                                    break;
                                                case 'in_transit':
                                                    $statusClass = 'bg-primary';
                                                    $statusText = 'กำลังขนส่ง';
                                                    break;
                                                case 'arrived_destination':
                                                    $statusClass = 'bg-info';
                                                    $statusText = 'ถึงปลายทางแล้ว';
                                                    break;
                                                case 'out_for_delivery':
                                                    $statusClass = 'bg-warning';
                                                    $statusText = 'กำลังนำส่ง';
                                                    break;
                                                case 'delivered':
                                                    $statusClass = 'bg-success';
                                                    $statusText = 'จัดส่งแล้ว';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'bg-danger';
                                                    $statusText = 'ยกเลิกแล้ว';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                                    $statusText = $shipment['status'];
                                            }
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">สร้างเมื่อ</label>
                                    <div>
                                        <?= date('d/m/Y H:i', strtotime($shipment['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">lot</label>
                                    <div>
                                        <span class="badge bg-info"><?= htmlspecialchars($lot['lot_number'] ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package details -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">รายละเอียดพัสดุ</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 150px;">น้ำหนัก</th>
                                <td>
                                    <?= number_format($shipment['weight'], 2) ?> กก.
                                </td>
                            </tr>
                            <tr>
                                <th>ขนาด</th>
                                <td>
                                    <?= number_format($shipment['length'], 2) ?> × 
                                    <?= number_format($shipment['width'], 2) ?> × 
                                    <?= number_format($shipment['height'], 2) ?> ซม.
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 150px;">น้ำหนักตามขนาด</th>
                                <td>
                                    <?= number_format($shipment['volumetric_weight'], 2) ?> กก.
                                </td>
                            </tr>
                            <tr>
                                <th>น้ำหนักที่คิดค่าบริการ</th>
                                <td class="text-primary">
                                    <?= number_format($shipment['chargeable_weight'], 2) ?> กก.
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sender/Receiver Information -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">ข้อมูลผู้ส่ง</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 150px;">รหัสลูกค้า</th>
                                <td><?= htmlspecialchars($shipment['customer_code'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>ชื่อผู้ส่ง</th>
                                <td><?= htmlspecialchars($shipment['sender_name']) ?></td>
                            </tr>
                            <tr>
                                <th>ติดต่อผู้ส่ง</th>
                                <td><?= htmlspecialchars($shipment['sender_contact']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">ข้อมูลผู้รับ</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 150px;">ชื่อผู้รับ</th>
                                <td><?= htmlspecialchars($shipment['receiver_name']) ?></td>
                            </tr>
                            <tr>
                                <th>ติดต่อผู้รับ</th>
                                <td><?= htmlspecialchars($shipment['receiver_contact']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Domestic Shipping Information -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">ข้อมูลการขนส่งภายในประเทศ</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">บริษัทขนส่งภายในประเทศ</th>
                        <td><?= htmlspecialchars($shipment['domestic_carrier'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>หมายเลขติดตามภายในประเทศ</th>
                        <td>
                            <?php if (!empty($shipment['domestic_tracking_number'])): ?>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?= htmlspecialchars($shipment['domestic_tracking_number']) ?></span>
                                    <button class="btn btn-outline-secondary btn-sm me-2" onclick="copyToClipboard('<?= htmlspecialchars($shipment['domestic_tracking_number']) ?>')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                    <a href="#" class="btn btn-primary btn-sm" onclick="openTrackingLink('<?= htmlspecialchars($shipment['domestic_carrier']) ?>', '<?= htmlspecialchars($shipment['domestic_tracking_number']) ?>')">
                                        ติดตาม
                                    </a>
                                </div>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>วันที่ส่งมอบ</th>
                        <td><?= !empty($shipment['handover_date']) ? date('d/m/Y', strtotime($shipment['handover_date'])) : '-' ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success toast
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '5';
        toast.innerHTML = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>
                        คัดลอกข้อมูลแล้ว
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        const toastEl = new bootstrap.Toast(toast.querySelector('.toast'));
        toastEl.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', function () {
            document.body.removeChild(toast);
        });
    });
}

function openTrackingLink(carrier, trackingNumber) {
    let trackingUrl = '';
    switch(carrier.toLowerCase()) {
        case 'flash':
        case 'flash express':
        case 'แฟลช':
            trackingUrl = 'https://www.flashexpress.co.th/tracking/?se=' + trackingNumber;
            break;
        case 'kerry':
        case 'kerry express':
            trackingUrl = 'https://th.kerryexpress.com/th/track/?track=' + trackingNumber;
            break;
        case 'thailand post':
        case 'ไปรษณีย์ไทย':
            trackingUrl = 'https://track.thailandpost.co.th/?trackNumber=' + trackingNumber;
            break;
        default:
            trackingUrl = 'https://www.google.com/search?q=' + encodeURIComponent(carrier + ' ' + trackingNumber);
    }
    window.open(trackingUrl, '_blank');
}

function confirmDelete(id) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบพัสดุนี้?')) {
        window.location.href = 'index.php?page=shipments&action=delete&id=' + encodeURIComponent(id);
    }
}
</script>

<?php require_once 'views/layout/footer.php'; ?>

