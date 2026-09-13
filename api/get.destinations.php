<?php
require_once __DIR__ . '/_bootstrap.php';

$db = getDBConnection();
if (!$db) {
    apiError('Database error', 500);
}

$stmt = $db->query("
    SELECT slug, municipality, name, description, location, category, tag, field_note, image_url
    FROM destinations
    ORDER BY created_at DESC
");

// Field names line up with the shape script.js's static `destinations`
// array already uses, so the frontend can merge these in directly.
$places = array_map(function ($row) {
    return [
        'id' => $row['slug'],
        'municipality' => $row['municipality'],
        'name' => $row['name'],
        'description' => $row['description'],
        'location' => $row['location'],
        'category' => $row['category'],
        'tag' => $row['tag'],
        'time' => $row['field_note'],
        'imageUrl' => $row['image_url'],
    ];
}, $stmt->fetchAll());

apiRespond(true, ['destinations' => $places]);