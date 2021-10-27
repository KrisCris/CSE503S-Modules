<?php
require '../../model/User.php';
require '../../util/reply.php'; 

ini_set("session.cookie_httponly", 1);
session_start();

// check if user is actually logged in in this session
if(isset($_SESSION["uid"]) && $_SESSION["uid"] == $_POST["uid"]){
    if(isset($_POST["uid"]) && isset($_POST["token"])){
        // check token against what we have in db
        $u = User::isLogin($_POST["uid"], $_POST["token"]);
        if($u){
            reply_json(1, ["username"=>$u->username]);
            exit;
        }
    }
    reply_json(0,[],"not login");
    exit;
}
reply_json(0);

?>