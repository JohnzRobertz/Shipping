<?php
/**
 * View Helper - ช่วยในการแสดงผลหน้าเว็บ
 */

/**
 * ฟังก์ชันสำหรับแสดงผลหน้าเว็บ
 * 
 * @param string $view ชื่อไฟล์ view ที่ต้องการแสดงผล
 * @param array $data ข้อมูลที่ต้องการส่งไปยัง view
 * @return void
 */
function view($view, $data = []) {
    // ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
    if (!empty($data)) {
        extract($data);
    }
    
    // กำหนดพาธของไฟล์ view
    $viewPath = 'views/' . $view . '.php';
    
    // ตรวจสอบว่าไฟล์ view มีอยู่หรือไม่
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        // ถ้าไม่พบไฟล์ view ให้แสดงข้อความแจ้งเตือน
        echo "Error: View file '{$viewPath}' not found.";
    }
}

