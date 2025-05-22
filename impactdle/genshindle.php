<?php
include('include/db.inc.php');

// Character name to fetch image for
$name = "aloy";

// Fetch just the image
$stmt = $conn->prepare("SELECT char_port FROM characters WHERE name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($image);
    $stmt->fetch();

    // Set the correct Content-Type manually
    header("Content-Type: image/png"); // change to image/jpeg if needed
    echo $image;
} else {
    http_response_code(404);
    echo "Image not found.";
}
?>

<!--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impactdle</title>
</head>
<body>
    <section class="title">
        <div class="title-container">
            <div class="title-content">
                <h1>Impactle</h1>
                <div class="title-sub-content">
                    <h3>Guess a character!</h3>
                </div>
            </div>
        </div>
    </section>
    <form action="<?php htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        <section class="game">
            <select name="characterMenuBtn" id="characterMenuBtn">Characters</select>
                <?php
                
                //echo "<option value=''>" . "</option>";
                ?>
    </section>
    </form>
</body>
</html>