<?php
require '../../util/reply.php'; 
require 'require-login.php';

User::logout($_POST['uid']);
unset($_SESSION["uid"]);
reply_json(1);
?>