<?php
require '../../model/User.php';
require dirname(__FILE__) . '/../../util/reply.php'; 

if(isset($_POST["uid"]) && isset($_POST["token"])){
    $u = User::isLogin($_POST["uid"], $_POST["token"]);
    if($u){
        reply_json(1, ["username"=>$u->username]);
        exit;
    }
}
reply_json(0,[],"not login")
?>