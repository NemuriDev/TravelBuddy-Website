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

$user = getCurrentUser();
$userId = $user['id'];

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$location = trim($_POST['location'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$db = getDBConnection();
if (!$db) {
    header('Location: userprofile.php?error=Database error');
    exit;
}

// =========================================
// PROFILE PHOTO UPLOAD
// =========================================

$profilePhoto = null;

if (
    isset($_FILES['profile_photo']) &&
    $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        header('Location: userprofile.php?error=Photo upload failed');
        exit;
    }

    // Maximum 2MB
    if ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
        header('Location: userprofile.php?error=Photo must be less than 2MB');
        exit;
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $mimeType = mime_content_type($_FILES['profile_photo']['tmp_name']);

    if (!isset($allowedTypes[$mimeType])) {
        header('Location: userprofile.php?error=Only JPG, PNG or WEBP images are allowed');
        exit;
    }

    $extension = $allowedTypes[$mimeType];

    $uploadDirectory = __DIR__ . '/uploads/profile/';

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0755, true);
    }

    $fileName = 'user_' . $userId . '_' . time() . '.' . $extension;

    $filePath = $uploadDirectory . $fileName;

    if (!move_uploaded_file(
        $_FILES['profile_photo']['tmp_name'],
        $filePath
    )) {
        header('Location: userprofile.php?error=Could not save profile photo');
        exit;
    }

    $profilePhoto = 'uploads/profile/' . $fileName;
}


if (empty($name) || empty($email)) {
    header('Location: userprofile.php?error=Name and email are required');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: userprofile.php?error=Invalid email');
    exit;
}

$db = getDBConnection();
if (!$db) {
    header('Location: userprofile.php?error=Database error');
    exit;
}

// Update user info
$setClauses = [
    'name = ?',
    'email = ?',
    'location = ?',
    'bio = ?'
];
$params = [$name, $email, $location, $bio];

if ($profilePhoto !== null) {
    $setClauses[] = 'profile_photo = ?';
    $params[] = $profilePhoto;
}

if (!empty($newPassword)) {
    if ($newPassword !== $confirmPassword) {
        header('Location: userprofile.php?error=Passwords do not match');
        exit;
    }
    if (strlen($newPassword) < 8) {
        header('Location: userprofile.php?error=Password must be at least 8 characters');
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $setClauses[] = 'password = ?';
    $params[] = $hashedPassword;
}

$params[] = $userId;

$stmt = $db->prepare(
    'UPDATE users SET ' . implode(', ', $setClauses) . ' WHERE id = ?'
);
$stmt->execute($params);

// Update session variables
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_location'] = $location;
$_SESSION['user_initials'] = getUserInitials($name);

header('Location: userprofile.php?updated=1');
exit;
