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

function profileError($message) {
    header('Location: userprofile.php?' . http_build_query(['error' => $message]));
    exit;
}

$user = getCurrentUser();
$userId = $user['id'];

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$location = trim($_POST['location'] ?? '');
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// =========================================
// VALIDATE TEXT FIELDS FIRST
// Nothing is written to disk until every check has passed, so a
// rejected save never leaves an orphaned photo behind.
// =========================================

if (empty($name) || empty($email)) {
    profileError('Name and email are required');
}

if (mb_strlen($name) > MAX_NAME_LENGTH) {
    profileError('Name must be ' . MAX_NAME_LENGTH . ' characters or fewer');
}

if (mb_strlen($email) > MAX_EMAIL_LENGTH) {
    profileError('Email must be ' . MAX_EMAIL_LENGTH . ' characters or fewer');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    profileError('Invalid email');
}

if (mb_strlen($location) > MAX_LOCATION_LENGTH) {
    profileError('Location must be ' . MAX_LOCATION_LENGTH . ' characters or fewer');
}

if (isset($_POST['bio']) && mb_strlen(trim($_POST['bio'])) > MAX_BIO_LENGTH) {
    profileError('Bio must be ' . MAX_BIO_LENGTH . ' characters or fewer');
}

if (!empty($newPassword)) {
    if ($newPassword !== $confirmPassword) {
        profileError('Passwords do not match');
    }
    if (strlen($newPassword) < 8) {
        profileError('Password must be at least 8 characters');
    }
    if (strlen($newPassword) > MAX_PASSWORD_BYTES) {
        profileError('Password must be ' . MAX_PASSWORD_BYTES . ' characters or fewer');
    }
}

$db = getDBConnection();
if (!$db) {
    profileError('Database error');
}

$stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
$stmt->execute([$email, $userId]);
if ($stmt->fetch()) {
    profileError('That email is already in use');
}

// =========================================
// PROFILE PHOTO UPLOAD
// =========================================

$profilePhoto = null;
$savedPhotoPath = null;

if (
    isset($_FILES['profile_photo']) &&
    $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        profileError('Photo upload failed');
    }

    // Maximum 2MB
    if ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
        profileError('Photo must be less than 2MB');
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $mimeType = mime_content_type($_FILES['profile_photo']['tmp_name']);

    if (!isset($allowedTypes[$mimeType])) {
        profileError('Only JPG, PNG or WEBP images are allowed');
    }

    $uploadDirectory = __DIR__ . '/uploads/profile/';

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0755, true);
    }

    $fileName = 'user_' . $userId . '_' . time() . '.' . $allowedTypes[$mimeType];
    $savedPhotoPath = $uploadDirectory . $fileName;

    if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $savedPhotoPath)) {
        profileError('Could not save profile photo');
    }

    $profilePhoto = 'uploads/profile/' . $fileName;
}

// =========================================
// UPDATE USER
// =========================================

$setClauses = ['name = ?', 'email = ?', 'location = ?'];
$params = [$name, $email, $location];

// Only the "Edit Profile" form sends a bio. Saving from Settings must
// leave the existing bio alone instead of overwriting it with ''.
if (isset($_POST['bio'])) {
    $setClauses[] = 'bio = ?';
    $params[] = trim($_POST['bio']);
}

if ($profilePhoto !== null) {
    $setClauses[] = 'profile_photo = ?';
    $params[] = $profilePhoto;
}

if (!empty($newPassword)) {
    $setClauses[] = 'password = ?';
    $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
}

$params[] = $userId;

// The DB is the source of truth for the current photo; read it before
// the UPDATE overwrites it so the old file can be removed afterwards.
$oldPhoto = null;
if ($profilePhoto !== null) {
    $stmt = $db->prepare('SELECT profile_photo FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $oldPhoto = $stmt->fetchColumn();
}

try {
    $stmt = $db->prepare(
        'UPDATE users SET ' . implode(', ', $setClauses) . ' WHERE id = ?'
    );
    $stmt->execute($params);
} catch (PDOException $e) {
    error_log('Profile update failed: ' . $e->getMessage());
    if ($savedPhotoPath !== null) {
        unlink($savedPhotoPath);
    }
    profileError('Could not save changes, please try again');
}

// The new photo is committed, so the previous file is now unreferenced.
if ($oldPhoto) {
    $oldPhotoPath = __DIR__ . '/' . $oldPhoto;
    if (is_file($oldPhotoPath)) {
        unlink($oldPhotoPath);
    }
}

// Update session variables
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_location'] = $location;
$_SESSION['user_initials'] = getUserInitials($name);

if ($profilePhoto !== null) {
    $_SESSION['user_profile_photo'] = $profilePhoto;
}

header('Location: userprofile.php?updated=1');
exit;