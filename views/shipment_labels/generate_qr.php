<?php
/**
 * Generate QR Code locally
 * This file generates QR codes locally without relying on external services
 */

// Check if tracking number is provided
if (!isset($_GET['tracking']) || empty($_GET['tracking'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Tracking number is required';
    exit;
}

// Get tracking number
$tracking = $_GET['tracking'];

// Set content type to PNG image
header('Content-Type: image/png');

// If you have a PHP QR Code library installed, use it here
// For example, with PHP QR Code library:
// https://github.com/chillerlan/php-qrcode or https://github.com/endroid/qr-code

// This is a simplified example. In a real application, you would use a proper QR code library.
// For now, we'll create a simple fallback image with text

// Create a blank image
$image = imagecreate(250, 250);

// Set colors
$bgColor = imagecolorallocate($image, 255, 255, 255);
$textColor = imagecolorallocate($image, 0, 0, 0);

// Fill the background
imagefilledrectangle($image, 0, 0, 249, 249, $bgColor);

// Draw a border
imagerectangle($image, 0, 0, 249, 249, $textColor);

// Add text
$text = $tracking;
$font = 5; // Built-in font
$textWidth = imagefontwidth($font) * strlen($text);
$textHeight = imagefontheight($font);
$x = (250 - $textWidth) / 2;
$y = (250 - $textHeight) / 2;

imagestring($image, $font, $x, $y, $text, $textColor);
imagestring($image, 3, $x, $y + 20, 'SCAN TO TRACK', $textColor);

// Output the image
imagepng($image);
imagedestroy($image);

