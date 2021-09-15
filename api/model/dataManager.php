<?php

require "file.php";
require "user.php";

class DataManager
{
    private static $instance = null;

    var $PATH = "/usr/shared/fileshare";

    var $users;

    private function __construct()
    {
        // init
    }

    // private function __clone()
    // {
    //     // disabled
    // }

    public static function getInstance()
    {
        if (self::$instance != null) {
            return self::$instance;
        } else {
            self::$instance = new static();
        }
    }

    function load($path)
    {
        // io

    }


    function dump($path)
    {
        $u = json_encode($this->users);
        // io
    }
}
