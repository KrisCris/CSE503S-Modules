<?php 
require '../../util/reply.php';
require '../../model/Event.php';
require '../user/require-login.php';

if(!isset($_POST["eid"])){
    reply_json(-1);
    exit;
}

if(Event::removeEvent($_POST["eid"], $_POST["uid"])){
    reply_json(1);
} else {
    reply_json(-1,[],"Invalid Operation!");
}