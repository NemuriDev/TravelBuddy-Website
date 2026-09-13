<?php
require_once __DIR__ . '/_bootstrap.php';

if (!isLoggedIn()) {
    apiError('Not logged in', 401);
}

$user = getCurrentUser();
if ($user['role'] !== ROLE_ADMIN) {
    apiError('Admin access required', 403);
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

try {
    $stmt = $db->prepare("DELETE FROM destinations WHERE slug = ?");
    $stmt->execute([$slug]);
} catch (PDOException $e) {
    error_log('admin_delete_destination failed: ' . $e->getMessage());
    apiError('Could not delete — it may have reviews or saves attached', 500);
}

apiRespond(true);