<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์ Tag พัสดุ - <?= $shipment['tracking_number']; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 20px;
        }
        .shipping-label {
            width: 100%;
            max-width: 400px;
            border: 1px solid #000;
            padding: 10px;
            margin: 0 auto 20px;
            page-break-inside: avoid;
        }
        .label-header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .company-logo {
            font-size: 24px;
            font-weight: bold;
        }
        .tracking-number {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        .barcode {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border: 1px dashed #ccc;
            background-color: #f9f9f9;
        }
        .barcode img {
            max-width: 100%;
            height: auto;
        }
        .section {
            margin-bottom: 10px;
        }
        .section-title {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 3px;
            margin-bottom: 5px;
        }
        .section-content {
            padding-left: 5px;
        }
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .shipping-label {
                page-break-after: always;
                border: 1px solid #000;
                margin: 0;
                padding: 10px;
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print();" class="btn btn-primary">
            <i class="fas fa-print"></i> พิมพ์ Tag
        </button>
        <button onclick="window.close();" class="btn btn-secondary">
            ปิดหน้านี้
        </button>
    </div>
    
    <div class="shipping-label">
        <div class="label-header">
            <div class="company-logo">DKC SHIPPING</div>
            <div class="tracking-number"><?= $shipment['tracking_number']; ?></div>
        </div>
        
        <div class="barcode">
            <!-- ในที่นี้ใช้ตัวอักษรแทนบาร์โค้ด แต่ในการใช้งานจริงควรใช้ไลบรารีสร้างบาร์โค้ด -->
            <div style="font-family: monospace; font-size: 14px; letter-spacing: 2px;">
                *<?= $shipment['tracking_number']; ?>*
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">ผู้ส่ง:</div>
            <div class="section-content">
                <strong><?= $shipment['sender_name']; ?></strong><br>
                <?= $shipment['sender_contact']; ?><br>
                โทร: <?= $shipment['sender_phone']; ?>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">ผู้รับ:</div>
            <div class="section-content">
                <strong><?= $shipment['receiver_name']; ?></strong><br>
                <?= $shipment['receiver_contact']; ?><br>
                โทร: <?= $shipment['receiver_phone']; ?>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">รายละเอียดพัสดุ:</div>
            <div class="section-content">
                <table style="width: 100%;">
                    <tr>
                        <td>น้ำหนัก:</td>
                        <td><?= $shipment['weight']; ?> kg</td>
                    </tr>
                    <tr>
                        <td>ขนาด:</td>
                        <td><?= $shipment['length']; ?> x <?= $shipment['width']; ?> x <?= $shipment['height']; ?> cm</td>
                    </tr>
                    <tr>
                        <td>วันที่:</td>
                        <td><?= date('d/m/Y', strtotime($shipment['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if (!empty($shipment['description'])): ?>
        <div class="section">
            <div class="section-title">หมายเหตุ:</div>
            <div class="section-content">
                <?= $shipment['description']; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>

