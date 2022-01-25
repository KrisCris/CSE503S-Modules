<?php
require '../../util/reply.php';
require '../../model/Event.php';
require '../user/require-login.php';

if(isset($_POST["eid"])){
    $e = Event::getEventById($_POST["eid"]);
    if($e){
        reply_json(1, $e->toDict());
    } else {
        reply_json(-3);
    }
} else {
    reply_json(-1);
}