<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to log debug information
function debug_log($message, $data = null) {
    // $log_file = __DIR__ . '/logs/debug_' . date('Y-m-d') . '.log';
    // $timestamp = date('Y-m-d H:i:s');
    
    // $log_message = "[{$timestamp}] {$message}";
    
    // if ($data !== null) {
    //     if (is_array($data) || is_object($data)) {
    //         $log_message .= "\n" . print_r($data, true);
    //     } else {
    //         $log_message .= " " . $data;
    //     }
    // }
    
    // $log_message .= "\n";
    
    // // Create logs directory if it doesn't exist
    // if (!is_dir(__DIR__ . '/logs')) {
    //     mkdir(__DIR__ . '/logs', 0755, true);
    // }
    
    // // Write to log file
    // file_put_contents($log_file, $log_message, FILE_APPEND);
    
    // // Also log to PHP error log
    // error_log($log_message);
}

// Function to dump and die for quick debugging
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

// Function to print and continue
function pc($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
?>

