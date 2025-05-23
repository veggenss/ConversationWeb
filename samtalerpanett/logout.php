<?php
include 'include/db.inc.php';
function deleteRememberMeToken(mysqli $conn, string $selector): void{
    $stmt = $conn->prepare('DELETE FROM user_tokens WHERE selector = ?');
    $stmt->bind_param("s",$selector);
    $stmt->execute();
    setcookie('remember_me', '', time() - 3600, "/", "", true, true);
}

if(isset($_SESSION['user_id'])){
    deleteRememberMeToken($conn, $selector);
    // nå kan man se hvis den faktisk fjerner remember me
    error_log('Deleted remember me');
}

// sjekker at den ødeleger session
error_log('Destroyed session');
session_unset();
session_destroy();

header('Location: login.php');
exit();
?>