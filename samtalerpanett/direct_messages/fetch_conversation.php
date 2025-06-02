<?php
session_start();
require '../include/db.inc.php';

header('Content-Type: application/json');
$user_id = $_SESSION['user_id'];


//yippie autistiks sql query incomming :D :D :D :D
$sql = "SELECT dc.id AS conversation_id, 
        IF (dc.user1_id = ?, dc.user2_id, dc.user1_id) AS other_user_id, 
        u.username AS other_username, 
        (SELECT message FROM dm_messages WHERE conversation_id = dc.id ORDER BY sent_date DESC LIMIT 1) AS last_message, 
        (SELECT sent_date FROM dm_messages WHERE conversation_id = dc.id ORDER BY sent_date DESC LIMIT 1) AS last_sent 
        FROM dm_conversations dc 
        JOIN users u ON u.id = IF(dc.user1_id = ?, dc.user2_id, dc.user1_id) 
        WHERE ? IN (dc.user1_id, dc.user2_id) 
        ORDER BY last_sent DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conversation = [];
while($row = $result->fetch_assoc()) {
    $conversation[] = $row;
}

echo json_encode($conversation);
?>