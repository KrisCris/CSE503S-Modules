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

$cid = $inputs["cid"];
if(isset($_POST["newTag"]) && $_POST["newTag"] != ""){
    require '../../model/Category.php';
    $ret = Category::addCate($_POST["uid"], $_POST["newTag"], "#000000");
    if($ret){
        $cid = $ret;
    }
}

$eid = Event::addEvent(
    $_POST['uid'],
    $cid == 0 ? null : $cid,
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
