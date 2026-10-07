<?php
require_once __DIR__ . '/_bootstrap.php';

$db = getDBConnection();
if (!$db) {
    apiError('Database error', 500);
}

$stmt = $db->query("
    SELECT slug, municipality, name, description, location, maps_url, category, tag, field_note, image_url, gallery_images, eco_guidelines
    FROM destinations
    ORDER BY id ASC
");

// Field names line up with the shape script.js's `destinations`
$places = array_map(function ($row) {
    return [
        'id' => $row['slug'],
        'municipality' => $row['municipality'],
        'name' => $row['name'],
        'description' => $row['description'],
        'location' => $row['location'],
        'mapsUrl' => $row['maps_url'],
        'category' => $row['category'],
        'tag' => $row['tag'],
        'time' => $row['field_note'],
        'imageUrl' => $row['image_url'],
        'gallery' => json_decode($row['gallery_images'] ?? '[]', true),
        'ecoGuidelines' => $row['eco_guidelines'] ?? '',
    ];
}, $stmt->fetchAll());

apiRespond(true, ['destinations' => $places]);
