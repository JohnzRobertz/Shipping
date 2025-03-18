<?php
// ตรวจสอบว่ามีการส่ง ID มาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?page=invoice');
    exit;
}

// ดึงข้อมูลใบแจ้งหนี้
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
// ใช้ shipments ที่ส่งมาจาก controller ซึ่งมีข้อมูล origin และ destination แล้ว

// ดึงข้อมูลค่าใช้จ่ายเพิ่มเติม
$invoiceChargeModel = new InvoiceCharge();
$additionalCharges = $invoiceChargeModel->getChargesByInvoiceId($id);
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('invoice') ?> #<?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .invoice-header {
            margin-bottom: 30px;
        }
        .company-info {
            text-align: right;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .customer-info {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print mb-3">
            <button class="btn btn-primary" onclick="window.print()"><?= __('print_invoice') ?></button>
            <a href="index.php?page=invoice&action=view&id=<?= $invoice['id'] ?>" class="btn btn-secondary"><?= __('back_to_invoice') ?></a>
        </div>
        
        <div class="invoice-header row">
            <div class="col-6">
                <h1><?= __('invoice') ?></h1>
                <div><strong><?= __('company_name') ?></strong></div>
                <div><?= __('company_address') ?></div>
                <div><?= __('company_city_country') ?></div>
                <div><?= __('company_phone') ?></div>
                <div><?= __('company_email') ?></div>
            </div>
            <div class="col-6 company-info">
                <div><strong><?= __('invoice_number') ?>:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?></div>
                <div><strong><?= __('date') ?>:</strong> <?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></div>
                <div><strong><?= __('due_date') ?>:</strong> <?= date('d/m/Y', strtotime($invoice['due_date'])) ?></div>
                <div>
                    <strong><?= __('status') ?>:</strong> 
                    <?= $invoice['status'] == 'paid' ? __('paid') : __('unpaid') ?>
                    <?php if ($invoice['status'] == 'unpaid' && strtotime($invoice['due_date']) < time()): ?>
                        (<?= __('overdue') ?>)
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="customer-info">
            <h4><?= __('bill_to') ?>:</h4>
            <div><strong><?= htmlspecialchars($customer['name']) ?></strong></div>
            <div><?= nl2br(htmlspecialchars($customer['address'])) ?></div>
            <div><?= __('phone') ?>: <?= htmlspecialchars($customer['phone']) ?></div>
            <div><?= __('email') ?>: <?= htmlspecialchars($customer['email']) ?></div>
        </div>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><?= __('tracking_number') ?></th>
                    <th><?= __('description') ?></th>
                    <th><?= __('origin') ?></th>
                    <th><?= __('destination') ?></th>
                    <th><?= __('weight') ?> (<?= __('kg') ?>)</th>
                    <th><?= __('amount') ?> (<?= __('currency') ?>)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shipments as $shipment): ?>
                    <tr>
                        <td><?= htmlspecialchars($shipment['tracking_number']) ?></td>
                        <td><?= __('shipping_service') ?> - <?= htmlspecialchars($shipment['transport_type'] ?? __('standard')) ?></td>
                        <td><?= htmlspecialchars($shipment['origin'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($shipment['destination'] ?? 'N/A') ?></td>
                        <td><?= number_format($shipment['weight'], 2) ?></td>
                        <td class="text-end"><?= number_format($shipment['total_price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end"><strong><?= __('subtotal') ?>:</strong></td>
                    <td class="text-end"><strong><?= number_format($invoice['subtotal'] ?? $invoice['total_amount'], 2) ?></strong></td>
                </tr>
                
                <?php if (!empty($additionalCharges)): ?>
                    <?php foreach ($additionalCharges as $charge): ?>
                        <tr>
                            <td colspan="5" class="text-end">
                                <?= htmlspecialchars($charge['description']) ?>
                                <?php if ($charge['is_percentage'] == 1): ?>
                                    (<?= number_format($charge['amount'], 2) ?>%)
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php
                                $chargeAmount = $charge['is_percentage'] == 1 
                                    ? ($invoice['subtotal'] ?? $invoice['total_amount']) * ($charge['amount'] / 100) 
                                    : $charge['amount'];
                                
                                if ($charge['charge_type'] == 'discount') {
                                    echo '-';
                                    $chargeAmount = abs($chargeAmount);
                                }
                                
                                echo number_format($chargeAmount, 2);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (isset($invoice['tax_rate']) && $invoice['tax_rate'] > 0): ?>
                        <tr>
                            <td colspan="5" class="text-end"><?= __('tax') ?> (<?= number_format($invoice['tax_rate'] * 100, 0) ?>%):</td>
                            <td class="text-end"><?= number_format($invoice['tax_amount'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>
                
                <tr>
                    <td colspan="5" class="text-end"><strong><?= __('total') ?>:</strong></td>
                    <td class="text-end"><strong><?= number_format($invoice['total_amount'], 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        
        <?php if (!empty($invoice['notes'])): ?>
            <div class="mt-4">
                <h5><?= __('notes') ?>:</h5>
                <p><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="mt-5">
            <h5><?= __('payment_information') ?>:</h5>
            <p>
                <?= __('please_make_payment_to') ?>:<br>
                <?= __('bank') ?>: <?= __('your_bank_name') ?><br>
                <?= __('account_name') ?>: <?= __('your_company_name') ?><br>
                <?= __('account_number') ?>: 123-4-56789-0<br>
                <?= __('reference') ?>: <?= htmlspecialchars($invoice['invoice_number']) ?>
            </p>
        </div>
        
        <div class="footer">
            <p><?= __('thank_you_for_your_business') ?></p>
        </div>
    </div>
</body>
</html>

