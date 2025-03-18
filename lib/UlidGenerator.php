<?php
/**
 * ULID Generator Class
 * 
 * A utility class for generating and working with ULIDs (Universally Unique Lexicographically Sortable Identifiers)
 */
class UlidGenerator {
    /**
     * Generate a new ULID
     * 
     * @return string The generated ULID
     */
    public static function generate() {
        // Time component: 48 bits (6 bytes) of milliseconds since Unix epoch
        $time = microtime(true) * 1000;
        $timeBinary = pack('J', $time); // 'J' format code for 64-bit unsigned integer
        $timeHex = bin2hex(substr($timeBinary, 2, 6)); // Take only 6 bytes
        
        // Random component: 80 bits (10 bytes) of randomness
        $randomBytes = random_bytes(10);
        $randomHex = bin2hex($randomBytes);
        
        // Combine time and random components
        $hex = $timeHex . $randomHex;
        
        // Convert to Crockford's Base32
        $base32Chars = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $ulid = '';
        
        // Process 5 bits at a time (hex to base32)
        $chunks = str_split($hex, 5);
        foreach ($chunks as $chunk) {
            $value = hexdec($chunk);
            $ulid .= $base32Chars[($value >> 15) & 31];
            $ulid .= $base32Chars[($value >> 10) & 31];
            $ulid .= $base32Chars[($value >> 5) & 31];
            $ulid .= $base32Chars[$value & 31];
        }
        
        return substr($ulid, 0, 26);
    }
    
    /**
     * Check if a string is a valid ULID
     * 
     * @param string $ulid The string to check
     * @return bool True if the string is a valid ULID, false otherwise
     */
    public static function isValid($ulid) {
        return is_string($ulid) && 
               strlen($ulid) === 26 && 
               preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $ulid);
    }
    
    /**
     * Get the timestamp from a ULID
     * 
     * @param string $ulid The ULID to extract the timestamp from
     * @return int|false The timestamp in milliseconds, or false if the ULID is invalid
     */
    public static function getTimestamp($ulid) {
        if (!self::isValid($ulid)) {
            return false;
        }
        
        // Convert the first 10 characters (time component) from Crockford's Base32 to decimal
        $base32Chars = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $timeChars = substr($ulid, 0, 10);
        $time = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $char = strtoupper($timeChars[$i]);
            $value = strpos($base32Chars, $char);
            if ($value === false) {
                return false;
            }
            $time = $time * 32 + $value;
        }
        
        return $time;
    }
    
    /**
     * Generate a ULID from a specific timestamp
     * 
     * @param int $timestamp The timestamp in milliseconds
     * @return string The generated ULID
     */
    public static function fromTimestamp($timestamp) {
        // Time component: 48 bits (6 bytes) of milliseconds
        $timeBinary = pack('J', $timestamp); // 'J' format code for 64-bit unsigned integer
        $timeHex = bin2hex(substr($timeBinary, 2, 6)); // Take only 6 bytes
        
        // Random component: 80 bits (10 bytes) of randomness
        $randomBytes = random_bytes(10);
        $randomHex = bin2hex($randomBytes);
        
        // Combine time and random components
        $hex = $timeHex . $randomHex;
        
        // Convert to Crockford's Base32
        $base32Chars = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $ulid = '';
        
        // Process 5 bits at a time (hex to base32)
        $chunks = str_split($hex, 5);
        foreach ($chunks as $chunk) {
            $value = hexdec($chunk);
            $ulid .= $base32Chars[($value >> 15) & 31];
            $ulid .= $base32Chars[($value >> 10) & 31];
            $ulid .= $base32Chars[($value >> 5) & 31];
            $ulid .= $base32Chars[$value & 31];
        }
        
        return substr($ulid, 0, 26);
    }
}

