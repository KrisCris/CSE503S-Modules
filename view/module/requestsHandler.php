<?php
session_start();

# handle all requests requiring token checks.
# redirect malicious requests to the real homepage.
function checkToken(){
    if ((!isset($_SESSION["token"]) || !isset($_POST["token"])) || (isset($_SESSION["token"]) && isset($_POST["token"]) && $_SESSION["token"] != $_POST["token"])){
        header("Location: index.php");
    }
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
    checkToken();
    $title = $_POST["title"];
    $content = $_POST["content"];
    $link = $_POST["link"];
    $storyId = $_POST["storyId"];
    $_GET["storyId"] = Story::addStory($u->id, $title, $content, $link, $storyId);
}

# delete story
if (isset($_POST["deleteStory"]) && $_POST["deleteStory"]==1){
    checkToken();
    Story::deleteStory($_POST['storyId'])?  :$_GET["storyId"] = $_POST['storyId'];
}

# rate the story
if (isset($_POST["rate"])){
    checkToken();
    Story::rateStory($_POST['storyId'], $u->id, $_POST['rate']);
    $_GET["storyId"] = $_POST['storyId'];
}

# deleteComment{
if (isset($_POST["deleteComment"]) && $_POST["deleteComment"]==1){
    checkToken();
    Comment::delete($_POST['commentId']);
    $_GET["storyId"] = $_POST['storyId'];
}

# add/edit comment
if (isset($_POST['commentOn']) && $_POST['commentOn'] >1){
    checkToken();
    if (isset($_POST['editComment']) && $_POST['editComment'] >1){
        Comment::editComment($_POST['editComment'], $_POST["comment"]);
    } else if(isset($_POST['replyTo']) && $_POST['replyTo'] > 1){
        Comment::addComment($_SESSION['uid'], $_POST['commentOn'], $_POST["comment"], $_POST['replyTo']);
    } else {
        Comment::addComment($_SESSION['uid'], $_POST['commentOn'], $_POST["comment"]);
    }
    $_GET["storyId"] = $_POST['commentOn'];
}
?>