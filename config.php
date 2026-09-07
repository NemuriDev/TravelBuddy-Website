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

define('ENVIRONMENT', 'production'); // Change to 'production' for live site

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

define('SITE_NAME', 'TravelBuddy');
define('SITE_TAGLINE', 'A visual field guide to places worth the detour in Bulacan.');
define('SITE_URL', 'https://travelbuddyy.page.gd');
define('SITE_EMAIL', 'info@travelbuddy.com');

// ============================================================
// DATABASE CONFIGURATION
// ============================================================

define('DB_HOST', 'sql200.infinityfree.com');
define('DB_NAME', 'if0_42856607_users');
define('DB_USER', 'if0_42856607');
define('DB_PASS', 'nUdO2ovrX2FD');

define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// ============================================================
// SESSION CONFIGURATION
// ============================================================

define('SESSION_NAME', 'travelbuddy_session');
define('SESSION_LIFETIME', 3600); // 1 hour

// Set session cookie parameters and garbage collection lifetime
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_set_cookie_params(SESSION_LIFETIME);

session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// FILE & PATH CONFIGURATION
// ============================================================

define('ROOT_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('ASSETS_PATH', ROOT_PATH . '/assets');

define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOAD_URL', SITE_URL . '/uploads');

// ============================================================
// UPLOAD CONFIGURATION
// ============================================================

define('MAX_FILE_SIZE', 5242880);
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

// ============================================================
// USER ROLES
// ============================================================

define('ROLE_ADMIN', 'admin');
define('ROLE_TOURIST', 'tourist');
define('ROLE_GUEST', 'guest');

// ============================================================
// CACHE CONFIGURATION
// ============================================================

define('CACHE_ENABLED', false);
define('CACHE_DURATION', 3600);

// ============================================================
// SECURITY CONFIGURATION
// ============================================================

define('PASSWORD_COST', 12);

define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 1800);

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

define('GOOGLE_MAPS_API_KEY', '');

// ============================================================
// META DEFAULTS
// ============================================================

define('META_TITLE', SITE_NAME);
define('META_DESCRIPTION', SITE_TAGLINE);
define('META_KEYWORDS', 'travel, bulacan, philippines, tourism, destinations, heritage, nature');

define('OG_TITLE', SITE_NAME);
define('OG_DESCRIPTION', SITE_TAGLINE);
define('OG_IMAGE', SITE_URL . '/assets/images/og-image.jpg');

// ============================================================
// DATABASE CONNECTION FUNCTION
// ============================================================

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

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

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
        'location' => $_SESSION['user_location'] ?? null,
        'created_at' => $_SESSION['user_created_at'] ?? null,
    ];
}

/**
 * Fetch full user data from database (useful for profile)
 */
function getUserData($userId) {
    $db = getDBConnection();
    if (!$db) return null;
    $stmt = $db->prepare("SELECT id, name, email, location, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

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

function generateCSRFToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION[CSRF_TOKEN_NAME] = $token;
    $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
    return $token;
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_NAME . '_time'])) {
        return false;
    }
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

function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

function redirect($url, $status = 302) {
    header('Location: ' . $url, true, $status);
    exit();
}

// ============================================================
// SECURITY HELPER FUNCTIONS
// ============================================================

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function escape($output) {
    return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
}

// ============================================================
// PAGE ACTIVE STATE HELPER
// ============================================================

function isActive($page, $active) {
    return $page === $active ? 'active' : '';
}

// ============================================================
// THEME FUNCTIONS
// ============================================================

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

date_default_timezone_set('Asia/Manila');
mb_internal_encoding('UTF-8');

$directories = [UPLOAD_PATH, INCLUDES_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ============================================================
// LOGGING FUNCTION
// ============================================================

function logMessage($message, $level = 'info') {
    $logEntry = date('Y-m-d H:i:s') . " [$level] " . $message;
    error_log($logEntry);
}

// ============================================================
// 404 HANDLER
// ============================================================

function show404() {
    http_response_code(404);
    include ROOT_PATH . '/404.php';
    exit();
}
?>