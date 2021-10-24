<?php
$msgs = [
    1 => "success",
    -1 => "input error",
    0 => "login required",
    -2 => "already exists",
    -3 => "not exists"
];

function reply_json($code=1, $data=[], $msg="success")
{
    if (isset($msgs[$code]) && $msg == $msgs[$code]) $msg = $msgs[$code];
    echo json_encode(["code"=>$code, "msg"=>$msg, "data"=>$data]);
}
?>