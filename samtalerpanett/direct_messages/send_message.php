<?php
session_start();
require '../include/db.inc.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$conversation_id = (int)$data['conversation_id'];
$message = $data['message'];

// finner den vedunderlige mottakeren
$sql = "SELECT user1_id, user2_id FROM dm_conversations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$to_user_id = ($row['user1_id'] == $user_id) ? $row['user2_id'] : $row['user1_id'];

$sql = "INSERT INTO dm_messages (conversation_id, user_id, to_user_id, message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $conversation_id, $user_id, $to_user_id, $message);
$success = $stmt->execute();

echo json_encode(['success' => $success]);

?>