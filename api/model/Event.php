<?php
require dirname(__FILE__) . '/DB.php';
class Event{
    private $id;
    private $uid;
    private $title;
    private $detail;
    private $location;
    private $color;
    private $isFullDay;
    private $doRepeat;
    private $start;
    private $end;

    private function __construct()
    {
        
    }

    // fetch detail of an event
    public static function getEventById(){

    }

    // fetch events in a period, say, a month.
    public static function getEventByRange(){

    }
}