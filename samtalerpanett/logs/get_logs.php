<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$logPath = __DIR__ . '/global/global_chat_log.txt';

if(!file_exists($logPath)){
    echo json_encode(["error" => "File does not exist", "path" => realpath($logPath)]);
    exit;
}

$lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$parsed = [];

foreach ($lines as $line) {
    $json = json_decode($line, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $parsed[] = ["error" => json_last_error_msg(), "raw" => $line];
    } else {
        $parsed[] = $json;
    }
}

echo json_encode([
    "lines_count" => count($lines),
    "messages" => $parsed
]);
