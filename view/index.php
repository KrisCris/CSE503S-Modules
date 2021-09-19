<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>Portal</title>
</head>

<body>
    <div>
    <img src="res/icon.gif"/>
    </div>
    <form action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="POST">
        <input type="text" name="username" id="username" placeholder="Username">
        <div>
            <input type="radio" name="regorlog" id="" value="login" checked><label for="login">Login</label><br>
            <input type="radio" name="regorlog" id="" value="register"><label for="register">Register</label><br>
        </div>
        <input id="btn" type="submit" value="Go">
    </form>
</body>
<p id="err">
    <?php
    require dirname(__FILE__).'/../api/model/DataManager.php';
    $DM = DataManager::getInstance();
    if ($DM != null) {
        if (isset($_POST["username"]) && isset($_POST["regorlog"])) {
            $type = $_POST["regorlog"];
            $username = $_POST["username"];
            if ($username == "") {
                echo "INVALID INPUT";
                exit;
            }
            # try to check account status, 
            # but indeed these code are useless as no protection are added to any account.
            if ($type == "login") {
                if ($DM->hasUser($username)) {
                    session_start();
                    $_SESSION["username"] = $username;
                    header("Location: my_files.php");
                } else {
                    echo "YOU HAVE TO REGISTER FIRST!";
                    exit;
                }
            } elseif ($type == "register") {
                if ($DM->addUser($username)) {
                    session_start();
                    $_SESSION["username"] = $username;
                    header("Location: my_files.php");
                } else {
                    echo "Username Existed!";
                    exit;
                }
            }
        }
    }


    ?>
</p>

</html>