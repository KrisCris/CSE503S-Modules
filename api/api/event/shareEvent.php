<?php
require '../../util/reply.php';
require '../../model/Event.php';
require '../user/require-login.php';

if(isset($_POST["eid"])){
    $ret = Event::shareEvent($_POST["eid"]);
    if($ret){
        reply_json(1, $ret);
    } else {
        reply_json(-1, [], "Invalid Operation");
    }
} else {
    reply_json(-1);
}
?>