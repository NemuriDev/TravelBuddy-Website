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

// Validate required fields
if (empty($name) || empty($email) || empty($password)) {
    header('Location: auth.php?error=All required fields must be filled');
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: auth.php?error=Invalid email address');
    exit;
}

// Validate password
if (strlen($password) < 8) {
    header('Location: auth.php?error=Password must be at least 8 characters');
    exit;
}

// Connect to database
$db = getDBConnection();

if (!$db) {
    header('Location: auth.php?error=Database error');
    exit;
}

// Check if email already exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    header('Location: auth.php?error=Email already registered');
    exit;
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

    header('Location: auth.php?error=Signup failed, please try again');
    exit;
}
?>