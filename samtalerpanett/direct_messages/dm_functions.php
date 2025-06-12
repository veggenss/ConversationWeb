<?php
header('Content-Type: application/json');

require_once '../include/db.inc.php';
$mysqli = dbConnection();

//Ser om action er POST eller GET
$data = json_decode(file_get_contents("php://input"), true);

$action = $_GET['action'] ?? ($data['action'] ?? null);


if($action === 'get_user_id'){

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
        return;
    }
    else{
        $UsernameToUserId = ['success' => false, "response" => "reciverUser er udefinert"];
        echo json_encode($UsernameToUserId);
        return;
    }
}

elseif($action === 'createConversation'){
    
    // Oppretter ny conversation med reciverUserId og currentUserId (inloggete bruker)
    $newConversationResponse = ["success" => NULL, "response" => NULL];
    $newConversationUserData = json_decode(file_get_contents("php://input"), true); //Siden vi brukte post på å sende infoen må vi gjøre dette for å definere det

    if(!$newConversationUserData['user2_id'] || !$newConversationUserData['user1_id']){
        $newConversationReponse = ["success" => false, "response" => "En user id er ikke definert. \n user1_id: $user1_id \n user2_id: $user2_id"];
        echo json_encode($newConversationReponse);
        return;
    }
    else{
        $user1_id = $newConversationUserData['user1_id'];
        $user2_id = $newConversationUserData['user2_id'];
        $query = "INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE id = id";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $user1_id, $user2_id);
        $stmt->execute();
        $stmt->affected_rows === 1 ? $newConversationReponse = ["success" => true, "response" => "Opprettet conversation mellom $user1_id og $user2_id"] : $newConversationReponse = ["success" => false, "response" => "Du har allerede samtale med dene brukeren"]; 
        echo json_encode($newConversationReponse);
        return;
    }
}
elseif($action === 'loadConversationDiv'){

    //Laster in aktive conversations sånn at de er listet på sidepannelet 
    $loadConversationDivResponse = ["success" => NULL, "response" => NULL];
    $loadConversationDivData = json_decode(file_get_contents("php://input"), true);
    $user_id = $loadConversationDivData['user_id'];

    if(!$user_id){
        $loadConversationDivResponse = ["success" => false, "response" => "user_id er ikke definert"];
        echo json_encode($loadConversationDivResponse);
        return;
    }
    
    function user2NameById($mysqli, $user2_id){
        $query = "SELECT username FROM users WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $user2_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['username'];
    }

    $query = "SELECT id, user2_id FROM conversations WHERE user1_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();


    $conversations = [];

    while($row = $result->fetch_assoc()){

        $user2_id = $row['user2_id'];
        if(!isset($user2_id) || $user2_id === NULL){
            $loadConversationDivResponse = ["success" => false, "response" => "Kunne ikke finne user2_id"];
            echo json_encode($loadConversationDivResponse);
            return;
        }
        $user2_name = user2NameById($mysqli, $user2_id);


        $icon_query = "SELECT profile_picture FROM users WHERE id = ?";
        $icon_stmt = $mysqli->prepare($icon_query);
        $icon_stmt->bind_param("i", $user2_id);
        $icon_stmt->execute();
        $icon_result = $icon_stmt->get_result();
        $icon_data = $icon_result->fetch_assoc();


        $profile_picture = $icon_data['profile_picture'] ?? 'default.png';
        $profile_picture_url = '/projects/samtalerpanett/uploads/' . ltrim($profile_picture, '/');

        $conversations[] = [
            "conversation_id" => $row['id'],
            "recipientUsername" => $user2_name,
            "recipient_profile_icon" => $profile_picture_url
        ];

        $icon_stmt->close();
    }

    $stmt->close();
    
    if(count($conversations) > 0){
        $loadConversationDivResponse = ["success" => true, "response" => "Fant " . count($conversations) . " Samtaler", "conversations" => $conversations];
        echo json_encode($loadConversationDivResponse);
    }
    else{
        $loadConversationDivResponse = ['success' => false, "reponse" => "Ingen samtaler funnet"];
        echo json_encode($loadConversationDivResponse);
    }
    
}