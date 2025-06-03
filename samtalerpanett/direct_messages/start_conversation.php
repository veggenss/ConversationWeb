<?php
session_start();
require '../include/db.inc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Parse input safely
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$other_user_id = (int)$data['user_id'];

$sql = "SELECT id FROM dm_conversations WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    $stmt->bind_result($conversation_id);
    $stmt->fetch();
    echo json_encode(['conversation_id' => $conversation_id]);
}
else{
    $stmt = $conn->prepare("INSERT INTO dm_conversations (user1_id, user2_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $current_user_id, $other_user_id);
    $stmt->execute();
    echo json_encode(['conversation_id' => $conn->insert_id]);
}
?>