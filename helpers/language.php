<?php
/**
 * Load language file based on selected language
 * 
 * @param string $lang Language code (en or th)
 * @return array Language translations
 */
function loadLanguage($lang) {
    $langFile = 'languages/' . $lang . '.php';
    if (file_exists($langFile)) {
        return include $langFile;
    } else {
        // Fallback to Thai
        return include 'languages/th.php';
    }
}

/**
 * Translate text based on current language
 * 
 * @param string $key Translation key
 * @return string Translated text
 */
// Check if function exists before declaring it to avoid redeclaration error
if (!function_exists('__')) {
    function __($key) {
        global $lang;
        return isset($lang[$key]) ? $lang[$key] : $key;
    }
}
?>

