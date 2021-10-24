<?php
require dirname(__FILE__) . '/../../util/reply.php'; 
require 'require-login.php';
User::logout($_POST['uid']);
reply_json(1);
?>