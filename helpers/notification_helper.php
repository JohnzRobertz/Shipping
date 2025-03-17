<?php
/**
 * ฟังก์ชันช่วยเหลือสำหรับการส่งการแจ้งเตือน
 */

/**
 * ส่งอีเมลแจ้งเตือนลูกค้าเมื่อพัสดุถึงประเทศไทยและมีการส่งต่อให้ขนส่งภายในประเทศ
 */
function sendDomesticTrackingNotification($shipment) {
    // ตรวจสอบว่ามีอีเมลผู้รับหรือไม่
    if (empty($shipment['receiver_email'])) {
        return false;
    }
    
    // โหลดฟังก์ชันช่วยเหลือ
    require_once 'tracking_helper.php';
    
    // โหลดไฟล์ภาษา
    $lang = include 'languages/' . (isset($_SESSION['language']) ? $_SESSION['language'] : 'en') . '.php';
    
    // ข้อมูลสำหรับส่งอีเมล
    $to = $shipment['receiver_email'];
    $subject = $lang['site_title'] . ' - ' . $lang['status_local_delivery'] . ' - ' . $shipment['tracking_number'];
    
    // สร้าง URL สำหรับติดตามพัสดุภายในประเทศ
    $domesticTrackingUrl = getDomesticTrackingUrl($shipment['domestic_carrier'], $shipment['domestic_tracking_number']);
    $carrierName = getDomesticCarrierName($shipment['domestic_carrier']);
    
    // เนื้อหาอีเมล
    $message = '
    <html>
    <head>
        <title>' . $lang['status_local_delivery'] . ' - ' . $shipment['tracking_number'] . '</title>
    </head>
    <body>
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
            <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
                <h2 style="color: #0d6efd;">' . $lang['status_local_delivery'] . '</h2>
            </div>
            
            <div style="background-color: #ffffff; padding: 20px; border-radius: 0 0 5px 5px; border: 1px solid #dee2e6;">
                <p>' . $lang['greeting'] . ' ' . $shipment['receiver_name'] . ',</p>
                
                <p>' . $lang['domestic_delivery_email_message'] . '</p>
                
                <div style="background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <h3 style="margin-top: 0;">' . $lang['shipping_information'] . ':</h3>
                    <p><strong>' . $lang['tracking_number'] . ':</strong> ' . $shipment['tracking_number'] . '</p>
                    <p><strong>' . $lang['domestic_carrier'] . ':</strong> ' . $carrierName . '</p>
                    <p><strong>' . $lang['domestic_tracking_number'] . ':</strong> ' . $shipment['domestic_tracking_number'] . '</p>
                </div>
                
                <p>' . $lang['track_package_message'] . ':</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $domesticTrackingUrl . '" style="background-color: #0d6efd; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">' . $lang['track'] . ' ' . $carrierName . '</a>
                </div>
                
                <p>' . $lang['contact_support_message'] . ' <a href="mailto:support@example.com">support@example.com</a> ' . $lang['or'] . ' 02-123-4567</p>
                
                <p>' . $lang['thank_you_message'] . '</p>
                
                <p>' . $lang['regards'] . ',<br>' . $lang['site_title'] . ' ' . $lang['team'] . '</p>
            </div>
            
            <div style="text-align: center; margin-top: 20px; color: #6c757d; font-size: 12px;">
                <p>&copy; ' . date('Y') . ' ' . $lang['site_title'] . '. ' . $lang['all_rights_reserved'] . '.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // ส่งอีเมล
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $lang['site_title'] . ' <noreply@example.com>' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * ส่ง SMS แจ้งเตือนลูกค้า (ถ้ามีบริการ SMS)
 */
function sendSmsNotification($phoneNumber, $message) {
    // ในอนาคตอาจเชื่อมต่อกับบริการ SMS API
    // ตัวอย่างเช่น Twilio, Nexmo, หรือผู้ให้บริการในประเทศไทย
    
    // สำหรับตอนนี้ เราจะเพียงแค่จำลองการส่ง SMS
    return true;
}

