<?php
/**
 * Translation helper functions
 */

/**
 * Get translation for a given key
 * 
 * @param string $key Translation key
 * @param array $params Optional parameters for string replacement
 * @return string Translated text
 */
function getTranslation($key, $params = []) {
    global $lang;
    
    // If language array is not loaded, try to load it
    if (!isset($lang) || !is_array($lang)) {
        $currentLang = $_SESSION['lang'] ?? 'th';
        $langFile = 'languages/' . $currentLang . '.php';
        
        if (file_exists($langFile)) {
            $lang = include $langFile;
        } else {
            // Fallback to Thai if language file doesn't exist
            $lang = include 'languages/th.php';
        }
    }
    
    // Get translation
    $translation = isset($lang[$key]) ? $lang[$key] : $key;
    
    // Replace parameters if any
    if (!empty($params)) {
        foreach ($params as $param => $value) {
            $translation = str_replace(':' . $param, $value, $translation);
        }
    }
    
    return $translation;
}

/**
 * Get current language code
 * 
 * @return string Current language code
 */
function getCurrentLanguage() {
    return $_SESSION['lang'] ?? 'th';
}

/**
 * Check if current language is RTL
 * 
 * @return bool True if current language is RTL
 */
function isRTL() {
    $rtlLanguages = ['ar', 'he', 'fa'];
    return in_array(getCurrentLanguage(), $rtlLanguages);
}

