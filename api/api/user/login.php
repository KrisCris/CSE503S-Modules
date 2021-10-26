<?php
require '../../util/reply.php'; 
require '../../model/User.php';

ini_set("session.cookie_httponly", 1);
session_start();

if(isset($_POST["username"]) && isset($_POST["password"])){
    $u = User::login($_POST["username"], $_POST["password"]);
    if ($u){
        $_SESSION["uid"] = $u->id;
        reply_json(1, ["token"=>$u->token, "uid"=>$u->id, "username"=>$u->username]);
    } else {
        reply_json(-3);
    }
} else {
    reply_json(-1);
}
