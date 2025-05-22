<?php
session_start();
include('include/db.inc.php');

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
        header('Location: index.php'); // redirecter til hovedsiden
        exit();
    } else {
        $error = "Ugyldig brukernavn eller passord"; // error melding hvis du skrev ugyldig brukernavn eller passord
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Logg inn</title>
    <link rel="stylesheet" href="texteditor.css">
    <script src="texteditor.js"></script>
    <link rel="icon" href="../Pictures/ordlogo.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Open Graph meta-tagger -->
    <meta property="og:title" content="Ord På Nett <?php echo $version; ?>">
    <meta property="og:description" content="UI Redesign! Ord på Nett er et kraftig og brukervennlig tekstbehandlingsverktøy utviklet av meg (Isak Brun Henriksen). Bruk også https://isak.brunhenriksen.no/tonerpanett">
    <meta property="og:image" content="https://isak.brunhenriksen.no/Pictures/ordlogo.png">
    <meta property="og:url" content="https://isak.brunhenriksen.no/ordpanett">
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
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Passord:</label>
                <input type="password" name="password" required>
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
