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
    $newConversationReponse = ["success" => NULL, "response" => NULL];
    $newConversationUserData = json_decode(file_get_contents("php://input"), true); //Siden vi brukte post på å sende infoen må vi gjøre dette for å definere det
    $user1_id = $newConversationUserData['user1_id'];
    $user2_id = $newConversationUserData['user2_id'];

    if(!$user1_id || !$user2_id){
        $newConversationReponse = ["success" => false, "response" => "En user id er ikke definert. \n user1_id: $user1_id \n user2_id: $user2_id"];
        echo json_encode($newConversationReponse);
        return;
    }
    else{
        $query = "INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $user1_id, $user2_id);
        $stmt->execute() ? $newConversationReponse = ["success" => true, "response" => "Opprettet conversation mellom $user1_id og $user2_id"] : $newConversationReponse = ["success" => false, "response" => "Kunne ikke INSERTe inn i databasen"];   
        echo json_encode($newConversationReponse);
        return;
    }
}

?>