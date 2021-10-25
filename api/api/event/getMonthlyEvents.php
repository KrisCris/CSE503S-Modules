<?php
require '../../util/reply.php';
require '../user/require-login.php';
require '../../model/Event.php';

if(isset($_POST["beginTS"]) && isset($_POST["endTS"])){
    $beginTS = $_POST["beginTS"];
    $endTS = $_POST["endTS"];
    
    $objArr = Event::getEventByRange($_POST["uid"], $beginTS, $endTS);
    $dictArr = array();
    for($i = 0; $beginTS+$i*86400-1 <= $endTS; $i++){
        $dictArr[$beginTS+$i*86400] = array();
    }
    
    foreach ($dictArr as $time => $arr) {
        $dayEnd = $time + 86400-1;
        foreach ($objArr as $e) {
            if(
                ($e->start>=$time && $e->start<=$dayEnd) || 
                ($e->end>=$time && $e->end<=$dayEnd) || 
                ($e->start<=$time && $e->end>=$dayEnd)
            ){
                array_push($dictArr[$time], $e->toDict());
            }
        }
    }

    reply_json(1, $dictArr);
} else {
    reply_json(-1);
}
