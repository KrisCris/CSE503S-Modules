<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/nav.css">
</head>

<?php 
    require dirname(__FILE__) . '/../api/model/User.php'; 
    session_start();

?>

<body>
    <?php require dirname(__FILE__) . '/module/nav.php';?>
    if ($_SESSION){

    }
</body>

</html>