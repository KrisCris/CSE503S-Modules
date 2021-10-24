<?php
require dirname(__FILE__) . '/../../util/reply.php'; 
require dirname(__FILE__) . '/../../model/User.php';

if(isset($_POST["username"]) && isset($_POST["password"])){
    $u = User::login($_POST["username"], $_POST["password"]);
    if ($u){
        reply_json(1, ["token"=>$u->token, "uid"=>$u->id, "username"=>$u->username]);
    } else {
        reply_json(-3);
    }
} else {
    reply_json(-1);
}
