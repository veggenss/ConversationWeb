<?php
include "include/db.inc.php";
$registerd = null;


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // opplasting av profilbilder
    $profile_picture = 'default.png';
    /*if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) { // hvis brukeren har lastet opp et profil bilde og det ikke oppstod en feil
        $allowed = ['jpg', 'jpeg', 'png', 'gif']; // de tillatte filtypene
        $filename = $_FILES['profile_picture']['name']; // henter filnavnet til uploaden
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); // henter file extensionen (eller filutvidelsen hvis du virkelig vil ha det på norsk, da) til bildet

        // valider og last opp bildet
        if (in_array($ext, $allowed)) { // sjekker hvis file extensionen til bildet brukeren lastet opp er i $allowed arrayen
            $new_filename = uniqid() . '.' . $ext; // genererer en unik id basert på det nåværende klokkeslettet (tror jeg, i hvertfall) - for å ikke få conflicts med filnavn og sånt drit
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], 'uploads/' . $new_filename); // flytter den opplastede filen til uploads og gir den nytt filnavn (fra $new_filename variabelen)
            $profile_picture = $new_filename; // setter profilbildet
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], 'uploads/' . $new_filename)) { // hvis den ikke klarte å flytte bildet
                error_log('Kunne ikke flytte bildet :c. Error: ' . error_get_last()['message']); // skriver til error loggen at den ikke klarte å flytte bildet, og legger til error meldingen/feilmeldingen
                $error = "Kunne ikke laste opp bildet."; // setter error til kunne ikke laste opp bilde, slik at brukeren ser det
            }
            }*/

    if(!preg_match('/^.{4,}$/', $_POST['username'])){
        $error = "Brukernvnet må være minst 4 siffer";
    }
    else{
        $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
        if(!preg_match('/^.{5,}$/', $_POST['password'])){
            $error = "Passordet må være minst 5 siffer";
        }
        elseif(!preg_match('/(?=.*\w)(?=.*\d)/', $_POST['password'])){

            $error = "Passordet må ha minst 1 tegn og 1 tall";
        }
        elseif(preg_match('/[ ]/', $_POST['password'])){
            $error = "Passordet kan ikke ha mellomrom";
        }
        else{
            // email validation
            $email = trim($_POST['email']);
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $error = "Ugyldig e-post";
            }
            // sjekker at domenet til eposten finnes
            elseif(!checkdnsrr(substr(strrchr($email, "@"), 1), "MX")){
                $error = "E-postdomenet finnes ikke";

            }
            else{
                // ser etter eposten i db
                $sql = "SELECT * FROM users WHERE mail = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $fetch_mail = $result->fetch_assoc();

                if($fetch_mail) {
                    $error = "E-posten er allerede i bruk";
                }
                else{
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                    // lager e-post token
                    $token = bin2hex(random_bytes(16));

                    // inserter alt inn i databasen
                    $sql = "INSERT INTO users (username, mail, password, profile_picture, email_verification_token, email_verified) VALUES (?, ?, ?, ?, ?, 0)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssss", $username, $email, $password, $profile_picture, $token);

                    if ($stmt->execute()) {
                        require 'send_email_verification.php'; // for de som kloner: Denne filen er ikke i repo se .gitignore - wiggo
                        // å du er så sigma for å skrive den kommentaren, wiggo - isak
                        if(sendVerificationEmail($email, $username, $token)){
                            $registerd = true;
                        }
                        else{
                            $error = "E-post kunne ikke sendes";
                        }
                    }
                    else {
                        $error = "Kunne ikke registreres";
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/userRegLog.css">
    <link rel="icon" href="assets/icons/logo.ico" />
    <title>Samtaler På Nett | Registrer</title>
</head>
<body>
    <div class="auth-con">
        <h2>Registrering</h2>
        <p>Du må registrere deg for å bruke nettsiden</p>

        <?php if (isset($error)):?>
        <div class="error"><?php echo "{$error}<br>"; ?></div>
        <?php endif; ?>
        <?php if ($registerd):?>
        <div class="positive">Du er nå registrert!</div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="register-form">

            <div class="form-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="brukernavn" name="username" required>
            </div>

            <div class="form-group">
                <label>E-post:</label>
                <input type="email" placeholder="e-post" name="email" required>
            </div>

            <div class="form-group">
                <label>Passord:</label>
                <input type="password" placeholder="passord" name="password" required>
            </div>

            <div class="form-group">
                <label>Profilbilde:</label>
                <input type="file" name="profile_picture">
            </div>

            <button type="submit" value="Register" class="submit">Registrer deg</button>

            <?php if(isset($registerd)):?>
            <div class="positive">Bekreftelses epost har blir sent til <?php echo $email;?></div>
            <?php endif; ?>
            <p>Har du allerede bruker? <a href="login.php">Logg inn her</a></p>
        </form>
    </div>
</body>
</html>