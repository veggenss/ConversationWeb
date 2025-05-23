<?php
include "include/db.inc.php";
$registerd = null;


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //Starts the long username and password verification proccess
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
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            // Username doesn't exist, proceed to insert
            $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                $registerd = true;
            }
            else {
                $error = "Kunne ikke registrere";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/userRegLog.css">
    <title>Registrer</title>
</head>
<body>
    <div class="auth-con">
        <h2>Registrering</h2>
        <p>Du må registrere deg for å bruke Samtaler På Nett.</p>
        <?php if (isset($error)):?>
        <div class="error"><?php echo "{$error}<br>"; ?></div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="register-form">

            <div class="form-group">
                <label>Brukernavn:</label>
                <input type="text" placeholder="Brukernavn" name="username" required>
            </div>

            <div class="form-group">
                <label>E-post:</label>
                <input type="email" placeholder="E-post" name="email" required>
            </div>

            <div class="form-group">
                <label>Passord:</label>
                <input type="password" placeholder="Passord" name="password" required>
            </div>

            <button type="submit" value="Register" class="submit">Registrer deg</button>
            <?php if ($registerd):?>
                <div class="registerd">Du er nå registrert!</div>
            <?php endif; ?> <br>

            <p>Har du allerede bruker? <a href="login.php">Log inn her</a></p>
        </form>
    </div>
</body>
</html>