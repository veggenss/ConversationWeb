<?php
include 'include/db.inc.php';
include 'include/navbar.php';
include 'include/sidebar.php';

// variabel for versjonsnummer
$version = "Beta v0.0.1";

session_start();
if (!isset($_SESSION['user_id'])) {
    // hvis brukeren ikke er logget inn, redirect til login
    header("Location: login.php");
    exit();
}

// logger in
if(!isset($_SESSION['user_id'])){
    if($user_id !== null){
        $_SESSION['user_id'] = $user_id;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title></title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="CSS/mainStyle.css" />
    <!-- logoet til toner på nett som favicon !! LAG SAMTALER PÅ NETT LOGO OG LEGG DET TIL HER ISTEDET FOR TONER PÅ NETT LOGO - Isak 23.05.25!!-->
    <link rel="icon" href="assets/icons/logo.png" />
    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
</head>
<body>
    <div class="activity-viewer">
        <div class="activity-list">
            <h3>Aktive venner</h3>

        </div>
    </div>
    <div class="container">
        <h1 id="header">Samtaler på Nett</h1>

        <p>Her kommer den snart berømte smsappen, Samtaler på Nett.</p>
        <p><a href="logout.php">Log ut test</a></p>
    </div>


</body>
</html>
