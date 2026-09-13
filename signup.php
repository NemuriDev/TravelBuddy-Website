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
$oldFields = ['name' => $name, 'email' => $email, 'location' => $location];

// Validate required fields
if (empty($name) || empty($email) || empty($password)) {
    redirectAuthError('signup', 'All required fields must be filled', $oldFields);
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectAuthError('signup', 'Invalid email address', $oldFields);
}

// Validate password
if (strlen($password) < 8) {
    redirectAuthError('signup', 'Password must be at least 8 characters', $oldFields);
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