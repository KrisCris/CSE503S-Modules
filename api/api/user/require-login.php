<?php
require dirname(__FILE__) . '/../../model/User.php';

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


?>