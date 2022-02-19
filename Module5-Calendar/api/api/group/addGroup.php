<?php
require '../../util/reply.php';
require '../../model/Group.php';
require '../user/require-login.php';

if(isset($_POST["name"])){
    $g = Group::addGroup($_POST["uid"], $_POST["name"]);
    if($g){
        reply_json(1, $g);
        exit;
    }
}
reply_json(-1);
?>