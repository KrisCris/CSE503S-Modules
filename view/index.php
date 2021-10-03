<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="style/nav.css">
    <link rel="stylesheet" href="style/story.css">
    <link rel="stylesheet" href="style/editor.css">
    <link rel="stylesheet" href="style/comments.css">
</head>

<?php 
    require dirname(__FILE__) . '/../api/model/User.php'; 
    require dirname(__FILE__) . '/../api/model/Story.php';
    require dirname(__FILE__) . '/module/requestsHandler.php';
?>

<body>
    <?php 


    require dirname(__FILE__) . '/module/nav.php';
    require dirname(__FILE__) . '/module/storyList.php';
    require dirname(__FILE__) . '/module/editor.php';
    require dirname(__FILE__) . '/module/storyViewer.php';
    ?>
</body>

</html>