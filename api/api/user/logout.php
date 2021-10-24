<?php
require dirname(__FILE__) . '/../../util/reply.php'; 
require 'require-login.php';
session_unset();
reply_json(1);
?>