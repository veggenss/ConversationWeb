<?php
$message = null;


if(isset($_COOKIE['mail_message'])){
    $message = $_COOKIE['mail_message'];
    setcookie("mail_message", "", time() - 3600, "/");
}
if(isset($_COOKIE['username'])){
    $username = $_COOKIE['username'];
}
if(isset($_COOKIE['mail'])){
    $mail = $_COOKIE['mail'];
}


if($_SERVER['REQUEST_METHOD'] == 'POST'){

    include '/projects/samtalerpanett/send_email_verification.php';

    $token = bin2hex(random_bytes(16));
    if(sendVerificationEmail($mail, $username, $token)){
        $message = "E-post Sendt...";
    }
    else{
        $error = "Kunne ikke sende e-post";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/projects/samtalerpanett/assets/icons/logo.ico">
    <link rel="stylesheet" href="../css/userRegLog.css">
    <title>Samtaler På Nett | E-post sendt</title>
</head>
<body>
    <div class="auth-con">
        <h2>Verifiser E-post...</h2>
        <?php if($message){ echo htmlspecialchars("<div class='positive'>$message</div>");}?>
        <form action="" method="POST">
            <button type="submit"><?php if(isset($error)){echo $error;}else{ echo "Send epost på nytt";}?></button>
        </form>
        <br>
        <a href="../login.php" class="backButton">Trykk her for å logge inn</a>
    </div>
</body>
</html>