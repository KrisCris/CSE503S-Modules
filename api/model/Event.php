<?php
require dirname(__FILE__) . '/DB.php';
class Event{
    public $id;
    public $uid;
    public $title;
    public $detail;
    public $location;
    public $color;
    public $isFullDay;
    public $doRepeat;
    public $start;
    public $end;

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