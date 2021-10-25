<?php
require dirname(__FILE__) . '/DB.php';
class Event{
    public $id;
    public $uid;
    public $title;
    public $detail;
    public $isFullDay;
    public $start;
    public $end;

    public $cid;
    public $cName;
    public $cColor;

    private function __construct()
    {
        
    }

    // fetch detail of an event
    public static function getEventById(){

    }

    // fetch events in a period, say, a month.
    public static function getEventByRange(){
        global $conn;
        // $stmt = $conn->prepare("select id, title, detail, ")
    }

    public static function addEvent($uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end=null){
        global $conn;
        $stmt = $conn->prepare("insert into event (uid, cid, gid, title, detail, isFullDay, start, end) values (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iiissiii', $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end);
        if($stmt->execute()){
            $id = $conn->insert_id;
            $stmt->close();
            return $id;
        }
        $stmt->close();
        return null;
    }
}