<?php
require_once __DIR__ . '/_bootstrap.php';

if (!isLoggedIn()) {
    apiError('Not logged in', 401);
}

$user = getCurrentUser();
if ($user['role'] !== ROLE_ADMIN) {
    apiError('Admin access required', 403);
}

// This form ships a file, so it's multipart — read $_POST directly
// instead of apiReadJsonBody(), same as update_profile.php does.
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    apiError('Invalid CSRF token', 403);
}

// Present (and non-empty) only when editing an existing place; empty
// when adding a new one. This is how the update-vs-insert path here.
$originalSlug = trim($_POST['original_slug'] ?? '');

$slug = trim($_POST['slug'] ?? '');
$name = trim($_POST['name'] ?? '');
$municipality = trim($_POST['municipality'] ?? '');
$category = trim($_POST['category'] ?? '');
$tag = trim($_POST['tag'] ?? '');
$fieldNote = trim($_POST['field_note'] ?? '');
$location = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');
$imageUrl = trim($_POST['image_url'] ?? '');

if ($slug === '' || $name === '' || $municipality === '') {
    apiError('Name, slug, and municipality are required');
}

if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    apiError('Slug can only contain lowercase letters, numbers, and hyphens');
}

// An uploaded photo takes priority over a pasted URL.
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mimeType = mime_content_type($_FILES['image_file']['tmp_name']);

    if (!isset($allowedTypes[$mimeType])) {
        apiError('Photo must be JPG, PNG, or WEBP');
    }

    if ($_FILES['image_file']['size'] > 5 * 1024 * 1024) {
        apiError('Photo must be less than 5MB');
    }

    $uploadDir = __DIR__ . '/../uploads/destinations/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = $slug . '_' . time() . '.' . $allowedTypes[$mimeType];

    if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $fileName)) {
        apiError('Could not save the uploaded photo', 500);
    }

    $imageUrl = 'uploads/destinations/' . $fileName;
}

$db = getDBConnection();
if (!$db) {
    apiError('Database error', 500);
}

$params = [$slug, $name, $municipality, $description, $location, $category, $tag, $fieldNote, $imageUrl];

try {
    if ($originalSlug !== '') {
        $stmt = $db->prepare("
            UPDATE destinations
            SET slug = ?, name = ?, municipality = ?, description = ?, location = ?, category = ?, tag = ?, field_note = ?, image_url = ?
            WHERE slug = ?
        ");
        $stmt->execute([...$params, $originalSlug]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO destinations (slug, name, municipality, description, location, category, tag, field_note, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute($params);
    }
} catch (PDOException $e) {
    error_log('admin_save_destination failed: ' . $e->getMessage());
    $isDuplicate = str_contains($e->getMessage(), 'Duplicate');
    apiError($isDuplicate ? 'That slug is already in use' : 'Could not save this place', 500);
}

apiRespond(true, [
    'destination' => [
        'id' => $slug,
        'municipality' => $municipality,
        'name' => $name,
        'description' => $description,
        'location' => $location,
        'category' => $category,
        'tag' => $tag,
        'time' => $fieldNote,
        'imageUrl' => $imageUrl,
    ],
]);