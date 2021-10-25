<?php
require '../../util/reply.php'; 
require '../../model/User.php';

if(isset($_POST["username"]) && isset($_POST["password"])){
    $u = User::register($_POST["username"], $_POST["password"]);
    if ($u){
        reply_json(1, ["token"=>$u->token, "uid"=>$u->id, "username"=>$u->username]);
    } else {
        reply_json(-2);
    }
} else {
    reply_json(-1);
}
