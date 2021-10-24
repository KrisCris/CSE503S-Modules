<?php
require dirname(__FILE__) . '/../../util/reply.php'; 
require dirname(__FILE__) . '/../../model/User.php';

if(isset($_POST["username"]) && isset($_POST["password"])){
    $u = User::register($_POST["username"], $_POST["password"]);
    if ($u){
        $_SESSION["uid"] = $u->id;
        $_SESSION["token"] = bin2hex(random_bytes(32));
        reply_json(1, ["token"=>$_SESSION["token"]]);
    } else {
        reply_json(-2);
    }
} else {
    reply_json(-1);
}
