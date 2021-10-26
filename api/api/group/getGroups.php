<?php
require '../../util/reply.php';
require '../../model/Group.php';
require '../user/require-login.php';

reply_json(1, Group::getMyGroups($_POST["uid"]));