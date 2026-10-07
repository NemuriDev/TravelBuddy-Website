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
    FROM visits
    INNER JOIN destinations ON visits.destination_id = destinations.id
    WHERE visits.user_id = ?
");
$stmt->execute([$user['id']]);

$visited = array_column($stmt->fetchAll(), 'slug');

apiRespond(true, ['visited' => $visited]);