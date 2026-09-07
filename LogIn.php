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

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header('Location: auth.php?error=Please fill in all fields');
    exit;
}

$db = getDBConnection();

if (!$db) {
    header('Location: auth.php?error=Database error');
    exit;
}

// Get user information from database
$stmt = $db->prepare("
    SELECT id, name, email, password, location, role, created_at
    FROM users
    WHERE email = ?
");

$stmt->execute([$email]);
$user = $stmt->fetch();

// Check login credentials
if ($user && password_verify($password, $user['password'])) {

    // Set session information
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_location'] = $user['location'];
    $_SESSION['user_created_at'] = $user['created_at'];
    $_SESSION['user_initials'] = getUserInitials($user['name']);

    // Get role directly from database
    $_SESSION['user_role'] = $user['role'];

    // Go to profile after successful login
    header('Location: userprofile.php');
    exit;

} else {

    // Login failed
    header('Location: auth.php?error=Invalid email or password');
    exit;
}
?>