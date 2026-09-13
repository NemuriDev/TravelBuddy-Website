<?php
require_once __DIR__ . '/_bootstrap.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    apiError('Missing slug');
}

$db = getDBConnection();
if (!$db) {
    apiError('Database error', 500);
}

$stmt = $db->prepare("
    SELECT reviews.rating, reviews.comment, reviews.created_at, users.name
    FROM reviews
    INNER JOIN destinations ON reviews.destination_id = destinations.id
    INNER JOIN users ON reviews.user_id = users.id
    WHERE destinations.slug = ?
    ORDER BY reviews.created_at DESC
");
$stmt->execute([$slug]);
$rows = $stmt->fetchAll();

$reviews = array_map(function ($row) {
    return [
        'name' => $row['name'],
        'initials' => getUserInitials($row['name']),
        'date' => date('F Y', strtotime($row['created_at'])),
        'rating' => (int) $row['rating'],
        'text' => $row['comment'],
    ];
}, $rows);

// If the visitor is logged in and already reviewed this place, hand
// their own rating/comment back separately so the review form can
// open pre-filled instead of blank — editing becomes visible instead
// of a second submission silently overwriting the first.
$mine = null;
if (isLoggedIn()) {
    $user = getCurrentUser();
    $stmt = $db->prepare("
        SELECT reviews.rating, reviews.comment
        FROM reviews
        INNER JOIN destinations ON reviews.destination_id = destinations.id
        WHERE destinations.slug = ? AND reviews.user_id = ?
    ");
    $stmt->execute([$slug, $user['id']]);
    $row = $stmt->fetch();
    if ($row) {
        $mine = [
            'rating' => (int) $row['rating'],
            'text' => $row['comment'],
        ];
    }
}

apiRespond(true, ['reviews' => $reviews, 'mine' => $mine]);