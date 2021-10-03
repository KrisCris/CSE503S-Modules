<?php
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

# create new story / edit story
if(isset($_POST["submitStory"]) && isset($_POST["token"]) && $_POST["submitStory"] == 1){
    $title = $_POST["title"];
    $content = $_POST["content"];
    $link = $_POST["link"];
    $storyId = $_POST["storyId"];
    $_GET["storyId"] = Story::addStory($_SESSION['uid'], $title, $content, $link, $storyId);
}

# delete story
if (isset($_POST["delete"]) && $_POST["delete"]==1){
    Story::deleteStory($_POST['storyId'])?  :$_GET["storyId"] = $_POST['storyId'];
}
?>