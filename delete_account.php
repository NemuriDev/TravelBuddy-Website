<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: userprofile.php');
    exit;
}

if (!isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    die('Invalid CSRF token');
}

function deleteError($message) {
    header('Location: userprofile.php?' . http_build_query(['error' => $message]));
    exit;
}

$userId = getCurrentUser()['id'];
$password = $_POST['current_password'] ?? '';

$db = getDBConnection();
if (!$db) {
    deleteError('Database error');
}

// Role and password come from the DB, not the session, so a stale
// session can't bypass either check.
$stmt = $db->prepare('SELECT password, role, profile_photo FROM users WHERE id = ?');
$stmt->execute([$userId]);
$account = $stmt->fetch();

if (!$account) {
    deleteError('Account not found');
}

// An admin deleting themselves could leave the site with no admin.
if ($account['role'] === ROLE_ADMIN) {
    deleteError('Admin accounts cannot be deleted here');
}

if ($password === '' || !password_verify($password, $account['password'])) {
    deleteError('Incorrect password');
}

// Explicit deletes in one transaction, so it works whether or not the
// foreign keys cascade. Any failure rolls everything back.
try {
    $db->beginTransaction();
    foreach (['favorites', 'visits', 'reviews'] as $table) {
        $db->prepare("DELETE FROM $table WHERE user_id = ?")->execute([$userId]);
    }
    $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
    $db->commit();
} catch (PDOException $e) {
    $db->rollBack();
    error_log('Account deletion failed: ' . $e->getMessage());
    deleteError('Could not delete your account, please try again');
}

// The account is gone, so its photo file is now unreferenced.
if ($account['profile_photo']) {
    $photoPath = __DIR__ . '/' . $account['profile_photo'];
    if (is_file($photoPath)) {
        unlink($photoPath);
    }
}

session_destroy();
header('Location: home.php');
exit;