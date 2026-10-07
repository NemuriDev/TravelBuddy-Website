<?php

// ERROR REPORTING
// Log everything, show nothing to visitors.
error_reporting(E_ALL);
ini_set('display_errors', 0);

date_default_timezone_set('Asia/Manila');
mb_internal_encoding('UTF-8');

// EMAIL CONFIGURATION (Brevo transactional email API)
define('BREVO_API_KEY', ''); 
define('BREVO_SENDER_EMAIL', 'travelbuddies79@gmail.com'); 
define('BREVO_SENDER_NAME', 'TravelBuddy');
define('CONTACT_RECIPIENT', 'mjntarin@tip.edu.ph'); // where contact-form messages are delivered

// DATABASE CONFIGURATION
define('DB_HOST', 'sql313.ezyro.com');
define('DB_NAME', 'ezyro_43047792_users');
define('DB_USER', 'ezyro_43047792');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// ============================================================
// SITE
// ============================================================

define('SITE_NAME', 'TravelBuddy');
define('SITE_TAGLINE', 'A visual field guide to places worth the detour in Bulacan.');
define('SITE_URL', 'https://travelbuddyy.liveblog365.com');

// ============================================================
// USER ROLES
// ============================================================

define('ROLE_ADMIN', 'admin');
define('ROLE_TOURIST', 'tourist');
define('ROLE_GUEST', 'guest');

// ============================================================
// INPUT LIMITS AND ALLOWED VALUES
// What the server enforces on user-submitted fields. HTML maxlength
// attributes and <select> options are only conveniences; the browser
// can be edited, so these are the source of truth.
// ============================================================

define('MAX_NAME_LENGTH', 100);
define('MAX_EMAIL_LENGTH', 254);
define('MAX_LOCATION_LENGTH', 100);
define('MAX_BIO_LENGTH', 500);
define('MAX_PASSWORD_BYTES', 72); // bcrypt ignores everything past 72 bytes
define('MAX_MESSAGE_LENGTH', 2000);
define('MAX_URL_LENGTH', 500);
define('MAX_DESTINATION_SLUG_LENGTH', 100);
define('MAX_DESTINATION_NAME_LENGTH', 150);
define('MAX_DESTINATION_SHORT_LENGTH', 100); // municipality, field note
define('MAX_DESTINATION_LOCATION_LENGTH', 255);
define('MAX_DESTINATION_DESCRIPTION_LENGTH', 2000);

define('CONTACT_SUBJECTS', [
    'General Inquiry',
    'Submit a Place',
    'Correct Information',
    'Partnership',
    'Other',
]);

define('DESTINATION_CATEGORIES', ['Nature', 'Heritage', 'Sacred', 'Resort']);

define('DESTINATION_TAGS', [
    'Adventure', 'Ancestral home', 'Basilica', 'Cave', 'Cave & river',
    'Cave & spring', 'Caves & trails', 'Caving', 'Colonial', 'Family',
    'Farm café', 'Farm stay', 'Glamping', 'Hidden waterfall', 'Hiking',
    'Hillwalk', 'Historic church', 'History', 'House museum', 'Landmark',
    'Memorial', 'Monument', 'Museum', 'Parish', 'Pilgrimage',
    'Private pool', 'Reservoir view', 'Resort', 'Retreat',
    'Riverside shrine', 'Roadside', 'Shrine', 'Small resort',
    'Summit hike', 'Viewpoint', 'Waterfall', 'Waterpark', 'Wave pools',
]);

// ============================================================
// SESSION
// ============================================================

define('SESSION_NAME', 'travelbuddy_session');
define('SESSION_LIFETIME', 3600); // 1 hour

ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    // Only sent over HTTPS when the page itself is served over HTTPS,
    // so a plain-http visit can still log in.
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// CSRF
// ============================================================

define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 1800); // 30 minutes

function generateCSRFToken() {
    // Reuse a live token instead of minting a new one on every include,
    // so a token issued to a page doesn't go stale before the browser
    // posts back with it.
    if (
        isset($_SESSION[CSRF_TOKEN_NAME], $_SESSION[CSRF_TOKEN_NAME . '_time']) &&
        (time() - $_SESSION[CSRF_TOKEN_NAME . '_time'] <= CSRF_TOKEN_LIFETIME)
    ) {
        return $_SESSION[CSRF_TOKEN_NAME];
    }

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
// DATABASE CONNECTION
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
// SESSION HELPERS
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
        'profile_photo' => $_SESSION['user_profile_photo'] ?? null,
    ];
}

/**
 * Fetch full user data from the database (used by the profile page).
 */
function getUserData($userId) {
    $db = getDBConnection();
    if (!$db) return null;
    $stmt = $db->prepare("SELECT id, name, email, location, bio, profile_photo, created_at FROM users WHERE id = ?");
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
// REDIRECTS
// ============================================================

/**
 * Send the visitor back to auth.php after a failed login/signup, keeping
 * the same tab open and refilling what they'd already typed. $fields
 * end up in the URL, so they should only ever contain non-sensitive
 * values (name, email, location) - never a password.
 */
function redirectAuthError($tab, $error, $fields = []) {
    $params = array_merge(['tab' => $tab, 'error' => $error], $fields);
    header('Location: auth.php?' . http_build_query($params));
    exit;
}

// ============================================================
// EMAIL
// ============================================================

/**
 * Sends one transactional email through Brevo's HTTP API (POST over
 * HTTPS, port 443) - never SMTP, so hosting-provider port restrictions
 * on 25/465/587 can't affect it. Throws on any failure (missing curl,
 * network error, or a non-2xx response from Brevo) so the caller can
 * decide how to tell the visitor, rather than silently doing nothing.
 */
function sendTransactionalEmail($toEmail, $toName, $subject, $textBody, $replyToEmail = null, $replyToName = null) {
    $payload = [
        'sender' => ['name' => BREVO_SENDER_NAME, 'email' => BREVO_SENDER_EMAIL],
        'to' => [['email' => $toEmail, 'name' => $toName]],
        'subject' => $subject,
        'textContent' => $textBody,
    ];

    if ($replyToEmail !== null) {
        $payload['replyTo'] = ['email' => $replyToEmail, 'name' => $replyToName ?? $replyToEmail];
    }

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . BREVO_API_KEY,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 15,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Brevo request failed: ' . $curlError);
    }

    if ($statusCode < 200 || $statusCode >= 300) {
        throw new RuntimeException("Brevo API returned HTTP {$statusCode}: {$response}");
    }
}

// ============================================================
// LOGGING
// ============================================================

function logMessage($message, $level = 'info') {
    $logEntry = date('Y-m-d H:i:s') . " [$level] " . $message;
    error_log($logEntry);
}
