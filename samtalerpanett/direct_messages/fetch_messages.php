<?php
session_start();
require '../include/db.inc.php';
header('Content-Type: application/json');

$conversation_id = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : NULL;

$sql = "SELECT user_id, message, sent_date FROM dm_messages WHERE conversation_id = ? ORDER BY sent_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows > 0){
    echo json_encode(['error' => "idfk, fant ikke rows ig"]);
    return;
}
$messages = [];
while($row = $result->fetch_assoc()){
    $messages[] = $row;
}

echo json_encode($messages);
?>