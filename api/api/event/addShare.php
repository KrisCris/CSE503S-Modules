<?php
require '../../util/reply.php';
require '../../model/Event.php';
require '../user/require-login.php';

if(isset($_POST["shareToken"])){
    $ret = Event::addShare($_POST["uid"], $_POST["shareToken"]);
    if($ret){
        reply_json(1, ["eid"=>$ret]); 
    } else {
        reply_json(-1);
    }
} else {
    reply_json(-1);
}


?>