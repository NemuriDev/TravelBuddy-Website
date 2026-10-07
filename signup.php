<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: auth.php');
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    die('Invalid CSRF token');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$location = trim($_POST['location'] ?? '');

// Everything the signup form needs back on error — never the password.
// Cut to the limits so an oversized value can't bloat the redirect URL.
$oldFields = [
    'name' => mb_substr($name, 0, MAX_NAME_LENGTH),
    'email' => mb_substr($email, 0, MAX_EMAIL_LENGTH),
    'location' => mb_substr($location, 0, MAX_LOCATION_LENGTH),
];

// Validate required fields
if (empty($name) || empty($email) || empty($password)) {
    redirectAuthError('signup', 'All required fields must be filled', $oldFields);
}

if (mb_strlen($name) > MAX_NAME_LENGTH) {
    redirectAuthError('signup', 'Name must be ' . MAX_NAME_LENGTH . ' characters or fewer', $oldFields);
}

if (mb_strlen($location) > MAX_LOCATION_LENGTH) {
    redirectAuthError('signup', 'Location must be ' . MAX_LOCATION_LENGTH . ' characters or fewer', $oldFields);
}

if (mb_strlen($email) > MAX_EMAIL_LENGTH) {
    redirectAuthError('signup', 'Email must be ' . MAX_EMAIL_LENGTH . ' characters or fewer', $oldFields);
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectAuthError('signup', 'Invalid email address', $oldFields);
}

// Validate password
if (strlen($password) < 8) {
    redirectAuthError('signup', 'Password must be at least 8 characters', $oldFields);
}

if (strlen($password) > MAX_PASSWORD_BYTES) {
    redirectAuthError('signup', 'Password must be ' . MAX_PASSWORD_BYTES . ' characters or fewer', $oldFields);
}

// Connect to database
$db = getDBConnection();

if (!$db) {
    redirectAuthError('signup', 'Database error', $oldFields);
}

// Check if email already exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    redirectAuthError('signup', 'Email already registered', $oldFields);
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Create tourist account
$stmt = $db->prepare("
    INSERT INTO users 
    (name, email, password, location, role, created_at) 
    VALUES (?, ?, ?, ?, ?, NOW())
");

if ($stmt->execute([
    $name,
    $email,
    $hashed,
    $location,
    ROLE_TOURIST
])) {

    // Get newly created user ID
    $userId = $db->lastInsertId();

    session_regenerate_id(true);

    // Log user in
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_location'] = $location;
    $_SESSION['user_created_at'] = date('Y-m-d H:i:s');
    $_SESSION['user_initials'] = getUserInitials($name);
    $_SESSION['user_role'] = ROLE_TOURIST;

    header('Location: userprofile.php');
    exit;

} else {

    redirectAuthError('signup', 'Signup failed, please try again', $oldFields);
}
?>