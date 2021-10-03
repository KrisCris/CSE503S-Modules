<div class="nav">
    <?php
    if (isset($_SESSION["uid"])) {
        $name = $u->username;
        $img = $u->getPhoto();
        $imgStyle = null;
    } else {
        $name = 'Guest';
        $img = User::getGuestPhoto();
        $imgStyle = "filter: grayscale(100%);";
    }
    ?>
    <p id="hello">Hello <?php echo $name ?>!</p>
    <div id="nav">
    <img class="avatar" src=<?php echo $img ?> alt="user-photo" style="<?php echo $imgStyle ?>">
    <?php if (isset($_SESSION["uid"])) { ?>
        <div class='inputs'>
        <form class="nav" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <input type="hidden" name="logout" value="1">
            <input type="submit" value="Logout">
        </form>
        </div>
        <div class='inputs'>
        <form class="nav" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
            <input type="hidden" name="newStory" value="1">
            <input type="submit" value="New Story">
        </form>
        </div>
        <?php } else { ?>
        <form class="nav" action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="POST">
            <input type="hidden" name="sign" value="1">
            <div class='inputs'>
                <input type="text" name="username" id="username" placeholder="Username"><br>
                <input type="password" name="password" id="password" placeholder="Password">
            </div>

            <div class='inputs'>
                <input type="radio" name="regorlogin" id="login" value="login" checked><label for="login">Login</label><br>
                <input type="radio" name="regorlogin" id="register" value="register"><label for="register">Register</label><br>
            </div>
            <input id="btnGO" type="submit" value="Go">
        </form>
        <?php if ($errMsg) { ?>
        <p id="error"><?php echo $errMsg; ?></p>
        <?php }?>
    </div>
    <?php } ?>
</div>