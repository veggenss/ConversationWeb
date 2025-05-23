<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler På Nett | Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="css/mainStyle.css" />
    <!-- logoet til toner på nett som favicon !! LAG SAMTALER PÅ NETT LOGO OG LEGG DET TIL HER ISTEDET FOR TONER PÅ NETT LOGO - Isak 23.05.25!!-->
    <link rel="icon" href="assets/icons/logo.png" /> <!-- La verdens beste logo -->
    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
</head>

<body>
    <div class="profile-settings">
        <h2><?php echo htmlspecialchars($_SESSION["username"]); ?>s profil</h2>
        <div class="current-profile">
            <img src="uploads/<?php echo htmlspecialchars(
                $_SESSION["profile_picture"]
            ); ?>" alt="Profilbilde">
        </div>

        <p>Antall samtalepoeng: <?php // samtalepoeng går her ?></p>

        <br> <br>
        <a id="backButton" href="index.php">Tilbake til Samtaler På Nett</a>
    </div>
</body>

</html>