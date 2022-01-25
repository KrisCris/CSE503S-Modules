<?php
require dirname(__FILE__) . '/../../model/User.php';

ini_set("session.cookie_httponly", 1);
session_start();

// check if user is actually logged in in this session
if(isset($_SESSION["uid"]) && $_SESSION["uid"] == $_POST["uid"]){
    // check token against what we have in db
    if(!(isset($_POST["uid"]) && isset($_POST["token"]))){
        reply_json(0);
        exit;
    } else {
        $u = User::isLogin($_POST["uid"], $_POST["token"]);
        if(!$u){
            reply_json(0);
            exit;
        }
    }
} else {
    reply_json(0);
    exit;
}




?>