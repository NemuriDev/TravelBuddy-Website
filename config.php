<?php
/**
 * TravelBuddies - Configuration File
 * 
 * This file contains all global configuration settings for the application.
 * Include this file at the top of every page before any other code.
 */

// ============================================================
// ERROR REPORTING (Development vs Production)
// ============================================================

// Set error reporting based on environment
// In development: display all errors
// In production: display no errors
define('ENVIRONMENT', 'development'); // Change to 'production' for live site

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================================
// SITE CONFIGURATION
// ============================================================

// Site information
define('SITE_NAME', 'TravelBuddy');
define('SITE_TAGLINE', 'A visual field guide to places worth the detour in Bulacan.');
define('SITE_URL', 'http://localhost/travelbuddies'); // Change to your domain
define('SITE_EMAIL', 'info@travelbuddy.com');

// ============================================================
// DATABASE CONFIGURATION
// ============================================================

// Database settings - update with your actual credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'travelbuddies');
define('DB_USER', 'root');
define('DB_PASS', '');

// Database connection options
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// ============================================================
// SESSION CONFIGURATION
// ============================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session settings
define('SESSION_NAME', 'travelbuddies_session');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// Set session name
session_name(SESSION_NAME);

// ============================================================
// FILE & PATH CONFIGURATION
// ============================================================

// Directory paths
define('ROOT_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// URL paths
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOAD_URL', SITE_URL . '/uploads');

// ============================================================
// UPLOAD CONFIGURATION
// ============================================================

// File upload limits
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf']);

// ============================================================
// PAGINATION CONFIGURATION
// ============================================================

define('PLACES_PER_PAGE', 12);
define('REVIEWS_PER_PAGE', 10);

// ============================================================
// DATE & TIME CONFIGURATION
// ============================================================

date_default_timezone_set('Asia/Manila');
define('DATE_FORMAT', 'F j, Y');
define('DATETIME_FORMAT', 'F j, Y g:i A');

// ============================================================
// USER ROLES
// ============================================================

define('ROLE_ADMIN', 1);
define('ROLE_EDITOR', 2);
define('ROLE_USER', 3);
define('ROLE_GUEST', 4);

// ============================================================
// CACHE CONFIGURATION
// ============================================================

// Enable/disable caching
define('CACHE_ENABLED', false);
define('CACHE_DURATION', 3600); // 1 hour in seconds

// ============================================================
// SECURITY CONFIGURATION
// ============================================================

// Password hashing cost
define('PASSWORD_COST', 12);

// CSRF token settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 1800); // 30 minutes in seconds

// ============================================================
// EMAIL CONFIGURATION
// ============================================================

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM', SITE_EMAIL);
define('MAIL_FROM_NAME', SITE_NAME);

// ============================================================
// SOCIAL LINKS
// ============================================================

define('SOCIAL_FACEBOOK', 'https://facebook.com/travelbuddy');
define('SOCIAL_INSTAGRAM', 'https://instagram.com/travelbuddy');
define('SOCIAL_TWITTER', 'https://twitter.com/travelbuddy');
define('SOCIAL_YOUTUBE', 'https://youtube.com/travelbuddy');

// ============================================================
// API CONFIGURATION
// ============================================================

// Google Maps API Key (if using maps)
define('GOOGLE_MAPS_API_KEY', '');

// ============================================================
// META DEFAULTS
// ============================================================

define('META_TITLE', SITE_NAME);
define('META_DESCRIPTION', SITE_TAGLINE);
define('META_KEYWORDS', 'travel, bulacan, philippines, tourism, destinations, heritage, nature');

// Open Graph defaults
define('OG_TITLE', SITE_NAME);
define('OG_DESCRIPTION', SITE_TAGLINE);
define('OG_IMAGE', SITE_URL . '/assets/images/og-image.jpg');

// ============================================================
// DEPRECATED - REMOVED SECTIONS
// ============================================================

// Festival Banner has been removed from the website
// Home Reviews section has been removed from the website

// ============================================================
// DATABASE CONNECTION FUNCTION
// ============================================================

/**
 * Get database connection
 * 
 * @return PDO|false Database connection object or false on failure
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return false;
    }
}

// ============================================================
// SESSION HELPER FUNCTIONS
// ============================================================

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? ROLE_GUEST,
        'initials' => $_SESSION['user_initials'] ?? null,
    ];
}

/**
 * Get user initials from name
 * 
 * @param string $name Full name
 * @return string Initials (max 2 characters)
 */
function getUserInitials($name) {
    $initials = '';
    $words = explode(' ', trim($name));
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(mb_substr($word, 0, 1));
        }
    }
    return mb_substr($initials, 0, 2);
}

// ============================================================
// CSRF TOKEN FUNCTIONS
// ============================================================

/**
 * Generate a CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION[CSRF_TOKEN_NAME] = $token;
    $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
    return $token;
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_NAME . '_time'])) {
        return false;
    }
    
    // Check if token has expired
    if (time() - $_SESSION[CSRF_TOKEN_NAME . '_time'] > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION[CSRF_TOKEN_NAME]);
        unset($_SESSION[CSRF_TOKEN_NAME . '_time']);
        return false;
    }
    
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// ============================================================
// URL HELPER FUNCTIONS
// ============================================================

/**
 * Get full URL for a path
 * 
 * @param string $path Relative path
 * @return string Full URL
 */
function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @param int $status HTTP status code
 */
function redirect($url, $status = 302) {
    header('Location: ' . $url, true, $status);
    exit();
}

// ============================================================
// SECURITY HELPER FUNCTIONS
// ============================================================

/**
 * Sanitize input string
 * 
 * @param string $input Raw input
 * @return string Sanitized input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for HTML
 * 
 * @param string $output Raw output
 * @return string Escaped output
 */
function escape($output) {
    return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
}

// ============================================================
// PAGE ACTIVE STATE HELPER
// ============================================================

/**
 * Check if a page is active
 * 
 * @param string $page Current page
 * @param string $active Active page identifier
 * @return string 'active' class if matches, empty string otherwise
 */
function isActive($page, $active) {
    return $page === $active ? 'active' : '';
}

// ============================================================
// THEME FUNCTIONS
// ============================================================

/**
 * Get theme color variables for inline CSS
 * 
 * @return array Array of color values
 */
function getThemeColors() {
    return [
        'forest-green' => '#1a4332',
        'forest-green-dark' => '#123527',
        'terracotta' => '#c45c26',
        'terracotta-dark' => '#a84d1f',
        'amber' => '#f59e0b',
        'cream' => '#fef9f0',
        'sand' => '#e8d5b7',
    ];
}

// ============================================================
// ERROR HANDLING
// ============================================================

/**
 * Display a user-friendly error message
 * 
 * @param string $message Error message
 * @param bool $isCritical Whether the error is critical
 */
function showError($message, $isCritical = false) {
    if ($isCritical && ENVIRONMENT === 'production') {
        $message = 'An error occurred. Please try again later.';
    }
    
    echo '<div class="error-message" style="padding: 20px; margin: 20px; background: #fee; border: 1px solid #c45c26; border-radius: 8px; color: #1a4332;">';
    echo '<strong>Error:</strong> ' . htmlspecialchars($message);
    echo '</div>';
}

// ============================================================
// MAINTENANCE MODE
// ============================================================

define('MAINTENANCE_MODE', false);
define('MAINTENANCE_IP_WHITELIST', ['127.0.0.1', '::1']);

/**
 * Check if maintenance mode is active
 * 
 * @return bool True if in maintenance mode, false otherwise
 */
function isMaintenanceMode() {
    if (!MAINTENANCE_MODE) {
        return false;
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return !in_array($ip, MAINTENANCE_IP_WHITELIST, true);
}

// ============================================================
// INITIALIZATION
// ============================================================

// Set default timezone
date_default_timezone_set('Asia/Manila');

// Set default character encoding
mb_internal_encoding('UTF-8');

// Create required directories if they don't exist
$directories = [UPLOAD_PATH, INCLUDES_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ============================================================
// INCLUDES
// ============================================================

// Auto-load helper functions (if you have them)
// require_once INCLUDES_PATH . '/functions.php';

// ============================================================
// LOGGING FUNCTION
// ============================================================

/**
 * Log a message to the error log
 * 
 * @param string $message Message to log
 * @param string $level Log level (info, warning, error)
 */
function logMessage($message, $level = 'info') {
    $logEntry = date('Y-m-d H:i:s') . " [$level] " . $message;
    error_log($logEntry);
}

// ============================================================
// 404 HANDLER
// ============================================================

/**
 * Show 404 page
 */
function show404() {
    http_response_code(404);
    include ROOT_PATH . '/404.php';
    exit();
}

// ============================================================
// DEPRECATED CONSTANTS - KEPT FOR BACKWARD COMPATIBILITY
// ============================================================

// These constants are kept for backward compatibility but are no longer used
// They will be removed in a future version

// Festival Banner (deprecated)
// define('FESTIVAL_ENABLED', false);

// Home Reviews (deprecated)
// define('REVIEWS_ENABLED', false);

// ============================================================
// LOAD DATABASE CONNECTION
// ============================================================

// Initialize database connection if needed
// $db = getDBConnection();

// ============================================================
// END OF CONFIG
// ============================================================
?>