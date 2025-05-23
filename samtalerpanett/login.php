<?php
session_start();
include 'include/db.inc.php';

//Remember me (bedre en ord på nett haha)
// det var slemt :( - isak
function createRememberMeToken(mysqli $conn, int $userId): void
{
    $selector = bin2hex(random_bytes(8));
    $validator = bin2hex(random_bytes(32));
    $hashedValidator = hash('sha256', $validator);
    $expiry = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30); // Varer i 30 dager :)

    // Lagrer i databasen
    $stmt = $conn->prepare("INSERT INTO user_tokens (selector, hashed_validator, user_id, expiry) VALUES (?, ?, ?, ?)");
    $stmt->execute([$selector, $hashedValidator, $userId, $expiry]);

    // Lagrer som cookie
    $cookieValue = "$selector:$validator";
    setcookie('remember_me', $cookieValue, time() + 60 * 60 * 24 * 30, "/", "", true, true); // Sikker og HttpOnly
}

// håndterer innlogging
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);

    // sjekker brukernavn og passord opp mot databasen
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // verifiser brukernavn og passord og lager session hvis de er riktig
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_picture'] = $user['profile_picture'];

        //Sjekker om remember me er set
        if (!empty($_POST['remember_me'])) {
            createRememberMeToken($conn, $userId);
        }

        header('Location: main.php'); // redirecter til hovedsiden
        exit();
    } else {
        $error = "Ugyldig brukernavn eller passord"; // error melding hvis du skrev ugyldig brukernavn eller passord
    }
<<<<<<< HEAD

=======
>>>>>>> 55966ce52584718d71e7049aed9e77ea833bb3ce
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Logg inn</title>
    <link rel="stylesheet" href="texteditor.css">
    <script src="texteditor.js"></script>
    <link rel="icon" href="../Pictures/ordlogo.png" />
    <link rel="stylesheet" href="CSS/userRegLog.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Open Graph meta-tagger -->
    <meta property="og:title" content="Ord På Nett <?php echo $version; ?>">
    <meta property="og:description" content="UI Redesign! Ord på Nett er et kraftig og brukervennlig tekstbehandlingsverktøy utviklet av meg (Isak Brun Henriksen). Bruk også https://isak.brunhenriksen.no/tonerpanett">
    <meta property="og:image" content="https://isak.brunhenriksen.no/Pictures/samtalelogo.png">
    <meta property="og:url" content="https://isak.brunhenriksen.no/conversationWeb">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="no_NO">
    <meta property="og:site_name" content="Ord På Nett">
</head>

<body>
    <div class="auth-container">
        <h2>Logg inn</h2>
        <p>
            For å bruke Ord på Nett, må du logge inn.
        </p> <br>

        <?php if (isset($error)): ?> <!-- hvis det oppstod en feil-->
            <div class="error"><?php echo $error; ?></div> <!-- for error melding -->
        <?php endif; ?> <!-- exiter if statement -->

        <form method="POST">

            <div class="form-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="enter username" name="username" required>
            </div>

            <div class="form-group">
                <label>Passord:</label>
                <input type="password" placeholder="enter password" name="password" required>
            </div>

            <label for="remember_me">
                <input type="checkbox" id="remember_me" name="remember_me"> Husk meg
            </label> <br> <br>

            <button id="submit" type="submit">Logg inn</button>

        </form>

        <p>Har du ikke bruker enda? <a href="register.php">Registrer deg her</a></p>
    </div>
</body>

</html>
