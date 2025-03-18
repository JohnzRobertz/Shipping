<?php
// Ensure we have shipments data
if (!isset($shipments) || empty($shipments)) {
    echo "ไม่พบข้อมูลพัสดุ";
    exit;
}

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์ฉลากพัสดุหลายรายการ</title>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }
        
        body {
            font-family: 'Sarabun', 'Prompt', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .label-container {
            width: 100mm;
            height: 150mm;
            padding: 3mm;
            box-sizing: border-box;
            position: relative;
            page-break-after: always;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
        }
        
        .company-info {
            font-size: 8pt;
            margin: 1mm 0;
        }
        
        .tracking-section {
            text-align: center;
            margin: 3mm 0;
        }
        
        .tracking-number {
            font-size: 14pt;
            font-weight: bold;
            margin: 2mm 0;
        }
        
        .barcode {
            margin: 2mm auto;
            text-align: center;
        }
        
        .barcode img {
            max-width: 90mm;
            height: 15mm;
        }
        
        .qrcode {
            position: absolute;
            top: 35mm;
            right: 5mm;
            width: 25mm;
            height: 25mm;
            display: block;
            overflow: visible;
            z-index: 10;
        }
        
        .qrcode img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }
        
        .info-section {
            margin-top: 2mm;
            font-size: 9pt;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 1mm;
        }
        
        .info-label {
            font-weight: bold;
            width: 25mm;
        }
        
        .info-value {
            flex: 1;
        }
        
        .address-section {
            margin-top: 3mm;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 10pt;
            border-bottom: 1px dashed #000;
            margin-bottom: 1mm;
            padding-bottom: 1mm;
        }
        
        .address-content {
            font-size: 10pt;
            line-height: 1.3;
        }
        
        .footer {
            position: absolute;
            bottom: 3mm;
            left: 3mm;
            right: 3mm;
            text-align: center;
            font-size: 8pt;
            border-top: 1px solid #000;
            padding-top: 2mm;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }
        
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 9999;
        }
        
        .print-button:hover {
            background-color: #0052a3;
        }
        
        .controls {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="controls no-print">
        <button class="print-button" onclick="window.history.back();">กลับไปหน้าก่อนหน้า</button>
        <p>จำนวนฉลากทั้งหมด: <?php echo count($shipments); ?> รายการ</p>
    </div>
    
    <?php foreach ($shipments as $shipment): ?>
    <div class="label-container">
        <!-- Header with Company Info -->
        <div class="header">
            <p class="company-name">DKC LOGISTICS</p>
            <!-- <p class="company-info">บริษัท ดีเคซี โลจิสติกส์ จำกัด</p>
            <p class="company-info">โทร: 02-XXX-XXXX | Line: @dkclogistics</p> -->
        </div>
        
        <!-- Tracking Number and Barcode -->
        <div class="tracking-section">
            <div class="tracking-number"><?php echo htmlspecialchars($shipment['tracking_number']); ?></div>
            <div class="barcode">
                <img src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo urlencode($shipment['tracking_number']); ?>&code=Code128&multiplebarcodes=false&translate-esc=false&unit=Fit&dpi=96&imagetype=Gif&rotation=0&color=%23000000&bgcolor=%23ffffff&codepage=Default&qunit=Mm&quiet=0" alt="Barcode">
            </div>
        </div>
        
        <!-- QR Code for tracking -->
        <!-- <div class="qrcode">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($shipment['tracking_number']); ?>" alt="QR Code">
        </div>
         -->
        <!-- Package Information -->
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">น้ำหนัก:</div>
                <div class="info-value"><?php echo htmlspecialchars($shipment['weight']); ?> kg</div>
            </div>
            <div class="info-row">
                <div class="info-label">ขนาด:</div>
                <div class="info-value"><?php echo htmlspecialchars($shipment['length']); ?> x <?php echo htmlspecialchars($shipment['width']); ?> x <?php echo htmlspecialchars($shipment['height']); ?> cm</div>
            </div>
            <?php if (!empty($shipment['lot_number'])): ?>
            <div class="info-row">
                <div class="info-label">ล็อต:</div>
                <div class="info-value"><?php echo htmlspecialchars($shipment['lot_number']); ?></div>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-label">วันที่:</div>
                <div class="info-value"><?php echo date('d/m/Y', strtotime($shipment['created_at'])); ?></div>
            </div>
        </div>
        
        <!-- Sender Information -->
        <div class="address-section">
            <div class="section-title">ผู้ส่ง</div>
            <div class="address-content">
                <strong><?php echo htmlspecialchars($shipment['sender_name']); ?></strong><br>
                <?php echo htmlspecialchars($shipment['sender_contact']); ?><br>
                <?php if (!empty($shipment['sender_phone'])): ?>
                โทร: <?php echo htmlspecialchars($shipment['sender_phone']); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Receiver Information -->
        <div class="address-section">
            <div class="section-title">ผู้รับ</div>
            <div class="address-content">
                <strong><?php echo htmlspecialchars($shipment['receiver_name']); ?></strong><br>
                <?php echo htmlspecialchars($shipment['receiver_contact']); ?><br>
                <?php if (!empty($shipment['receiver_phone'])): ?>
                โทร: <?php echo htmlspecialchars($shipment['receiver_phone']); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            ขอบคุณที่ใช้บริการของเรา
        </div>
    </div>
    <?php endforeach; ?>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500); // เพิ่มดีเลย์เล็กน้อยเพื่อให้แน่ใจว่าหน้าโหลดเสร็จสมบูรณ์
        };
    </script>
    <script>
        // ตรวจสอบการโหลดรูปภาพ QR Code
        document.addEventListener('DOMContentLoaded', function() {
            var qrImages = document.querySelectorAll('.qrcode img');
            qrImages.forEach(function(qrImage) {
                qrImage.onerror = function() {
                    console.error('ไม่สามารถโหลด QR Code ได้');
                    // สร้าง QR Code แบบ fallback ด้วย text
                    var qrDiv = this.parentNode;
                    qrDiv.innerHTML = '<div style="width:100%;height:100%;background:#fff;border:1px solid #000;display:flex;align-items:center;justify-content:center;text-align:center;font-size:8pt;">SCAN<br>TO<br>TRACK</div>';
                };
            });
        });
    </script>
</body>
</html>

