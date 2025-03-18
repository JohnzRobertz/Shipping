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

// ดึงข้อมูล shipments พร้อมข้อมูล lot
global $db;
$sql = "SELECT s.*, l.origin, l.destination, l.lot_number, l.lot_type 
        FROM shipments s 
        LEFT JOIN invoice_shipments is_rel ON s.id = is_rel.shipment_id 
        LEFT JOIN lots l ON s.lot_id = l.id 
        WHERE is_rel.invoice_id = :invoice_id";

$stmt = $db->prepare($sql);
$stmt->bindParam(':invoice_id', $id);
$stmt->execute();
$shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ถ้าไม่มีข้อมูล origin และ destination ให้ลองดึงจากตาราง shipments โดยตรง
if (!empty($shipments)) {
    foreach ($shipments as &$shipment) {
        // ถ้า origin หรือ destination เป็น NULL ให้ตรวจสอบว่ามีในตาราง shipments หรือไม่
        if (empty($shipment['origin']) && isset($shipment['origin_address'])) {
            $shipment['origin'] = $shipment['origin_address'];
        }
        
        if (empty($shipment['destination']) && isset($shipment['destination_address'])) {
            $shipment['destination'] = $shipment['destination_address'];
        }
    }
    unset($shipment); // ยกเลิกการอ้างอิง
}

// เพิ่มโค้ดนี้หลังจากดึงข้อมูล shipments เพื่อตรวจสอบข้อมูลที่ได้
if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    echo '<pre>';
    print_r($shipments);
    echo '</pre>';
    exit;
}

// ดึงข้อมูลค่าใช้จ่ายเพิ่มเติม
require_once 'models/InvoiceCharge.php';
$invoiceChargeModel = new InvoiceCharge();
$additionalCharges = $invoiceChargeModel->getChargesByInvoiceId($id);

// กำหนดภาษา (th หรือ en)
$language = isset($_GET['lang']) && $_GET['lang'] == 'en' ? 'en' : 'th';

// ข้อความในแต่ละภาษา
$texts = [
    'en' => [
        'invoice' => 'INVOICE',
        'company_name' => 'Shipping Express Co., Ltd.',
        'company_address' => '163/102 Moo 1, Pimonrat Sub-district Bang Bua Thong District',
        'company_city_country' => 'Nonthaburi 11110, Thailand',
        'company_phone' => 'Tel: 0852449919',
        'company_email' => 'Email:',
        'tax_id' => 'Tax ID: 0991028589508',
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
        'bank' => 'Bank: KASIKORNBANK(KBANK)',
        'account_name' => 'Account Name: Mr. DAE-UK CHOI',
        'account_number' => 'Account Number: 200-134-2358',
        'reference' => 'Reference:',
        'tracking_number' => 'Tracking No.',
        'description' => 'Description',
        'origin' => 'Origin',
        'destination' => 'Destination',
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
        'tax' => 'Tax',
        'kg' => 'kg',
        'currency' => 'THB',
    ],
    'th' => [
        'invoice' => 'ใบแจ้งหนี้',
        'company_name' => 'Mr. DAE-UK CHOI',
        'company_address' => ' 163/102  หมู่ที่ 1 ตําบลพิมลราช อําเภอบางบัวทอง',
        'company_city_country' => 'จังหวัดนนทบุรี 11110 ประเทศไทย',
        'company_phone' => 'โทร: 0852449919',
        'company_email' => 'อีเมล:',
        'tax_id' => 'เลขประจำตัวผู้เสียภาษี: 0991028589508',
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
        'bank' => 'ธนาคาร: ธนาคารกสิกรไทย',
        'account_name' => 'ชื่อบัญชี: บริษัท Mr. DAE-UK CHOI',
        'account_number' => 'เลขที่บัญชี: 200-134-2358',
        'reference' => 'อ้างอิง:',
        'tracking_number' => 'เลขติดตาม',
        'description' => 'รายละเอียด',
        'origin' => 'ต้นทาง',
        'destination' => 'ปลายทาง',
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
        'tax' => 'ภาษี',
        'kg' => 'กก.',
        'currency' => 'บาท',
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
$pdf->SetFont('freeserif', 'B', 8); // ลดขนาดฟอนต์ลงเพื่อให้ข้อความไม่ล้น

// ปรับขนาดคอลัมน์ให้เหมาะสม
$trackingWidth = 30;
$descWidth = 45;
$originWidth = 30;
$destWidth = 30;
$weightWidth = 20;
$amountWidth = 25;

// หัวตาราง
$pdf->Cell($trackingWidth, 7, $texts[$language]['tracking_number'], 1, 0, 'C', true);
$pdf->Cell($descWidth, 7, $texts[$language]['description'], 1, 0, 'C', true);
$pdf->Cell($originWidth, 7, $texts[$language]['origin'], 1, 0, 'C', true);
$pdf->Cell($destWidth, 7, $texts[$language]['destination'], 1, 0, 'C', true);
$pdf->Cell($weightWidth, 7, $texts[$language]['weight'], 1, 0, 'C', true);
$pdf->Cell($amountWidth, 7, $texts[$language]['amount'], 1, 1, 'C', true);

// รายการสินค้า
$pdf->SetFont('freeserif', '', 8); // ลดขนาดฟอนต์ลงเพื่อให้ข้อความไม่ล้น
$fill = false;
foreach ($shipments as $shipment) {
    // ตรวจสอบว่าต้องขึ้นหน้าใหม่หรือไม่
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
        
        // พิมพ์หัวตารางใหม่
        $pdf->SetFont('freeserif', 'B', 10);
        $pdf->Cell(0, 6, $texts[$language]['invoice'] . ' ' . $texts[$language]['details'] . ' (' . $texts[$language]['continued'] . ')', 0, 1);
        
        $pdf->SetFillColor(242, 242, 242);
        $pdf->SetFont('freeserif', 'B', 8);
        
        $pdf->Cell($trackingWidth, 7, $texts[$language]['tracking_number'], 1, 0, 'C', true);
        $pdf->Cell($descWidth, 7, $texts[$language]['description'], 1, 0, 'C', true);
        $pdf->Cell($originWidth, 7, $texts[$language]['origin'], 1, 0, 'C', true);
        $pdf->Cell($destWidth, 7, $texts[$language]['destination'], 1, 0, 'C', true);
        $pdf->Cell($weightWidth, 7, $texts[$language]['weight'], 1, 0, 'C', true);
        $pdf->Cell($amountWidth, 7, $texts[$language]['amount'], 1, 1, 'C', true);
        
        $pdf->SetFont('freeserif', '', 8);
    }
    
    // ตรวจสอบและกำหนดค่าต้นทางและปลายทาง
    $origin = !empty($shipment['origin']) ? $shipment['origin'] : 
             (!empty($shipment['origin_address']) ? $shipment['origin_address'] : 'N/A');
    
    $destination = !empty($shipment['destination']) ? $shipment['destination'] : 
                  (!empty($shipment['destination_address']) ? $shipment['destination_address'] : 'N/A');
    
    // ตัดข้อความให้พอดีกับความกว้างของคอลัมน์
    $trackingNumber = $pdf->getStringHeight($trackingWidth, $shipment['tracking_number']) > 6 
        ? substr($shipment['tracking_number'], 0, 15) . '...' 
        : $shipment['tracking_number'];
    
    $description = $texts[$language]['shipping_service'] . ' ' . ($shipment['transport_type'] ?? $texts[$language]['standard']);
    $description = $pdf->getStringHeight($descWidth, $description) > 6 
        ? substr($description, 0, 20) . '...' 
        : $description;
    
    $origin = $pdf->getStringHeight($originWidth, $origin) > 6 
        ? substr($origin, 0, 15) . '...' 
        : $origin;
    
    $destination = $pdf->getStringHeight($destWidth, $destination) > 6 
        ? substr($destination, 0, 15) . '...' 
        : $destination;
    
    $pdf->Cell($trackingWidth, 6, $trackingNumber, 1, 0, 'L', $fill);
    $pdf->Cell($descWidth, 6, $description, 1, 0, 'L', $fill);
    $pdf->Cell($originWidth, 6, $origin, 1, 0, 'L', $fill);
    $pdf->Cell($destWidth, 6, $destination, 1, 0, 'L', $fill);
    $pdf->Cell($weightWidth, 6, number_format($shipment['weight'], 2), 1, 0, 'R', $fill);
    $pdf->Cell($amountWidth, 6, number_format($shipment['total_price'], 2), 1, 1, 'R', $fill);
    
    $fill = !$fill; // สลับสีพื้นหลัง
}

// สรุปยอด
$pdf->SetFont('freeserif', 'B', 8);

// คำนวณ subtotal จากยอดรวมของ shipments
$subtotal = 0;
foreach ($shipments as $shipment) {
    $subtotal += $shipment['total_price'];
}

// ถ้ามี subtotal ในข้อมูล invoice ให้ใช้ค่านั้น
if (isset($invoice['subtotal']) && $invoice['subtotal'] > 0) {
    $subtotal = $invoice['subtotal'];
}

// ความกว้างรวมของคอลัมน์ทั้งหมด
$totalWidth = $trackingWidth + $descWidth + $originWidth + $destWidth + $weightWidth;

$pdf->Cell($totalWidth, 6, $texts[$language]['subtotal'], 1, 0, 'R');
$pdf->Cell($amountWidth, 6, number_format($subtotal, 2), 1, 1, 'R');

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
            $pdf->Cell($totalWidth, 6, $chargeDescription, 1, 0, 'R');
            $pdf->Cell($amountWidth, 6, '-' . number_format(abs($chargeAmount), 2), 1, 1, 'R');
        } else {
            $pdf->Cell($totalWidth, 6, $chargeDescription, 1, 0, 'R');
            $pdf->Cell($amountWidth, 6, number_format($chargeAmount, 2), 1, 1, 'R');
        }
    }
}

// แสดงภาษี
// ใช้ข้อมูลจากฐานข้อมูลถ้ามี
if (isset($invoice['tax_rate']) && $invoice['tax_rate'] > 0) {
    // ใช้ข้อมูลจากฐานข้อมูล
    $taxRate = $invoice['tax_rate'];
    
    $taxAmount = isset($invoice['tax_amount']) && $invoice['tax_amount'] > 0 
        ? $invoice['tax_amount'] 
        : $subtotal * $taxRate;
    
    $taxDescription = $texts[$language]['vat'] . ' (' . number_format($taxRate * 100, 0) . '%)';
    $pdf->Cell($totalWidth, 6, $taxDescription, 1, 0, 'R');
    $pdf->Cell($amountWidth, 6, number_format($taxAmount, 2), 1, 1, 'R');
}

$pdf->SetFillColor(255, 243, 224); // สีส้มอ่อน
$pdf->Cell($totalWidth, 6, $texts[$language]['total'], 1, 0, 'R', true);
$pdf->Cell($amountWidth, 6, number_format($invoice['total_amount'], 2), 1, 1, 'R', true);

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

// // ตรวจสอบว่ามีไฟล์รูปลายเซ็นหรือไม่
// if (file_exists($signatureImagePath)) {
//     try {
//         // คำนวณความกว้างของรูปลายเซ็นให้พอดีกับพื้นที่ (ไม่เกิน 80)
//         $signatureWidth = 40; // ปรับตามความเหมาะสม
        
//         // ตำแหน่ง X ของรูปลายเซ็น (กึ่งกลางของพื้นที่ 90)
//         $signatureX = 10 + (90 - $signatureWidth) / 2;
        
//         // แสดงรูปลายเซ็น - ระบุชนิดไฟล์เป็น JPEG
//         $pdf->Image($signatureImagePath, $signatureX, $startY, $signatureWidth, 0, 'JPEG');
        
//         // เลื่อนตำแหน่ง Y ให้พ้นรูปลายเซ็น
//         $pdf->SetY($startY + $signatureHeight);
//     } catch (Exception $e) {
//         // ถ้าเกิด error ให้แสดงเส้นสำหรับเซ็นแทน
//         $pdf->Cell(90, 0, '', 'T', 0);
//         $pdf->Cell(10, 0, '', 0, 0);
//         $pdf->Cell(90, 0, '', 'T', 1);
//     }
// } else {
//     // ถ้าไม่พบไฟล์รูปลายเซ็น ให้แสดงเส้นสำหรับเซ็น
//     $pdf->Cell(90, 0, '', 'T', 0);
//     $pdf->Cell(10, 0, '', 0, 0);
//     $pdf->Cell(90, 0, '', 'T', 1);
// }

// ฝังรูปภาพลายเซ็นเป็น Base64 (ตัวอย่าง)
$signatureBase64 = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAwADAAAD/4QKqRXhpZgAATU0AKgAAAAgACAESAAMAAAABAAEAAAMBAAUAAAABAAABegMDAAEAAAABAAAAAFEQAAEAAAABAQAAAFERAAQAAAABAAAdhlESAAQAAAABAAAdhodpAAQAAAABAAABguocAAcAAAEMAAAAbgAAAAAc6gAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGGoAAAsY8AAeocAAcAAAEMAAABlAAAAAAc6gAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/4QHdaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49J++7vycgaWQ9J1c1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCc/Pg0KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyI+PHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIi8+PC94OnhtcG1ldGE+DQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPD94cGFja2V0IGVuZD0ndyc/Pv/bAEMAAgEBAgEBAgICAgICAgIDBQMDAwMDBgQEAwUHBgcHBwYHBwgJCwkICAoIBwcKDQoKCwwMDAwHCQ4PDQwOCwwMDP/bAEMBAgICAwMDBgMDBgwIBwgMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDP/AABEIAKIBcgMBIgACEQEDEQH/xAAfAAABBQEBAQEBAQAAAAAAAAAAAQIDBAUGBwgJCgv/xAC1EAACAQMDAgQDBQUEBAAAAX0BAgMABBEFEiExQQYTUWEHInEUMoGRoQgjQrHBFVLR8CQzYnKCCQoWFxgZGiUmJygpKjQ1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4eLj5OXm5+jp6vHy8/T19vf4+fr/xAAfAQADAQEBAQEBAQEBAAAAAAAAAQIDBAUGBwgJCgv/xAC1EQACAQIEBAMEBwUEBAABAncAAQIDEQQFITEGEkFRB2FxEyIygQgUQpGhscEJIzNS8BVictEKFiQ04SXxFxgZGiYnKCkqNTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqCg4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2dri4+Tl5ufo6ery8/T19vf4+fr/2gAMAwEAAhEDEQA/AP38ooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKpQeJNOutdn0uO/s5NSto1mmtFmUzRIxwGZM5APYkV5v+1Z+09a/s5+FrGK00+48ReMfEs4sPD2hWrD7RqVwcZPP3Y0B3O3ZQa8O/Yy/YZ8Wfs8ftseMPiH4g16816f4g+H0bVJZWMkcF4JwwijY9ERPlAwBgV0QoJ03Obt28wPsasvxh420b4faFNqmvatp2i6bbjMt1fXKW8MY92YgCvNP2qv2vtH/ZtsbDTYLW48SeOPELiDRPDlhh7y9YnaZCuflhTqzngAV5b8S/2FNC+PXgWz8UftJ6u+vpoGdXudLivHtNEsgo3bZEUjzVQDHzHBI70U6Csp1HZP736ID6b8E+PdE+JXh6HVvD2r6drel3H+qurG4WeF/oykitavlj/glV8PrLw18NvGWvaDo8nhzwb4s8QzXnhzTSSFjskAjSUKc4Em0sOemK+p6ivTUKjhF3SAKKKKyAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACsH4n/ErRvg78P9W8T+ILxLHR9FtnurqZhnaqjOAByWPQAckkVuSyrBE0jsqIgLMzHAUDqTXyXpKSf8FI/jYNQuEaX4FeA70NYIcqni7VYWIMp/v2sJyAOjuM9BW1GmpPmlpFb/5LzYGr+xr8Pda+PPxFvPj54+sjb32rxNbeCNLmU7tC0duRIyn7s84wz9wMCvQ/2s/2qbf9nfQNP0/S7F/E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n4x/aBnutF+AXhdtVlhfZN4q16F7XQ4BnBMRxunYccKMV5X+0N/wSK8a/tKfCjxBceNfi94i8S+O7m2LaXax3D2OhWcw5CiFeSpxjJyRX3hp2m2+j2ENraQQ21tboEiiiQIkajoABwBU1EMW6b/cq34v7/8AKwHxZ4B/bi8f/slfDvw94c+LnwU8cQW2h2MOnvr3hlRrlo6xRhPNdUPmLkLnoTXtvwc/4KDfB346xoug+O9DW8frY38wsbxDkjDRS7WB46Yr2brXn3xZ/ZS+G3x0s5IPFvgnw5rqy8s9xZIZM+ocAMD9DSdSjPWUbPyf6P8AzA7uxv4NTtlmtp4biF/uvE4dT9COKkjlWVcoysMkZBzyK+Q7/wD4JUN8Jbie++BvxO8Z/C+5Ks0emvdNqmkFyO8ExbavThTxjivdv2S/hr4u+EnwI0fQ/Hev2nifxVbtNJf6lbQmGK5eSV3yF7cMBWdSnBLmhK/laz/y/ED0iiiisQCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACvHP2of2pG+EGoaR4T8MWC+I/iL4rfydK0tTlbZTwbu4xysCHqe+MZr2OvmD46/wDBPrxB8Qf2nLz4peD/AIqax4F13UNLi0iUQ6dDeCKFDk+X5nCbuM4H41vhlT5/3r0/Xz8gOr8BeCfC37I+ial41+InifTJ/GGtgS6vrd9Kse9u0FurcrEvAVF64FZKftK+Nv2ld1h8K/D11oulyHEvinX7VobdEPU28R5lfHTOB61f+G3/AAT58KaDqttrXjXUta+KHie3ww1LxHOZ1jb1jgH7pB+Br3mGFLeJUjVURBhVUYCj0ArSdWmnde9Lv0+S/r0EeZ/BP9lXw98Hr6fV5pbvxN4rvm33eu6swnu3buIyeIk/2UwPrXp1FFc05ym+aTGfDnxl/Zi/aF8Xft+N8QrW0+H/AIh8KaHB9m8M2ur3sqQaYGHzzPCqndLk8MOmODXulj+zN4m+K1uknxX8Urq8fU6NoivZab9HyS8g+pr2+iuieLm1FJJWVtEBm+E/Buk+A9Eh03RdNstKsIBhILWFYkX8AOvvWlRRXK3fVgFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAf/2Q=='; // ใส่ข้อมูล Base64 ของรูปภาพลายเซ็น

        $signatureWidth = 40; // ปรับตามความเหมาะสม
        
        // ตำแหน่ง X ของรูปลายเซ็น (กึ่งกลางของพื้นที่ 90)
        $signatureX = 10 + (90 - $signatureWidth) / 2;
        
        // แสดงรูปลายเซ็น - ระบุชนิดไฟล์เป็น JPEG
       // $pdf->Image('@'.base64_decode($signatureBase64), $signatureX, $startY, $signatureWidth, 0, 'JPEG');
        
            // ตัด prefix 'data:image/jpeg;base64,' ออกก่อน
        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64);

        // Decode ข้อมูลภาพอย่างถูกต้อง
        $imageBinary = base64_decode($base64Data);

        // แสดงรูปลายเซ็น - ระบุชนิดไฟล์เป็น JPEG
        $pdf->Image('@'.$imageBinary, $signatureX, $startY, $signatureWidth, 0, 'JPEG');



        // เลื่อนตำแหน่ง Y ให้พ้นรูปลายเซ็น
        $pdf->SetY($startY + $signatureHeight);



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

