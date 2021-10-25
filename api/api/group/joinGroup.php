<?php
require '../../util/reply.php';
require '../../model/Group.php';
require '../user/require-login.php';

if(isset($_POST["uuid"])){
    $g = Group::joinGroup($_POST["uuid"], $_POST["uid"]);
    if($g){
        reply_json(1, $g->toDict());
        exit;
    }
}
reply_json(-1);

?>