<?php
require_once __DIR__ . '/_bootstrap.php';

if (!isLoggedIn()) {
    apiError('Not logged in', 401);
}

$body = apiReadJsonBody();

if (!isset($body['csrf_token']) || !verifyCSRFToken($body['csrf_token'])) {
    apiError('Your session expired. Please refresh the page and try again.', 403);
}

$slug = trim($body['slug'] ?? '');
if ($slug === '') {
    apiError('Missing slug');
}

$db = getDBConnection();
if (!$db) {
    apiError('Database error', 500);
}

$user = getCurrentUser();

$stmt = $db->prepare("SELECT id FROM destinations WHERE slug = ?");
$stmt->execute([$slug]);
$destination = $stmt->fetch();

if (!$destination) {
    apiError('Unknown destination', 404);
}

$destinationId = $destination['id'];

// Same delete-then-insert toggle as toggle_favorite.php — the
// UNIQUE(user_id, destination_id) key keeps this race-safe.
$stmt = $db->prepare("DELETE FROM visits WHERE user_id = ? AND destination_id = ?");
$stmt->execute([$user['id'], $destinationId]);

if ($stmt->rowCount() > 0) {
    apiRespond(true, ['visited' => false]);
}

$stmt = $db->prepare("INSERT IGNORE INTO visits (user_id, destination_id) VALUES (?, ?)");
$stmt->execute([$user['id'], $destinationId]);

apiRespond(true, ['visited' => true]);