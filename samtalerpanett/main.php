<?php
include 'include/db.inc.php';
include 'include/navbar.php';
include 'include/sidebar.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    // hvis brukeren ikke er logget inn, redirect til login
    header("Location: login.php");
    exit();
}

// variabel for versjonsnummer
$version = "v0.0.1";

// sjekker om brukeren har logget in før
function checkRememberMe(mysqli $conn): ?int {
    if(empty($_COOKIE['remember_me'])){
        return null;
    }

    list($selector, $validator) = explode(':', $_COOKIE['remember_me']);

    $stmt = $conn -> prepare('SELECT * FROM user_tokens WHERE selector = ? AND expiry > NOW()');
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $result = $stmt->get_result();
    $token = $result->fetch_assoc();


    if($token && hash_equals($token['hashed_validator'], hash('sha256', $validator))) {
        createRememberMeToken($conn, $token['user_id']);
        return $token['user_id'];
    }

    return null;
}
// logger in
if(!isset($_SESSION['user_id'])){
    $user_id = checkRememberMe($conn);
    if($user_id !== null){
        $_SESSION['user_id'] = $user_id;
    }
}
else{

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler på nett <?php echo $version; ?></title>
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
