<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="POST">
        <input type="text" name="username" id="username" placeholder="Username">
        <div>
            <input type="radio" name="regorlog" id="" value="login"><label for="login">Login</label><br>
            <input type="radio" name="regorlog" id="" value="register"><label for="register">Register</label><br>            
        </div>
        <input type="submit" value="Go">
    </form>
</body>
</html>
form
<?php
    if(isset($_POST["username"]) && isset($_POST["regorlog"])){
        $type = $_POST["regorlog"];
        $username = $_POST["username"];
        if($type == "login"){
            session_start();
            $_SESSION["username"] = $username;
            header("Location: my_files.php");
        } elseif($type == "register"){
            
        }
    } else {
        exit;
    }
?>