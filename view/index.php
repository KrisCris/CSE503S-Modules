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
    <?php
    # start session
    session_start();
    # Use dirname to make sure the path won't be messed up when it also requiring other php files.
    require dirname(__FILE__) . '/../api/model/DataManager.php';
    # Get Singleton Instance DataManager.
    $DM = DataManager::getInstance();
    # auto login if possible.
    if (isset($_SESSION["username"]) && $DM->hasUser($_SESSION["username"])) {
        header("Location: my_files.php");
    }
    ?>

    <!-- some decorations -->
    <div>
        <img alt="icon" src="res/icon.gif" />
    </div>

    <!-- login or register -->
    <form action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="POST">
        <input type="text" name="username" id="username" placeholder="Username">
        <div>
            <input type="radio" name="regorlog" id="login" value="login" checked><label for="login">Login</label><br>
            <input type="radio" name="regorlog" id="register" value="register"><label for="register">Register</label><br>
        </div>
        <input id="btn" type="submit" value="Go">
    </form>

    <!-- login / register logic, and error output -->
    <p id="err">
        <?php
        if ($DM != null) {
            // if requres is sent
            if (isset($_POST["username"]) && isset($_POST["regorlog"])) {
                $type = $_POST["regorlog"];
                $username = $_POST["username"];

                if ($username == "") {
                    echo "INVALID INPUT";
                    exit;
                }
                # check account status, 
                # but indeed these code are useless as no password protection are there.
                # login
                if ($type == "login") {
                    # if the user is in our list, login.
                    if ($DM->hasUser($username)) {
                        $_SESSION["username"] = $username;
                        header("Location: my_files.php");
                    } else {
                        echo "YOU HAVE TO REGISTER FIRST!";
                        exit;
                    }
                # register
                } elseif ($type == "register") {
                    # check if account existed.
                    if ($DM->addUser($username)) {
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
</body>
</html>