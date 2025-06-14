<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$logPath = __DIR__ . '/global_chat_log.txt';

if (!file_exists($logPath)) {
    echo json_encode([]);
    exit;
}

$lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$messages = array_filter(array_map(function($line) {
    $decoded = json_decode($line, true);
    return is_array($decoded) ? $decoded : null;
}, $lines));

echo json_encode(array_values($messages));
?>