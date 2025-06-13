<?php
session_start();
require 'functions.php';

//redirecter bruker hvis de har uverifisert email eller ikke har svart på passord reset email
if(isset($_COOKIE['not_verified'])){
    header("Location: login.php");
}

if(isset($_COOKIE['password_token_set'])){
    header("Location: login.php");
}

if (!isset($_SESSION['user_id'])) {
    // hvis brukeren ikke er logget inn, redirect til login
    header("Location: logout.php");
    exit();
}

// variabel for versjonsnummer
$version = "1.0.0-alpha";

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
            <li><a href="#" id="global-enable"><i class="fa-regular fa-message"></i>Global Chat</a></li>
            <li><a href="/projects/samtalerpanett/pages/friends.php"><i class="fa-regular fa-face-smile"></i>Venner</a></li>
            <li><a href="">+ Legg til venner</a></li>
        </ul>
        <ul class="nav-prof">
            <li><a href="/projects/samtalerpanett/pages/profile.php"><i class="fa-regular fa-circle-user"></i>Profil</a></li>
        </ul>
    </nav>

    <!-- Direct message liste -->
    <div class="DM-left">
        <div class="DM-act">

            <h3>Mine samtaler <br>(funker nesten)</h3>
            <button id="newDM" class="new-dm-button"><i class="fa-solid fa-plus"></i>Ny samtale</button>
            
            <div class="search-container">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="messageSearch" placeholder="Søk i meldinger...">
            </div>
            <div id="DMList"></div>

        </div>
    </div>

    <!-- Activity liste (høyre) -->
    <div class="activity-viewer">
        <div class="activity-list">
            <h3>Aktive venner</h3>

        </div>
    </div>

    <!-- Midt delen med chat -->
    <div class="container">
        <div class="header">
            <h1 id="header">Samtaler på Nett</h1>
        </div>

        <!--Global Chat-->
        <div class="chat">
            <div id="messages"></div>
            <div class="message-inputs">
                <input type="text" id="messageInput" placeholder="Skriv melding...">
                <button id="sendButton">Send</button>
            </div>
        </div>
    </div>


    <script>
        window.currentUserId = <?php echo json_encode($_SESSION['user_id']);?>;
        window.currentUsername = <?php echo json_encode($_SESSION['username']);?>;
        window.currentProfilePictureUrl = <?php echo json_encode('http://localhost/projects/samtalerpanett/uploads/' . $_SESSION['profile_picture']); ?>;
        window.activeChatType = "global";
        window.recipientId = "all";
   </script>
</body>

<script src="/projects/samtalerpanett/js/mainScript.js"></script>
</html>