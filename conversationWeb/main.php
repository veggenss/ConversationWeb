<?php
// variabel for versjonsnummer
$version = "v0.0.1";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Samtaler på nett <?php echo $version; ?></title>

    <!-- meta tags for søkemotoroptimalisering -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- stylesheets -->
    <link rel="stylesheet" href="CSS/mainStyle.css" />

    <!-- logoet til toner på nett som favicon -->
    <link rel="icon" href="assets/icons/logo.png" />

    <!-- ikoner fra font awesome og google fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
</head>

<body>
    <div class="message-manager">
        <div class="song-list">
            <h3>Mine samtaler</h3>
            <button id="newDM" class="new-dm-button">
                <i class="fa-solid fa-plus"></i>
                Ny samtale
            </button>
            <div class="search-container">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="messageSearch" placeholder="Søk i meldinger...">
            </div>
            <ul id="messageList"></ul>
        </div>
    </div>

    <div class="activity-viewer">
        <div class="activity-list">
            <h3>Aktive venner</h3>
        
        </div>
    </div>
    <div class="container">
        <h1 id="header">Samtaler på Nett</h1>

        <p>Her kommer den snart berømte smsappen, Samtaler på Nett.</p>
    </div>
</body>

</html>