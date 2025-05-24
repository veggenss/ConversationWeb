<?php
include '../include/db.inc.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $sql = "SELECT password FROM users WHERE user_id = ?";
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
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>">
            <div class="form-group">
                <label>Nytt Passord:</label>
                <input type="password" name="password" required>
                <br>
                <label>Gjenta Passord:</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Oppdater Passord</button>
        </form>
    </div>
</body>
</html>