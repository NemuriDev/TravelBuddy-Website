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
    SELECT reviews.user_id, reviews.rating, reviews.comment, reviews.created_at, users.name, users.profile_photo
    FROM reviews
    INNER JOIN destinations ON reviews.destination_id = destinations.id
    INNER JOIN users ON reviews.user_id = users.id
    WHERE destinations.slug = ?
    ORDER BY reviews.created_at DESC
");
$stmt->execute([$slug]);
$rows = $stmt->fetchAll();

$currentUserId = isLoggedIn() ? getCurrentUser()['id'] : null;
$mine = null;

$reviews = array_map(function ($row) use ($currentUserId, &$mine) {
    $review = [
        'name' => $row['name'],
        'initials' => getUserInitials($row['name']),
        'photo' => $row['profile_photo'],
        'date' => date('F Y', strtotime($row['created_at'])),
        'rating' => (int) $row['rating'],
        'text' => $row['comment'],
    ];

    // Flag the viewer's own review 
    if ($currentUserId !== null && (int) $row['user_id'] === (int) $currentUserId) {
        $mine = [
            'rating' => (int) $row['rating'],
            'text' => $row['comment'],
        ];
    }

    return $review;
}, $rows);

apiRespond(true, ['reviews' => $reviews, 'mine' => $mine]);
