<?php
include 'include/db.inc.php';

session_start();

// håndterer innlogging
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);

    // sjekker brukernavn og passord opp mot databasen
    $sql = "SELECT * FROM users WHERE username = ? AND email_verified = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // verifiser brukernavn og passord og lager session hvis de er riktig
    if ($user){
        if(!password_verify($_POST['password'], $user['password'])) {
            $error = "Ugyldig passord";
        }
        elseif(!$user['email_verified']){
            $error = "Du må bekrefte e-posten din";
        }
        else{

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['profile_picture'] = $user['profile_picture'];

            header('Location: main.php'); // redirecter til hovedsiden
            exit();
        }

    }
    else {
        $error = "Du må bekrefte e-posten din"; // error melding hvis du skrev ugyldig brukernavn eller passord
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Samtaler På Nett | Logg inn</title>
    <link rel="icon" href="assets/icons/logo.ico" />
    <link rel="stylesheet" href="css/userRegLog.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

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
    <div class="auth-con">
        <h2>Logg inn</h2>
        <p>For å bruke Samtaler på Nett, må du logge inn.</p> <br>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="form-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="brukernavn" name="username" required>
            </div>

            <div class="form-group">
                <label>Passord:</label>
                <input type="password" placeholder="passord" name="password" required>
            </div>

            <label for="remember_me" class="remember_me">
                <input type="checkbox" id="remember_me" name="remember_me"> Husk meg
            </label><br>

            <button id="submit" type="submit">Logg inn</button>

        </form>

        <p>Har du ikke bruker enda? <a href="register.php">Registrer deg her</a></p>
    </div>
</body>

</html>
