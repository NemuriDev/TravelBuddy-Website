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
$rating = (int) ($body['rating'] ?? 0);
$comment = trim($body['comment'] ?? '');

if ($slug === '' || $comment === '') {
    apiError('Missing slug or comment');
}

if ($rating < 1 || $rating > 5) {
    apiError('Rating must be between 1 and 5');
}

if (mb_strlen($comment) > 1000) {
    apiError('Review must be 1000 characters or fewer');
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

// UNIQUE(user_id, destination_id) means a second review from the same
// visitor updates their existing one instead of creating a duplicate.
$stmt = $db->prepare("
    INSERT INTO reviews (user_id, destination_id, rating, comment)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)
");
$stmt->execute([$user['id'], $destination['id'], $rating, $comment]);

apiRespond(true);