<?php
// ใช้ output buffering เพื่อป้องกันการส่งข้อมูลโดยไม่ตั้งใจ
ob_start();

// แสดง error ทั้งหมด (แต่เก็บไว้ใน buffer)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// โหลด Composer autoload ก่อนทุกอย่าง
$autoloadPaths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    'vendor/autoload.php'
];

$autoloadLoaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require $path;
        $autoloadLoaded = true;
        break;
    }
}

if (!$autoloadLoaded) {
    die('ไม่พบไฟล์ vendor/autoload.php กรุณาตรวจสอบการติดตั้ง Composer');
}

require_once 'config/config.php';
require_once 'helpers/functions.php';
require_once 'helpers/language.php';
require_once 'models/Invoice.php';
require_once 'models/Customer.php';
require_once 'models/Shipment.php';

// ตรวจสอบว่ามีการส่ง ID มาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?page=invoice');
    exit;
}

$id = $_GET['id'];
$invoiceModel = new Invoice();
$invoice = $invoiceModel->getInvoiceById($id);

if (!$invoice) {
    die('ไม่พบใบแจ้งหนี้ที่ต้องการ');
}

// ดึงข้อมูลลูกค้า
$customerModel = new Customer();
$customer = $customerModel->getCustomerById($invoice['customer_id']);

if (!$customer) {
    die('ไม่พบข้อมูลลูกค้า');
}

// ดึงข้อมูลการขนส่งที่เกี่ยวข้อง
$shipments = $invoiceModel->getShipmentsByInvoiceId($id);

// กำหนดภาษา (th หรือ en)
$language = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'th';

// ข้อความในแต่ละภาษา
$texts = [
    'en' => [
        'invoice' => 'INVOICE',
        'company_name' => 'Shipping Express Co., Ltd.',
        'company_address' => '123 Sukhumvit Road',
        'company_city_country' => 'Bangkok 10110, Thailand',
        'company_phone' => 'Tel: 02-123-4567',
        'company_email' => 'Email: info@shipping-express.com',
        'tax_id' => 'Tax ID: 0123456789012',
        'invoice_no' => 'Invoice No:',
        'date' => 'Date:',
        'due_date' => 'Due Date:',
        'status' => 'Status:',
        'paid' => 'PAID',
        'unpaid' => 'UNPAID',
        'payment_terms' => 'Payment Terms: Net 30 days',
        'bill_to' => 'BILL TO:',
        'phone' => 'Phone:',
        'email' => 'Email:',
        'payment_info' => 'PAYMENT INFORMATION:',
        'bank' => 'Bank: Bangkok Bank',
        'account_name' => 'Account Name: Shipping Express Co., Ltd.',
        'account_number' => 'Account Number: 123-4-56789-0',
        'reference' => 'Reference:',
        'tracking_number' => 'Tracking Number',
        'description' => 'Description',
        'weight' => 'Weight (kg)',
        'amount' => 'Amount (THB)',
        'shipping_service' => 'Shipping Service -',
        'standard' => 'Standard',
        'subtotal' => 'Subtotal:',
        'vat' => 'VAT', // แก้ไขจาก 'VAT (7%):' เป็น 'VAT'
        'total' => 'Total:',
        'notes' => 'Notes:',
        'authorized_signature' => 'Authorized Signature',
        'customer_signature' => 'Customer Signature',
        'received_date' => 'Received Date:',
        'thank_you' => 'Thank you for your business!',
        'auto_generated' => 'This invoice was generated automatically.',
        'details' => 'Details',
        'page' => 'Page',
        'continued' => 'continued',
    ],
    'th' => [
        'invoice' => 'ใบแจ้งหนี้',
        'company_name' => 'บริษัท ขนส่งทันใจ จำกัด',
        'company_address' => '123 ถนนสุขุมวิท',
        'company_city_country' => 'กรุงเทพฯ 10110 ประเทศไทย',
        'company_phone' => 'โทร: 02-123-4567',
        'company_email' => 'อีเมล: info@shipping-express.com',
        'tax_id' => 'เลขประจำตัวผู้เสียภาษี: 0123456789012',
        'invoice_no' => 'เลขที่ใบแจ้งหนี้:',
        'date' => 'วันที่:',
        'due_date' => 'วันครบกำหนด:',
        'status' => 'สถานะ:',
        'paid' => 'ชำระแล้ว',
        'unpaid' => 'ยังไม่ชำระ',
        'payment_terms' => 'เงื่อนไขการชำระเงิน: ชำระภายใน 30 วัน',
        'bill_to' => 'เรียกเก็บเงินไปที่:',
        'phone' => 'โทรศัพท์:',
        'email' => 'อีเมล:',
        'payment_info' => 'ข้อมูลการชำระเงิน:',
        'bank' => 'ธนาคาร: ธนาคารกรุงเทพ',
        'account_name' => 'ชื่อบัญชี: บริษัท ขนส่งทันใจ จำกัด',
        'account_number' => 'เลขที่บัญชี: 123-4-56789-0',
        'reference' => 'อ้างอิง:',
        'tracking_number' => 'หมายเลขติดตาม',
        'description' => 'รายละเอียด',
        'weight' => 'น้ำหนัก (กก.)',
        'amount' => 'จำนวนเงิน (บาท)',
        'shipping_service' => 'บริการขนส่ง -',
        'standard' => 'มาตรฐาน',
        'subtotal' => 'ยอดรวมก่อนภาษี:',
        'vat' => 'ภาษีมูลค่าเพิ่ม', // แก้ไขจาก 'ภาษีมูลค่าเพิ่ม (7%):' เป็น 'ภาษีมูลค่าเพิ่ม'
        'total' => 'ยอดรวมทั้งสิ้น:',
        'notes' => 'หมายเหตุ:',
        'authorized_signature' => 'ลายเซ็นผู้มีอำนาจ',
        'customer_signature' => 'ลายเซ็นลูกค้า',
        'received_date' => 'วันที่รับ:',
        'thank_you' => 'ขอบคุณที่ใช้บริการของเรา',
        'auto_generated' => 'ใบแจ้งหนี้นี้ถูกสร้างขึ้นโดยอัตโนมัติ',
        'details' => 'รายละเอียด',
        'page' => 'หน้า',
        'continued' => 'ต่อ',
    ]
];

// ล้าง output buffer ก่อนที่จะสร้าง PDF
ob_clean();

// ตรวจสอบว่ามี TCPDF ติดตั้งแล้ว
if (!class_exists('TCPDF')) {
    die('TCPDF ยังไม่ได้ถูกติดตั้ง กรุณาติดตั้งผ่าน Composer ด้วยคำสั่ง: composer require tecnickcom/tcpdf');
}

// สร้างคลาสใหม่ที่สืบทอดจาก TCPDF
class MYPDF extends TCPDF {
    // ข้อความสำหรับหัวกระดาษและท้ายกระดาษ
    protected $headerText = array();
    protected $invoiceNumber = '';
    protected $invoiceDate = '';
    
    // ตั้งค่าข้อความสำหรับหัวกระดาษ
    public function setHeaderText($text) {
        $this->headerText = $text;
    }
    
    // ตั้งค่าข้อมูลใบแจ้งหนี้
    public function setInvoiceInfo($number, $date) {
        $this->invoiceNumber = $number;
        $this->invoiceDate = $date;
    }
    
    // หัวกระดาษ
    public function Header() {
        if (empty($this->headerText)) {
            return;
        }
        
        // ชื่อบริษัท
        $this->SetFont('freeserif', 'B', 12);
        $this->Cell(0, 6, $this->headerText['company_name'], 0, 1, 'L');
        
        // ชื่อเอกสาร
        $this->SetFont('freeserif', 'B', 14);
        $this->Cell(0, 8, $this->headerText['invoice'], 0, 1, 'R');
        
        // ข้อมูลใบแจ้งหนี้
        $this->SetFont('freeserif', '', 9);
        $this->Cell(0, 5, $this->headerText['invoice_no'] . ' ' . $this->invoiceNumber, 0, 1, 'R');
        $this->Cell(0, 5, $this->headerText['date'] . ' ' . $this->invoiceDate, 0, 1, 'R');
        
        // เส้นคั่น
        $this->SetLineWidth(0.1);
        $this->Line(10, $this->GetY() + 2, $this->getPageWidth() - 10, $this->GetY() + 2);
        
        // เว้นบรรทัด
        $this->Ln(5);
    }
    
    // ท้ายกระดาษ
    public function Footer() {
        // ตำแหน่ง 15 มม. จากด้านล่าง
        $this->SetY(-15);
        // ฟอนต์
        $this->SetFont('freeserif', '', 8);
        // หมายเลขหน้า
        $this->Cell(0, 10, $this->headerText['page'] . ' ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C');
    }
}

// สร้าง PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// ตั้งค่าข้อความสำหรับหัวกระดาษ
$pdf->setHeaderText($texts[$language]);
$pdf->setInvoiceInfo($invoice['invoice_number'], date('d/m/Y', strtotime($invoice['invoice_date'])));

// ตั้งค่าเอกสาร
$pdf->SetCreator('Shipping System');
$pdf->SetAuthor('Shipping Express');
$pdf->SetTitle('Invoice #' . $invoice['invoice_number']);
$pdf->SetSubject('Invoice');
$pdf->SetKeywords('Invoice, Shipping, PDF');

// ตั้งค่าขอบกระดาษ
$pdf->SetMargins(10, 40, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// ตั้งค่าการตัดหน้าอัตโนมัติ
$pdf->SetAutoPageBreak(TRUE, 15);

// ตั้งค่าฟอนต์
if ($language == 'th') {
    // ตรวจสอบว่ามีฟอนต์ภาษาไทยหรือไม่
    $fontPath = __DIR__ . '/assets/fonts/THSarabunNew.ttf';
    if (!file_exists($fontPath)) {
        die('ไม่พบไฟล์ฟอนต์: ' . $fontPath);
    }
    
    // ใช้ฟอนต์ที่มีอยู่แล้วใน TCPDF
    $pdf->SetFont('freeserif', '', 10);
} else {
    $pdf->SetFont('helvetica', '', 9);
}

// เพิ่มหน้า
$pdf->AddPage();

// ข้อมูลบริษัทและลูกค้า
$pdf->SetFont('freeserif', '', 9);

// ข้อมูลบริษัท
$pdf->Cell(95, 5, $texts[$language]['company_address'], 0, 0);
$pdf->Cell(95, 5, $texts[$language]['status'] . ': ' . 
    ($invoice['status'] == 'paid' ? $texts[$language]['paid'] : $texts[$language]['unpaid']), 0, 1, 'R');

$pdf->Cell(95, 5, $texts[$language]['company_city_country'], 0, 0);
$pdf->Cell(95, 5, $texts[$language]['due_date'] . ': ' . date('d/m/Y', strtotime($invoice['due_date'])), 0, 1, 'R');

$pdf->Cell(95, 5, $texts[$language]['company_phone'], 0, 0);
$pdf->Cell(95, 5, $texts[$language]['payment_terms'], 0, 1, 'R');

$pdf->Cell(95, 5, $texts[$language]['company_email'], 0, 1);
$pdf->Cell(95, 5, $texts[$language]['tax_id'], 0, 1);

$pdf->Ln(2);

// ข้อมูลลูกค้าและการชำระเงิน
$pdf->SetFont('freeserif', 'B', 10);
$pdf->Cell(95, 5, $texts[$language]['bill_to'], 0, 0);
$pdf->Cell(95, 5, $texts[$language]['payment_info'], 0, 1, 'R');

// แยกที่อยู่ลูกค้าเป็นบรรทัด
$addressLines = explode("\n", $customer['address']);
// เพิ่มข้อมูลลูกค้าเข้าไปในอาร์เรย์
array_unshift($addressLines, $customer['name']);
if (!empty($customer['tax_id'])) {
    $addressLines[] = $texts[$language]['tax_id'] . ' ' . $customer['tax_id'];
}
$addressLines[] = $texts[$language]['phone'] . ' ' . $customer['phone'];
$addressLines[] = $texts[$language]['email'] . ' ' . $customer['email'];

// ข้อมูลการชำระเงิน
$paymentInfo = [
    $texts[$language]['bank'],
    $texts[$language]['account_name'],
    $texts[$language]['account_number'],
    $texts[$language]['reference'] . ' ' . $invoice['invoice_number']
];

// จำนวนบรรทัดสูงสุดระหว่างที่อยู่ลูกค้าและข้อมูลการชำระเงิน
$maxLines = max(count($addressLines), count($paymentInfo));

$pdf->SetFont('freeserif', '', 9);
for ($i = 0; $i < $maxLines; $i++) {
    $addressLine = isset($addressLines[$i]) ? $addressLines[$i] : '';
    $paymentLine = isset($paymentInfo[$i]) ? $paymentInfo[$i] : '';
    
    $pdf->Cell(95, 5, $addressLine, 0, 0);
    $pdf->Cell(95, 5, $paymentLine, 0, 1, 'R');
}

$pdf->Ln(2);

// รายการสินค้า
$pdf->SetFont('freeserif', 'B', 10);
$pdf->Cell(0, 6, $texts[$language]['invoice'] . ' ' . $texts[$language]['details'], 0, 1);

$pdf->SetFillColor(242, 242, 242);
$pdf->SetFont('freeserif', 'B', 9);

// หัวตาราง
$pdf->Cell(50, 7, $texts[$language]['tracking_number'], 1, 0, 'L', true);
$pdf->Cell(80, 7, $texts[$language]['description'], 1, 0, 'L', true);
$pdf->Cell(25, 7, $texts[$language]['weight'], 1, 0, 'C', true);
$pdf->Cell(25, 7, $texts[$language]['amount'], 1, 1, 'R', true);

// รายการสินค้า
$pdf->SetFont('freeserif', '', 9);
$fill = false;
foreach ($shipments as $shipment) {
    // ตรวจสอบว่าต้องขึ้นหน้าใหม่หรือไม่
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
        
        // พิมพ์หัวตารางใหม่
        $pdf->SetFont('freeserif', 'B', 10);
        $pdf->Cell(0, 6, $texts[$language]['invoice'] . ' ' . $texts[$language]['details'] . ' (' . $texts[$language]['continued'] . ')', 0, 1);
        
        $pdf->SetFillColor(242, 242, 242);
        $pdf->SetFont('freeserif', 'B', 9);
        
        $pdf->Cell(50, 7, $texts[$language]['tracking_number'], 1, 0, 'L', true);
        $pdf->Cell(80, 7, $texts[$language]['description'], 1, 0, 'L', true);
        $pdf->Cell(25, 7, $texts[$language]['weight'], 1, 0, 'C', true);
        $pdf->Cell(25, 7, $texts[$language]['amount'], 1, 1, 'R', true);
        
        $pdf->SetFont('freeserif', '', 9);
    }
    
    $pdf->Cell(50, 6, $shipment['tracking_number'], 1, 0, 'L', $fill);
    $pdf->Cell(80, 6, $texts[$language]['shipping_service'] . ' ' . ($shipment['transport_type'] ?? $texts[$language]['standard']), 1, 0, 'L', $fill);
    $pdf->Cell(25, 6, number_format($shipment['weight'], 2), 1, 0, 'C', $fill);
    $pdf->Cell(25, 6, number_format($shipment['total_price'], 2), 1, 1, 'R', $fill);
    
    $fill = !$fill; // สลับสีพื้นหลัง
}

// ดึงข้อมูลค่าใช้จ่ายเพิ่มเติม
require_once 'models/InvoiceCharge.php';
$invoiceChargeModel = new InvoiceCharge();
$additionalCharges = $invoiceChargeModel->getChargesByInvoiceId($id);

// สรุปยอด
$pdf->SetFont('freeserif', 'B', 9);

// คำนวณ subtotal จากยอดรวมของ shipments
$subtotal = 0;
foreach ($shipments as $shipment) {
    $subtotal += $shipment['total_price'];
}

// ถ้ามี subtotal ในข้อมูล invoice ให้ใช้ค่านั้น
if (isset($invoice['subtotal']) && $invoice['subtotal'] > 0) {
    $subtotal = $invoice['subtotal'];
}

$pdf->Cell(155, 6, $texts[$language]['subtotal'], 1, 0, 'R');
$pdf->Cell(25, 6, number_format($subtotal, 2), 1, 1, 'R');

// แสดงค่าใช้จ่ายเพิ่มเติม
if (!empty($additionalCharges)) {
    foreach ($additionalCharges as $charge) {
        $chargeAmount = $charge['is_percentage'] == 1 
            ? $subtotal * ($charge['amount'] / 100) 
            : $charge['amount'];
        
        $chargeDescription = $charge['description'];
        if ($charge['is_percentage'] == 1) {
            $chargeDescription .= ' (' . number_format($charge['amount'], 2) . '%)';
        }
        
        if ($charge['charge_type'] == 'discount') {
            $pdf->Cell(155, 6, $chargeDescription, 1, 0, 'R');
            $pdf->Cell(25, 6, '-' . number_format(abs($chargeAmount), 2), 1, 1, 'R');
        } else {
            $pdf->Cell(155, 6, $chargeDescription, 1, 0, 'R');
            $pdf->Cell(25, 6, number_format($chargeAmount, 2), 1, 1, 'R');
        }
    }
}

// แสดงภาษี
// ใช้ข้อมูลจากฐานข้อมูลถ้ามี
if (isset($invoice['tax_rate'])) {
    // ใช้ข้อมูลจากฐานข้อมูล
    $taxRate = $invoice['tax_rate'];
    
    // แสดงภาษีเฉพาะเมื่อ tax_rate > 0
    if ($taxRate > 0) {
        $taxAmount = isset($invoice['tax_amount']) && $invoice['tax_amount'] > 0 
            ? $invoice['tax_amount'] 
            : $subtotal * $taxRate;
        
        $taxDescription = $texts[$language]['vat'] . ' (' . number_format($taxRate * 100, 0) . '%)';
        $pdf->Cell(155, 6, $taxDescription, 1, 0, 'R');
        $pdf->Cell(25, 6, number_format($taxAmount, 2), 1, 1, 'R');
    }
} else {
    // ถ้าไม่มีข้อมูล tax_rate ในฐานข้อมูล ให้ใช้ค่าเริ่มต้น 7% (กรณีเก่า)
    $taxRate = 0.07;
    $taxAmount = $subtotal * $taxRate;
    $taxDescription = $texts[$language]['vat'] . ' (7%)';
    $pdf->Cell(155, 6, $taxDescription, 1, 0, 'R');
    $pdf->Cell(25, 6, number_format($taxAmount, 2), 1, 1, 'R');
}

$pdf->SetFillColor(255, 243, 224); // สีส้มอ่อน
$pdf->Cell(155, 6, $texts[$language]['total'], 1, 0, 'R', true);
$pdf->Cell(25, 6, number_format($invoice['total_amount'], 2), 1, 1, 'R', true);

// หมายเหตุ
if (!empty($invoice['notes'])) {
    $pdf->Ln(3);
    $pdf->SetFont('freeserif', 'B', 10);
    $pdf->Cell(0, 6, $texts[$language]['notes'], 0, 1);
    
    $pdf->SetFont('freeserif', '', 9);
    $pdf->MultiCell(0, 5, $invoice['notes'], 1, 'L');
}

// ตรวจสอบว่ามีพื้นที่เพียงพอสำหรับลายเซ็นหรือไม่
if ($pdf->GetY() > 220) {
    $pdf->AddPage();
}

// ลายเซ็น
$pdf->Ln(10);
$pdf->SetFont('freeserif', '', 9);

// กำหนดพาธของรูปลายเซ็น (เปลี่ยนจาก .png เป็น .jpg)
$signatureImagePath = __DIR__ . '/assets/images/signature_dkc.jpg'; // เปลี่ยนเป็นพาธที่ถูกต้อง
$customerSignatureImagePath = ''; // ถ้ามีรูปลายเซ็นของลูกค้า

// ความสูงของลายเซ็น
$signatureHeight = 15;

// ตำแหน่งเริ่มต้นของลายเซ็น
$startY = $pdf->GetY();

// ลายเซ็นผู้มีอำนาจ (ด้านซ้าย)
$pdf->Cell(90, 5, '', 0, 0);
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(90, 5, '', 0, 1);

// ตรวจสอบว่ามีไฟล์รูปลายเซ็นหรือไม่
if (file_exists($signatureImagePath)) {
    try {
        // คำนวณความกว้างของรูปลายเซ็นให้พอดีกับพื้นที่ (ไม่เกิน 80)
        $signatureWidth = 40; // ปรับตามความเหมาะสม
        
        // ตำแหน่ง X ของรูปลายเซ็น (กึ่งกลางของพื้นที่ 90)
        $signatureX = 10 + (90 - $signatureWidth) / 2;
        
        // แสดงรูปลายเซ็น - ระบุชนิดไฟล์เป็น JPEG
        $pdf->Image($signatureImagePath, $signatureX, $startY, $signatureWidth, 0, 'JPEG');
        
        // เลื่อนตำแหน่ง Y ให้พ้นรูปลายเซ็น
        $pdf->SetY($startY + $signatureHeight);
    } catch (Exception $e) {
        // ถ้าเกิด error ให้แสดงเส้นสำหรับเซ็นแทน
        $pdf->Cell(90, 0, '', 'T', 0);
        $pdf->Cell(10, 0, '', 0, 0);
        $pdf->Cell(90, 0, '', 'T', 1);
    }
} else {
    // ถ้าไม่พบไฟล์รูปลายเซ็น ให้แสดงเส้นสำหรับเซ็น
    $pdf->Cell(90, 0, '', 'T', 0);
    $pdf->Cell(10, 0, '', 0, 0);
    $pdf->Cell(90, 0, '', 'T', 1);
}

// ข้อความใต้ลายเซ็น
$pdf->Cell(90, 5, $texts[$language]['authorized_signature'], 0, 0, 'C');
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(90, 5, $texts[$language]['customer_signature'], 0, 1, 'C');

$pdf->Cell(90, 5, $texts[$language]['company_name'], 0, 0, 'C');
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(90, 5, $customer['name'], 0, 1, 'C');

$pdf->Cell(90, 5, date('d/m/Y'), 0, 0, 'C');
$pdf->Cell(10, 5, '', 0, 0);
$pdf->Cell(90, 5, $texts[$language]['received_date'] . ' _______________', 0, 1, 'C');

// ข้อความท้ายเอกสาร
$pdf->Ln(5);
$pdf->SetFont('freeserif', '', 8);
$pdf->Cell(0, 5, $texts[$language]['thank_you'], 0, 1, 'C');
$pdf->Cell(0, 5, $texts[$language]['auto_generated'], 0, 1, 'C');

// ปิด PDF และส่งไปยังเบราว์เซอร์
$pdf->Output('invoice-' . $invoice['invoice_number'] . '.pdf', 'I');
exit;
?>

