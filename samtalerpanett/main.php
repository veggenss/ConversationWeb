<?php
session_start();
include 'functions.php';

if(isset($_COOKIE['not_verified'])){
    header("Location: login.php");
}

if(isset($_COOKIE['password_token_set'])){
    header("Location: login.php");
}
// variabel for versjonsnummer
$version = "Beta v0.0.1";

if (!isset($_SESSION['user_id'])) {
    // hvis brukeren ikke er logget inn, redirect til login
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler På Nett | Main</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="css/mainStyle.css" />
    <link rel="icon" href="assets/icons/logo.ico" />
    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

    <!-- Open Graph meta-tagger -->
    <meta property="og:title" content="Samtaler på Nett <?php echo $version; ?>">
    <meta property="og:description" content="Samtaler på Nett er et sted på nett hvor du kan ha samtaler.">
    <meta property="og:image" content="https://isak.brunhenriksen.no/Pictures/samtalelogo.png">
    <meta property="og:url" content="https://isak.brunhenriksen.no/samtalerpanett">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="no_NO">
    <meta property="og:site_name" content="Samtaler På Nett">
</head>
<body>
    <nav>
        <ul>
            <li><a href="/projects/samtalerpanett/pages/friends.php"><i class="fa-regular fa-face-smile"></i>Venner</a></li>
            <li><a href="">+ Legg til venner</a></li>
        </ul>
        <ul class="nav-prof">
            <li><a href="/projects/samtalerpanett/pages/profile.php"><i class="fa-regular fa-circle-user"></i>Profil</a></li>
        </ul>
    </nav>
    <div class="message-manager">
        <div class="song-list">
            <h3>Mine samtaler</h3>
            <button id="newDM" class="new-dm-button">
                <i class="fa-solid fa-plus"></i>
                Ny samtale
            </button>
        <div class="search-container">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="messageSearch" placeholder="Søk i meldinger...">
        </div>
            <ul id="messageList"></ul>
        </div>
    </div>
    
    <!-- Side Bar -->
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

<?php
// includes er helt nederst slik at de ikke fucker opp for html strukturen. når de var på toppen ødela de for <title> i <head>, og det synes jeg ikke var noe koselig i det hele tatt, så jeg fikset det. - isak
include 'include/db.inc.php';
?>