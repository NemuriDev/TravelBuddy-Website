<?php
require_once __DIR__ . '/_bootstrap.php';

$db = getDBConnection();
if (!$db) {
    apiError('Database error', 500);
}

// One row per destination, whether or not it has any reviews yet.
$stmt = $db->query("
    SELECT
        destinations.slug,
        AVG(reviews.rating) AS avg_rating,
        COUNT(reviews.id) AS review_count
    FROM destinations
    LEFT JOIN reviews ON reviews.destination_id = destinations.id
    GROUP BY destinations.id
");

$ratings = [];
foreach ($stmt->fetchAll() as $row) {
    $count = (int) $row['review_count'];
    $ratings[$row['slug']] = [
        'rating' => $count > 0 ? round((float) $row['avg_rating'], 1) : null,
        'count' => $count,
    ];
}

apiRespond(true, ['ratings' => $ratings]);