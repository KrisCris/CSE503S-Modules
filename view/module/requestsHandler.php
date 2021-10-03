<?php
session_start();
# handle all requests requiring token checks.
# redirect malicious requests to the real homepage.
if (isset($_SESSION["token"]) && isset($_POST["token"]) && $_SESSION["token"] != $_POST["token"]){
    header("Location: index.php");
}
if(isset($_SESSION['uid']))
    $u = User::getUserById($_SESSION["uid"]);

# login / register
$errMsg = null;
if (isset($_POST["sign"]) && $_POST["sign"] == 1) {
    if ($_POST['regorlogin'] == "login") {
        $u = User::login($_POST["username"], $_POST["password"]);
        if ($u == null) {
            $errMsg = "Wrong username or password!";
        } else {
            $_SESSION["uid"] = $u->id;
            $_SESSION["token"] = $_SESSION['token'] = bin2hex(random_bytes(32));
        }
    } else {
        $u = User::register($_POST["username"], $_POST["password"]);
        if ($u == null) {
            $errMsg = "Invalid username or password!";
        } else {
            $_SESSION["uid"] = $u->id;
            $_SESSION["token"] = $_SESSION['token'] = bin2hex(random_bytes(32));
        }
    }
}

# logout
if (isset($_POST['logout']) && $_POST['logout'] == 1) {
    session_unset();
}

# create new story / edit story
if(isset($_POST["submitStory"]) && isset($_POST["token"]) && $_POST["submitStory"] == 1){
    $title = $_POST["title"];
    $content = $_POST["content"];
    $link = $_POST["link"];
    $storyId = $_POST["storyId"];
    $_GET["storyId"] = Story::addStory($u->id, $title, $content, $link, $storyId);
}

# delete story
if (isset($_POST["delete"]) && $_POST["delete"]==1){
    Story::deleteStory($_POST['storyId'])?  :$_GET["storyId"] = $_POST['storyId'];
}

# rate the story
if (isset($_POST["rate"])){
    Story::rateStory($_POST['storyId'], $u->id, $_POST['rate']);
    $_GET["storyId"] = $_POST['storyId'];
}
?>