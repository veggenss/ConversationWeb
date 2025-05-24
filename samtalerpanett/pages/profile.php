<?php
session_start();
include '../include/db.inc.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler På Nett | Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="assets/icons/logo.png" />
    <link rel="stylesheet" href="/projects/samtalerpanett/css/userRegLog.css">
    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
</head>

<body>
    <div class="auth-con">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="POST">
            <h2><?php echo htmlspecialchars($_SESSION["username"]); ?>'s profil</h2>

            <!-- @IsakBH Hadde vært fint om du vile lagt til knapp og visning av profil bilde. Også må du vel lage nye rows i DB så bare paste her eller send meg SQL koden til det ;) --Viggo 24/05 22:00 -->
            <div class="profile-group">
                <img src="uploads/<?php echo htmlspecialchars($_SESSION["profile_picture"]); ?>" alt="Profilbilde">
            </div>

            <div class="profile-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="brukernavn" name="username" value="<?php echo $_SESSION['username']?>">
            </div>

            <div class="profile-group">
                <label>E-post:</label>
                <input type="text" value="<?php echo $_SESSION['email'];?>">
            </div>
            
            <div class="profile-group">
                <p>Glemt Passord? <br><a id="backButton" href="password_reset.php">Tilbakestill Passord <i class="fa-solid fa-arrow-up-right-from-square"></i></a></p>
            </div>
            <button id="submit" type="submit">Lagre Endringer</button>
        </form>
        <br>
        <p>Antall samtalepoeng: <?php // samtalepoeng går her ?></p>
        <a id="backButton" href="/projects/samtalerpanett/main.php">Tilbake til Samtaler På Nett</a>
    </div>
</body>

</html>