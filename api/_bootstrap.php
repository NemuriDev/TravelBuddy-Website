<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

/**
 * Every endpoint ends here — one exit path for both success and failure,
 * so the front end can always just check `data.ok`.
 */
function apiRespond($ok, $data = []) {
    echo json_encode(array_merge(['ok' => $ok], $data));
    exit;
}

function apiError($message, $status = 400) {
    http_response_code($status);
    apiRespond(false, ['error' => $message]);
}

/** Reads and JSON-decodes the request body once; POST endpoints share this shape. */
function apiReadJsonBody() {
    $body = json_decode(file_get_contents('php://input'), true);
    return is_array($body) ? $body : [];
}
