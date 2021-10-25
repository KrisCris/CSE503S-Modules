<?php
require '../../util/reply.php';
require '../../model/Event.php';
require '../user/require-login.php';

$required = ["cid", "gid", "title", "detail", "isFullDay", "start", "end"];
$inputs = [];
foreach ($required as $each) {
    if (!isset($_POST[$each])) {
        reply_json(-1, [], "require: " . implode(", ", $required));
        exit;
    }
    $inputs[$each] = $_POST[$each];
}

$e = Event::addEvent(
    $_POST['uid'],
    $inputs["cid"],
    $inputs["gid"] ? null : $inputs["gid"],
    $inputs["title"],
    $inputs["detail"],
    $inputs["isFullDay"],
    $inputs["start"],
    $inputs["isFullDay"] ? null : $inputs["end"]
);

if($e){
    reply_json(1, ["eid"=>$e]);
} else {
    reply_json(-1, [], "unknown error");
}
