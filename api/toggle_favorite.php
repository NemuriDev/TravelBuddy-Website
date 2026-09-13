<?php
require_once __DIR__ . '/_bootstrap.php';

if (!isLoggedIn()) {
    apiError('Not logged in', 401);
}

$body = apiReadJsonBody();

if (!isset($body['csrf_token']) || !verifyCSRFToken($body['csrf_token'])) {
    apiError('Invalid CSRF token', 403);
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

// Try removing the favorite first; if nothing was removed, it wasn't
// favorited yet, so add it. The UNIQUE(user_id, destination_id) key
// means this can't end up in an inconsistent state under a race.
$stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND destination_id = ?");
$stmt->execute([$user['id'], $destinationId]);

if ($stmt->rowCount() > 0) {
    apiRespond(true, ['favorited' => false]);
}

$stmt = $db->prepare("INSERT INTO favorites (user_id, destination_id) VALUES (?, ?)");
$stmt->execute([$user['id'], $destinationId]);

apiRespond(true, ['favorited' => true]);
