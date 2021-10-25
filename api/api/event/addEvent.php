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

$eid = Event::addEvent(
    $_POST['uid'],
    $inputs["cid"] == 0 ? null : $inputs["cid"],
    $inputs["gid"] == 0 ? null : $inputs["gid"],
    $inputs["title"],
    $inputs["detail"],
    $inputs["isFullDay"],
    $inputs["start"],
    $inputs["isFullDay"] == 1 ? null : $inputs["end"]
);

if($eid){
    reply_json(1, ["eid"=>$eid]);
} else {
    reply_json(-1, [], "unknown error");
}
