<?php
require_once '../include/db.inc.php';

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
    $stmt->bind_result($file_name);
    $stmt->fetch();

    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/projects/samtalerpanett/uploads/' . $file_name;

    if (file_exists($full_path)) {
        $mime_type = mime_content_type($full_path);
        header("Content-Type: $mime_type");
        readfile($full_path);
        exit;
    }
}
else{
    header("Content-Type: image/png");
    readfile("../uploads/default.png");
}
?>