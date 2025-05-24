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
    <link rel="icon" href="assets/icons/logo.png" /> <!-- La til verdens beste logo -->
    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <link rel="stylesheet" href="/projects/samtalerpanett/css/userRegLog.css">
</head>

<body>
    <div class="auth-con">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="POST">
            <h2><?php echo htmlspecialchars($_SESSION["username"]); ?>'s profil</h2>
            <div class="profile-group">
                <img src="uploads/<?php echo htmlspecialchars($_SESSION["profile_picture"]); ?>" alt="Profilbilde">
            </div>

            <div class="profile-group">

            </div>
            
            <p>Antall samtalepoeng: <?php // samtalepoeng går her ?></p>

        </form>
        <br> <br>
        <a id="backButton" href="/projects/samtalerpanett/main.php">Tilbake til Samtaler På Nett</a>
    </div>
</body>

</html>