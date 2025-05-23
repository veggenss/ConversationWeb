<?php
include 'include/db.inc.php';

if(isset($_GET['token'])){
    $token = $_GET['token'];

    $sql = "SELECT * FROM users WHERE email_verification_token = ? AND email_verified = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $sql = "UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        echo "E-posten din er nå bekreftet! Du kan logge inn";
    }
    else{
        echo "Ugyldig eller utløpt verifikasjonslink";
    }
}
else{
    echo "Ingen verifikasjonstoken funnet";
}
?>