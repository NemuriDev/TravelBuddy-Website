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
    apiError('Your session expired. Please refresh the page and try again.', 403);
}

const GALLERY_SLOTS = 3;

/**
 * Validates and stores one uploaded photo from $_FILES[$field].
 * Returns its public path, or null when nothing was uploaded in that slot.
 */
function saveUploadedImage($field, $slug) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mimeType = mime_content_type($_FILES[$field]['tmp_name']);

    if (!isset($allowedTypes[$mimeType])) {
        apiError('Photo must be JPG, PNG, or WEBP');
    }

    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
        apiError('Photo must be less than 5MB');
    }

    $uploadDir = __DIR__ . '/../uploads/destinations/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // The field name is part of the file name so several photos saved
    // in the same second can't overwrite each other.
    $fileName = $slug . '_' . $field . '_' . time() . '.' . $allowedTypes[$mimeType];

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $fileName)) {
        apiError('Could not save the uploaded photo', 500);
    }

    return 'uploads/destinations/' . $fileName;
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
$mapsUrl = trim($_POST['maps_url'] ?? '');
$description = trim($_POST['description'] ?? '');
$imageUrl = trim($_POST['image_url'] ?? '');
$ecoGuidelines = trim($_POST['eco_guidelines'] ?? '');

if ($slug === '' || $name === '' || $municipality === '') {
    apiError('Name, slug, and municipality are required');
}

if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    apiError('Slug can only contain lowercase letters, numbers, and hyphens');
}

if (
    mb_strlen($slug) > MAX_DESTINATION_SLUG_LENGTH ||
    mb_strlen($name) > MAX_DESTINATION_NAME_LENGTH ||
    mb_strlen($municipality) > MAX_DESTINATION_SHORT_LENGTH ||
    mb_strlen($fieldNote) > MAX_DESTINATION_SHORT_LENGTH ||
    mb_strlen($location) > MAX_DESTINATION_LOCATION_LENGTH ||
    mb_strlen($description) > MAX_DESTINATION_DESCRIPTION_LENGTH
) {
    apiError('One of the text fields is too long');
}

if (!in_array($category, DESTINATION_CATEGORIES, true)) {
    apiError('Choose a category from the list');
}

if (!in_array($tag, DESTINATION_TAGS, true)) {
    apiError('Choose a tag from the list');
}

if (mb_strlen($mapsUrl) > MAX_URL_LENGTH || mb_strlen($imageUrl) > MAX_URL_LENGTH) {
    apiError('Links must be ' . MAX_URL_LENGTH . ' characters or fewer');
}

// Checked before any photo is saved, so a rejected form leaves no files behind.
for ($i = 1; $i <= GALLERY_SLOTS; $i++) {
    if (mb_strlen(trim($_POST["gallery_url_$i"] ?? '')) > MAX_URL_LENGTH) {
        apiError('Links must be ' . MAX_URL_LENGTH . ' characters or fewer');
    }
}

if (mb_strlen($ecoGuidelines) > 1000) {
    apiError('Ecological guidelines must be 1000 characters or fewer');
}

if ($mapsUrl !== '' && (!filter_var($mapsUrl, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $mapsUrl))) {
    apiError('Google Maps link must be a valid URL');
}

// An uploaded photo takes priority over a pasted URL. The main photo is
// the large image on the card; the gallery holds up to GALLERY_SLOTS more.
$imageUrl = saveUploadedImage('image_file', $slug) ?? $imageUrl;

$gallery = [];
for ($i = 1; $i <= GALLERY_SLOTS; $i++) {
    $photo = saveUploadedImage("gallery_file_$i", $slug) ?? trim($_POST["gallery_url_$i"] ?? '');
    if ($photo !== '') {
        $gallery[] = $photo;
    }
}
$galleryJson = json_encode($gallery);

$db = getDBConnection();
if (!$db) {
    apiError('Database error', 500);
}

$params = [$slug, $name, $municipality, $description, $location, $mapsUrl, $category, $tag, $fieldNote, $imageUrl, $galleryJson, $ecoGuidelines];

try {
    if ($originalSlug !== '') {
        $stmt = $db->prepare("
            UPDATE destinations
            SET slug = ?, name = ?, municipality = ?, description = ?, location = ?, maps_url = ?, category = ?, tag = ?, field_note = ?, image_url = ?, gallery_images = ?, eco_guidelines = ?
            WHERE slug = ?
        ");
        $stmt->execute([...$params, $originalSlug]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO destinations (slug, name, municipality, description, location, maps_url, category, tag, field_note, image_url, gallery_images, eco_guidelines)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
        'mapsUrl' => $mapsUrl,
        'category' => $category,
        'tag' => $tag,
        'time' => $fieldNote,
        'imageUrl' => $imageUrl,
        'gallery' => $gallery,
        'ecoGuidelines' => $ecoGuidelines,
    ],
]);