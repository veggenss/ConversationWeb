<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // hvis brukeren ikke er logget inn, redirect til login
    header("Location: ../login.php");
    exit();
}

include '../include/db.inc.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();


    $new_username = $_POST['username'];
    $new_email = $_POST['email'];

    $email = trim($new_email);

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Ugyldig e-post";
    }
    // sjekker at domenet til eposten finnes
    elseif(!checkdnsrr(substr(strrchr($email, "@"), 1), "MX")){
        $error = "E-postdomenet finnes ikke";

    }
    elseif(!preg_match('/^.{4,}$/', $new_username)){
        $error = "Brukernavnet må være 4 siffer eller mer";
    }
    elseif($new_email !== $user['mail']){
        // Sjekk om epost allerede finnes
        $sql = "SELECT id FROM users WHERE mail = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_email, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "E-posten er allerede i bruk";
        }
    }


    if(!isset($error)){
        // hvis Email er anderledes en den i db oppdaterer den email også sender ny email verification
        if($new_email !== $user['mail']){

            $token = bin2hex(random_bytes(16));

            $sql = "UPDATE users SET username = ?, mail = ?, email_verification_token = ?, email_verified = 0 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $new_username, $new_email, $token, $_SESSION['user_id']);
            if($stmt->execute()){
                require '../send_email_verification.php';
                if(sendVerificationEmail($new_email, $new_username, $token)){
                    //Lager cookies for verify_email_info siden
                    setcookie("mail_message", "Du må verifisere email før du logger in igjen. \nEn verifikasjons link har blitt sent til \n$new_email", time() + 10, "/");
                    setcookie("username", $username, time() + 10, "/");
                    setcookie("mail", $new_email, time() + 10, "/");
                    session_unset();
                    session_destroy();
                    header("Location: verify_email_info.php");
                    exit();
                }
                else{
                    $error = "Kunne ikke sende e-post";
                }
            }
        }
        else{
            // oppdaterer bare brukernavn siden e-post er det samme
            $sql = "UPDATE users SET username = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_username, $_SESSION['user_id']);

            if($stmt->execute()){
                $message = "Oppdaterte bruker";
                $_SESSION['username'] = $new_username;
                $_SESSION['email'] = $new_email;
            }
            else{
                $error = "Kunne ikke oppdatere bruker";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler På Nett | Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="../assets/icons/logo.ico" />
    <link rel="stylesheet" href="/projects/samtalerpanett/css/userRegLog.css">
    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
</head>

<body>
    <div class="auth-con">
        <h2><?php echo htmlspecialchars($_SESSION["username"]); ?>s profil</h2>
        <?php if (isset($error)):?>
        <div class="error"><?php echo "{$error}<br>"; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="POST">

            <!-- @IsakBH Hadde vært fint om du vile lagt til knapp og visning av profil bilde. Også må du vel lage nye rows i DB så bare paste her eller send meg SQL koden til det ;) --Viggo 24/05 22:00 -->
            <!-- @veggenss Det skal jeg! --Isak 25/05 18:26 -->
            <div class="profile-group">
                <div class="current-profile">
                    <img src="../uploads/<?php echo htmlspecialchars(
                        $_SESSION["profile_picture"]
                    ); ?>" alt="Profilbilde">
                </div>
            </div>

            <div class="profile-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="brukernavn" name="username" value="<?php echo htmlspecialchars($_SESSION['username']);?>">
            </div>

            <div class="profile-group">
                <label>E-post:</label>
                <input type="text" name="email" value="<?php echo htmlspecialchars($_SESSION['email']);?>">
            </div>

            <div class="profile-group">
                <p>Glemt Passord? <br><a id="backButton" href="password_reset.php">Tilbakestill Passord <i class="fa-solid fa-arrow-up-right-from-square"></i></a></p>
            </div>
            <button id="submit" type="submit" onclick="return confirm('Hvis du har endret email: \nDu blir logget ut også må du verifisere mail på nytt før du logger in igjen')">Lagre Endringer</button>
        </form>
        <?php if(isset($message)):?>
        <div class="positive"><?php echo $message?></div>
        <?php endif;?>
        <p>Antall samtalepoeng: <?php // samtalepoeng går her ?></p>
        <a id="backButton" href="/projects/samtalerpanett/main.php">Tilbake til Samtaler På Nett</a>
    </div>
</body>

</html>