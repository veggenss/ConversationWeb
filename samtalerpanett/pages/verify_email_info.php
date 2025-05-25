<?php
$message = "";

if(isset($_COOKIE['mail_message'])){
    $message = $_COOKIE['mail_message'];
    setcookie("mail_message", "", time() - 3600, "/");
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
        <div class="positive"><?php if($message){ echo htmlspecialchars($message);}?></div>
        <a href="../login.php" class="backButton">Trykk her for å logge inn</a>
    </div>
</body>
</html>