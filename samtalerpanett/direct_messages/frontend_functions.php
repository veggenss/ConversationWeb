<?php
header('Content-Type: application/json');

require_once '../include/db.inc.php';
$mysqli = dbConnection();



// Når man skal lage ny conversation, tar input brukernavn og finner id til brukeren

if($_GET['reciverUser']){
    $reciverUser = $_GET['reciverUser']; 
    $UsernameToUserId = ['success' => NULL, "response" => NULL];

    $query="SELECT id FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $reciverUser);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $stmt->bind_result($reciverUserId);
        $stmt->fetch();
        $UsernameToUserId = ["success" => true, "response" => "Id funnet", "reciverUserId" => $reciverUserId];
        $stmt->close();
    }
    else{
        $UsernameToUserId = ["success" => false, "response" => "Kunne ikke finne id med brukernavn \"$reciverUser\" i db"];
    }
    echo json_encode($UsernameToUserId);
}
else{
    $UsernameToUserId = ['success' => false, "response" => "reciverUser er udefinert"];
    echo json_encode($UsernameToUserId);
}
?>