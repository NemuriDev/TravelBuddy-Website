<?php
require_once __DIR__ . '/_bootstrap.php';

if (!isLoggedIn()) {
    apiError('Not logged in', 401);
}

$db = getDBConnection();
if (!$db) {
    apiError('Database error', 500);
}

$user = getCurrentUser();

$stmt = $db->prepare("
    SELECT destinations.slug
    FROM favorites
    INNER JOIN destinations ON favorites.destination_id = destinations.id
    WHERE favorites.user_id = ?
");
$stmt->execute([$user['id']]);

$favorites = array_column($stmt->fetchAll(), 'slug');

apiRespond(true, ['favorites' => $favorites]);
