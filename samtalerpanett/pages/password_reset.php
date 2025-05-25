<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // hvis brukeren ikke er logget inn, redirect til login
    header("Location: login.php");
    exit();
}

include '../include/db.inc.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // definerer variabler
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old-password'];
    $new_password = $_POST['new-password'];
    $r_new_password = $_POST['r-new-password'];

    // henter bruker basert på user_id
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // ser om bruker finnes og at det gammle pasordet er riktig hvis bruker finnes
    if(!$user || !password_verify($old_password, $user['password'])){
        $error = "Ugyldig gammelt passord";
    }
    elseif($new_password !== $r_new_password){
        $error = "De nye passordene er ikke like";
    }
    // ser om passorder er bra nok, fordi alle samtaler på nett brukere har bare de beste passordene!
    elseif(!preg_match('/^.{5,}/', $new_password)){
        $error = "Passorder må være 5 siffer eller mer";
    }
    elseif(!preg_match('/(?=.*\w)(?=.*\d)/', $new_password)){
        $error = "Passordet må ha minst 1 tegn og 1 tall";
    }
    elseif(preg_match('/[ ]/', $new_password)){
        $error = "Passordet kan ikke ha mellomrom";
    }

    if(!isset($error)){
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if($stmt->execute()){
            $success = true;
            $message = "Passord er oppdatert :) \nDu må logge in igjen";
            session_unset();
            session_destroy();
            header('Refresh: 5; /projects/samtalerpanett/login.php');
        }
        else{
            $error = "Kunne ikke oppdatere passord :(";
        }

    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/icons/logo.png" />
    <link rel="stylesheet" href="/projects/samtalerpanett/css/userRegLog.css">
    <title>Samtaler På Nett | Passord Tilbakestilling</title>
</head>
<body>
    <div class="auth-con">
        <h2>Tilbakestill Passord</h2><br>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>">
            <?php if(isset($error)){ echo "<div class='error'>$error</div>";}?>
            <div class="form-group">
                <label>Gammle Passord:</label>
                <input type="password" name="old-password" required>
                <br><br>
                <label>Ny Passord:</label>
                <input type="password" name="new-password" required>
                <br><br>
                <label>Gjenta Ny Passord:</label>
                <input type="password" name="r-new-password" required>
            </div>

            <button type="submit"><?php if(isset($message)){ echo "Redirekter om 5...";}else{echo "Oppdater Passord";}?></button>
            <?php if(isset($message)){echo "<div class='positive'>$message</div>";}?>
        </form>
    </div>
</body>
</html>