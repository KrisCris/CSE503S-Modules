<?php
require dirname(__FILE__) . '/../../util/reply.php'; 
require '../user/require-login.php';
if(isset($_POST["beginTS"]) && isset($_POST["endTS"])){
    $beginTS = $_POST["beginTS"];
    $endTS = $_POST["endTS"];
    

    
    






    reply_json(1, ["beginTS"=>$beginTS, "endTS"=>$endTS]);
} else {
    reply_json(-1);
}

?>
