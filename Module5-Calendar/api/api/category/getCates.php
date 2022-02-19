<?php
require '../../model/Category.php';
require '../../util/reply.php';
require '../user/require-login.php';

$arr = array();
foreach (Category::getCates($_POST['uid']) as $cate) {
    array_push($arr, $cate->toDict());
}

reply_json(1, $arr);