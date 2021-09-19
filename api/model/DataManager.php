<?php

require dirname(__FILE__)."/User.php";
require dirname(__FILE__)."/../IOUtil.php";

class DataManager
{
    private static $instance = null;

    private $configPath;
    private $users;

    private function __construct()
    {
        $this->configPath = IOUtil::$path . ".config/";
        $this->users = array();
    }

    private function __clone()
    {
        // disabled
    }

    public static function getInstance()
    {
        if (self::$instance != null) {
            return self::$instance;
        } else {
            self::$instance = new static();
            self::$instance->readConfig();
            return self::$instance;
        }
    }

    public function readConfig(){
        if (!file_exists($this->configPath)){
            mkdir($this->configPath);
        }
        $usersPath = $this->configPath . "users.txt";
        if (!file_exists($usersPath)){
            $userConfFile = fopen($usersPath, "w") or die("Unable to open file!");
            fwrite($userConfFile, "[]");
            fclose($userConfFile);
        } else {
            $userConfFile = fopen($usersPath, "r+") or die("Unable to open file!");
            $usersJson = fread($userConfFile, filesize($usersPath));
            foreach (json_decode($usersJson, true) as $user) {
                $newUser = User::fromDecodedJson($user);
                if($newUser != null){
                    $this->users[$newUser->getName()] = $newUser;
                }
            } 
            fclose($userConfFile);
        }
    }

    function saveConfig()
    {
        $usersPath = $this->configPath . "users.txt";

        # form a json encoded string
        $str = "[";
        foreach ($this->users as $user) {
            $str  = $str . $user->toJson() . ",";
        }
        $str = substr($str, 0, -1);
        $str = $str . "]";

        # write back to disk
        $userConfFile = fopen($usersPath, "w") or die("Unable to open file!");
        fwrite($userConfFile, $str);
        fclose($userConfFile);
    }

    public function addUser($username){
        if($this->hasUser($username)){
            return false;
        } else {
            $this->users[$username] = new User($username);
            $this->saveConfig();
            return true;
        }
    }

    public function hasUser($username){
        if(array_key_exists($username, $this->users)){
            return true;
        }
        return false;
    }
}
