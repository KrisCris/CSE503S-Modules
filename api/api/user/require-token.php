<?php

if(isset($_POST["token"])){
    if($_POST["token"] != $_SESSION["token"]){
        reply_json(0,[],"illeagal access");
    }
} else {
    reply_json(0,[],"illeagal access");
}

?>