<?php
include('include/db.inc.php');

$sourceDir = 'characterPortraits/gi/';
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

$files = scandir($sourceDir);

foreach ($files as $file) {
    $filePath = $sourceDir . $file;
    $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (is_file($filePath) && in_array($fileExt, $allowed)) {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $fileData = file_get_contents($filePath);

        if ($fileData === false) {
            echo "Failed to read file: $file<br>";
            continue;
        }

        echo "Uploading '$fileName' (" . strlen($fileData) . " bytes)...<br>";

        $stmt = $conn->prepare("INSERT INTO characters (char_port, name) VALUES (?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ss", $fileData, $fileName);

        if ($stmt->execute()) {
            echo "Inserted: $fileName<br>";
        } else {
            echo "Failed: $fileName - " . $stmt->error . "<br>";
        }

        $stmt->close();
    }
}

$conn->close();
?>
