<?php
/**
 * ฟังก์ชันช่วยเหลือสำหรับการติดตามพัสดุ
 */

/**
 * แปลงชื่อผู้ให้บริการขนส่งภายในประเทศเป็นชื่อที่แสดงผล
 */
function getDomesticCarrierName($carrier) {
    $carriers = [
        'thailand_post' => 'ไปรษณีย์ไทย (Thailand Post)',
        'kerry' => 'Kerry Express',
        'flash' => 'Flash Express',
        'j&t' => 'J&T Express',
        'dhl' => 'DHL',
        'lalamove' => 'Lalamove',
        'other' => 'อื่นๆ (Other)'
    ];
    
    return isset($carriers[$carrier]) ? $carriers[$carrier] : $carrier;
}

/**
 * สร้าง URL สำหรับติดตามพัสดุกับผู้ให้บริการขนส่งภายในประเทศ
 */
function getDomesticTrackingUrl($carrier, $trackingNumber) {
    $urls = [
        'thailand_post' => 'https://track.thailandpost.co.th/?trackNumber=' . $trackingNumber,
        'kerry' => 'https://th.kerryexpress.com/th/track/?track=' . $trackingNumber,
        'flash' => 'https://www.flashexpress.co.th/tracking/?se=' . $trackingNumber,
        'j&t' => 'https://www.jtexpress.co.th/index/query/gzquery.html?bills=' . $trackingNumber,
        'dhl' => 'https://www.dhl.com/th-th/home/tracking/tracking-express.html?submit=1&tracking-id=' . $trackingNumber,
        'lalamove' => 'https://www.lalamove.com/thailand/bangkok/th/track?orderid=' . $trackingNumber,
        'other' => '#'
    ];
    
    return isset($urls[$carrier]) ? $urls[$carrier] : '#';
}

/**
 * แปลงสถานะเป็นสีสำหรับแสดงผล
 */
function getStatusColor($status) {
    $colors = [
        'received' => 'secondary',
        'processing' => 'info',
        'in_transit' => 'primary',
        'arrived_destination' => 'warning',
        'local_delivery' => 'info',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'on_hold' => 'warning',
        'exception' => 'danger'
    ];
    
    return isset($colors[$status]) ? $colors[$status] : 'secondary';
}

/**
 * จัดรูปแบบวันที่
 */
function formatDate($date) {
    if (empty($date)) {
        return '-';
    }
    
    $timestamp = strtotime($date);
    
    // ตรวจสอบภาษาปัจจุบัน
    $language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
    
    if ($language == 'th') {
        // รูปแบบวันที่ภาษาไทย
        $thaiMonths = [
            1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
            'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
        ];
        
        $day = date('j', $timestamp);
        $month = $thaiMonths[date('n', $timestamp)];
        $year = date('Y', $timestamp) + 543; // แปลงเป็นปี พ.ศ.
        $time = date('H:i', $timestamp);
        
        return "$day $month $year $time น.";
    } else {
        // รูปแบบวันที่ภาษาอังกฤษ
        return date('j M Y H:i', $timestamp);
    }
}

