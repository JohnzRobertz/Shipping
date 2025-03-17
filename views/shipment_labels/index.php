<?php
// Include header
include 'views/layout/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4">พิมพ์ Tag พัสดุ</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
        <li class="breadcrumb-item active">พิมพ์ Tag พัสดุ</li>
    </ol>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table mr-1"></i>
            รายการพัสดุสถานะ "receive" เรียงตามวันที่สร้างล่าสุด
        </div>
        <div class="card-body">
            <form action="index.php?page=shipment_labels&action=printMultiple" method="post" id="print-form">
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-print"></i> พิมพ์ Tag ที่เลือก
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th>Tracking Number</th>
                                <th>ผู้ส่ง</th>
                                <th>ผู้รับ</th>
                                <th>น้ำหนัก (kg)</th>
                                <th>วันที่สร้าง</th>
                                <th width="15%">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($shipments) && is_array($shipments) && count($shipments) > 0): ?>
                                <?php foreach($shipments as $shipment): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="shipment_ids[]" value="<?= $shipment['id']; ?>" class="shipment-checkbox">
                                        </td>
                                        <td><?= $shipment['tracking_number']; ?></td>
                                        <td><?= $shipment['sender_name']; ?></td>
                                        <td><?= $shipment['receiver_name']; ?></td>
                                        <td><?= $shipment['weight']; ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($shipment['created_at'])); ?></td>
                                        <td>
                                            <a href="index.php?page=shipment_labels&action=printLabel&id=<?= $shipment['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                <i class="fas fa-print"></i> พิมพ์ Tag
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">ไม่พบข้อมูลพัสดุสถานะ "receive"</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // เลือกทั้งหมด
    document.getElementById('select-all').addEventListener('change', function() {
        var checkboxes = document.getElementsByClassName('shipment-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });
    
    // ตรวจสอบก่อนส่งฟอร์ม
    document.getElementById('print-form').addEventListener('submit', function(e) {
        var checkboxes = document.getElementsByClassName('shipment-checkbox');
        var checked = false;
        
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                checked = true;
                break;
            }
        }
        
        if (!checked) {
            e.preventDefault();
            alert('กรุณาเลือกพัสดุอย่างน้อย 1 รายการ');
        }
    });
    
    // Initialize DataTable
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[5, 'desc']], // เรียงตามวันที่สร้างล่าสุด (คอลัมน์ที่ 5)
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                "zeroRecords": "ไม่พบข้อมูล",
                "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
                "infoEmpty": "ไม่มีข้อมูล",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "search": "ค้นหา:",
                "paginate": {
                    "first": "หน้าแรก",
                    "last": "หน้าสุดท้าย",
                    "next": "ถัดไป",
                    "previous": "ก่อนหน้า"
                }
            }
        });
    });
</script>

<?php
// Include footer
include 'views/layout/footer.php';
?>

