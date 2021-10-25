<?php
require '../../util/reply.php';
require '../user/require-login.php';

$required = ["name", "color"];
$inputs = [];
foreach ($required as $each) {
    if (!isset($_POST[$each])) {
        reply_json(-1, [], "require: " . implode(", ", $required));
        exit;
    }
    $inputs[$each] = $_POST[$each];
}

$eid = Category::addCate(
    $_POST['uid'],
    $inputs["name"],
    $inputs["color"]
);

if($eid){
    reply_json(1, ["eid"=>$eid]);
} else {
    reply_json(-1, [], "unknown error");
}