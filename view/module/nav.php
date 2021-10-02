<div class="nav">
    <?php
    # handle requests
    $errMsg = null;
    if (isset($_POST["sign"]) && $_POST["sign"] == 1) {
        if ($_POST['regorlogin'] == "login") {
            $u = User::login($_POST["username"], $_POST["password"]);
            if ($u == null) {
                $errMsg = "Wrong username or password!";
            } else {
                $_SESSION["uid"] = $u->getId();
                $_SESSION["token"] = $_SESSION['token'] = bin2hex(random_bytes(32));
            }
        } else {
            $u = User::register($_POST["username"], $_POST["password"]);
            if ($u == null) {
                $errMsg = "Invalid username or password!";
            } else {
                $_SESSION["uid"] = $u->getId();
                $_SESSION["token"] = $_SESSION['token'] = bin2hex(random_bytes(32));
            }
        }
    }
    if ($errMsg) { ?>
        <p class="error"><?php echo $errMsg; ?></p>
    <?php }
    ?>
    <?php
    if (isset($_POST['logout']) && $_POST['logout'] == 1) {
        session_unset();
    }
    ?>

    <?php
    if (isset($_SESSION["uid"])) {
        $u = User::getUserById($_SESSION["uid"]);
        $name = $u->getUsername();
        $img = $u->getPhoto();
        $imgStyle = null;
    } else {
        $name = 'Guest';
        $img = User::getGuestPhoto();
        $imgStyle = "filter: grayscale(100%);";
    }
    ?>
    <img class="avatar" src=<?php echo $img ?> alt="user-photo" style="<?php echo $imgStyle ?>">
    <p>Hello <?php echo $name ?>!</p>

    <?php if (isset($_SESSION["uid"])) { ?>
        <form action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post">
            <input type="hidden" name="logout" value="1">
            <input type="submit" value="Logout">
        </form>
    <?php } else { ?>
        <form action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="POST">
            <input type="hidden" name="sign" value="1">
            <div class='inputs'>
                <input type="text" name="username" id="username" placeholder="Username"><br>
                <input type="password" name="password" id="password" placeholder="Password">
            </div>

            <div class='inputs'>
                <input type="radio" name="regorlogin" id="login" value="login" checked><label for="login">Login</label><br>
                <input type="radio" name="regorlogin" id="register" value="register"><label for="register">Register</label><br>
            </div>
            <input id="btn" type="submit" value="Go">
        </form>
    <?php } ?>
</div>