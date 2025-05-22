<?php
include('include/db.inc.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $sql = 'UPDATE characters SET weap = "Catalyst" WHERE name IN ("Varesa", "Yumemizuki_Mizuki", "Citlali", "Xianyun", "Neuvillette", "Wriothesley", "Baizhu", "Wanderer", "Nahida", "Yae_Miko", "Sangonomiya_Kokomi", "Mona", "Klee")';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    if($stmt->execute()){
        echo "successfully updated rows: ";
    }
    else{
        echo "Couldn't execute" . $stmt->error;;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RowUpdate</title>
</head>
<body>
    <p>Used statments</p>
    <h3>weapons:</h3>
    <li>Claymore ! $sql = 'UPDATE characters SET weap = "Claymore" WHERE name IN ("Mauvika", "Kinich", "Navia", "Dehya", "Arataki_Itto", "Eula", "Diluc")';</li>
    <li>Sword ! $sql = 'UPDATE characters SET weap = "Sword" WHERE name IN ("keqing", "Xilonen", "Clorinde", "Chiori", "Furina", "Alhaitham", "Nilou", "Kamisato_Ayato", "Kamisato_Ayaka", "Kaedehara_Kazuha", "Albedo", "Qiqi", "Jean")';</li>
    <li>Polearm ! $sql = 'UPDATE characters SET weap = "Polearm" WHERE name IN ("Escoffier", "Emilie", "Arlecchino", "Cyno", "Shenhe", "Raiden_Shogun", "Hu_Tao", "Xiao", "Zhongli")';</li>
    <li>Bow</li>
    <li>Catalyst</li>
    <h3>Elements:</h3>
    <li></li>
    <h3>Nation</h3>
    <li></li>
    <h3>ascension stat</h3>
    <li></li>
    <br>
    <p>preparing to update</p>
    <form action="<?php htmlspecialchars($_SERVER['PHP_SELF'])?>" method="POST">
        <button type="submit" name="submit">Submit</button>
    </form>
</body>
</html>