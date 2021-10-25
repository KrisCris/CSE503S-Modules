<?php
require '../../util/reply.php';
require '../../model/Event.php';
require '../user/require-login.php';

$required = ["eid", "cid", "gid", "title", "detail", "isFullDay", "start", "end"];
$inputs = [];
foreach ($required as $each) {
    if (!isset($_POST[$each])) {
        reply_json(-1, [], "require: " . implode(", ", $required));
        exit;
    }
    $inputs[$each] = $_POST[$each];
}

if(Event::editEvent(
    $inputs["eid"],
    $inputs["cid"] == 0 ? null : $inputs["cid"],
    $inputs["gid"] == 0 ? null : $inputs["gid"],
    $inputs["title"],
    $inputs["detail"],
    $inputs["isFullDay"],
    $inputs["start"],
    $inputs["isFullDay"] == 1 ? null : $inputs["end"]
)){
    return reply_json(1);
} else {
    reply_json(-1, [], "error");
}
?>