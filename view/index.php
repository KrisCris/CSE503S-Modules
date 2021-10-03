<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <!-- <link rel="stylesheet" href="style/index.css">
    <link rel="stylesheet" href="style/nav.css"> -->
</head>

<?php 
    require dirname(__FILE__) . '/../api/model/User.php'; 
    require dirname(__FILE__) . '/../api/model/Story.php';
    session_start();

    # handle all requests requiring token checks.
    # redirect malicious requests to the real homepage.
    if (isset($_SESSION["token"]) && isset($_POST["token"]) && $_SESSION["token"] != $_POST["token"]){
        header("Location: index.php");
    }
?>

<body>
    <?php 
    require dirname(__FILE__) . '/module/nav.php';
    require dirname(__FILE__) . '/module/editor.php';
    require dirname(__FILE__) . '/module/storyViewer.php';
    require dirname(__FILE__) . '/module/storyList.php';
    
    
    
    
    ?>


</body>

</html>