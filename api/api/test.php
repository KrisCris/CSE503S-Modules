<?php 
ini_set("session.cookie_httponly", 1);

session_start();
if(isset($_SESSION["username"])){
    $_SESSION["username"] = $_SESSION["username"]+1;
} else{
    $_SESSION["username"] = 1;
}

echo json_encode(["1"=>$_SESSION["username"]]);
?>