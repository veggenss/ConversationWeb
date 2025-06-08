<?php
require '../include/db.inc.php';

if(!isset($_GET['other_user_id'])){
    http_response_code(400);
    exit("Missing user ID");
}

$other_user_id = intval($_GET['other_user_id']);

$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $other_user_id);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0){
    $stmt->bind_result($blob);
    $stmt->fetch();

    header('Content-Type: image/jpeg/png/gif');
    $_GET['other_profile_picture'] = $blob;
    json_encode($blob);
}
else{
    header("Content-Type: image/png");
    readfile("default.png");
}
?>