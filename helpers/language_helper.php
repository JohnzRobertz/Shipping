<?php
/**
 * Language Helper
 * Handles language switching and translation functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'th'; // Default language (Thai)
}

// Handle language switching
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'th'])) {
    $_SESSION['language'] = $_GET['lang'];
    
    // Redirect back to the same page without the lang parameter
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    $query_params = $_GET;
    unset($query_params['lang']);
    
    if (!empty($query_params)) {
        $redirect_url .= '?' . http_build_query($query_params);
    }
    
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Get current language
 * 
 * @return string Current language code
 */
function getCurrentLanguage() {
    return $_SESSION['language'] ?? 'th';
}

/**
 * Load language file
 * 
 * @param string $language Language code
 * @return array Language translations
 */
function loadLanguage($language = null) {
    $language = $language ?? getCurrentLanguage();
    $language_file = __DIR__ . '/../languages/' . $language . '.php';
    
    if (file_exists($language_file)) {
        return require $language_file;
    }
    
    // Fallback to English if language file doesn't exist
    return require __DIR__ . '/../languages/en.php';
}

/**
 * Get translation for a key
 * 
 * @param string $key Translation key
 * @param array $replacements Optional replacements for placeholders
 * @return string Translated text
 */
function getTranslation($key, $replacements = []) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = loadLanguage();
    }
    
    $text = $translations[$key] ?? $key;
    
    // Replace placeholders if any
    if (!empty($replacements)) {
        foreach ($replacements as $placeholder => $value) {
            $text = str_replace('%' . $placeholder . '%', $value, $text);
        }
    }
    
    return $text;
}

/**
 * Alias for getTranslation
 * 
 * @param string $key Translation key
 * @param array $replacements Optional replacements for placeholders
 * @return string Translated text
 */
// Check if function exists before declaring it to avoid redeclaration error
if (!function_exists('__')) {
    function __($key, $replacements = []) {
        return getTranslation($key, $replacements);
    }
}
?>

