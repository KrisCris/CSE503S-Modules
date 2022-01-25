<?php

require dirname(__FILE__)."/User.php";
require dirname(__FILE__)."/../IOUtil.php";

class DataManager
{
    private static $instance = null;

    private $configPath;
    private $users;
    private $fileShareKeys;

    # private constructor as it is a singleton instance
    private function __construct()
    {
        $this->configPath = IOUtil::$path . ".config/";
        $this->users = array();
        $this->fileShareKeys = array();
    }

    private function __clone()
    {
        // disabled clone
    }

    # get the singleton instance.
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

    # read necessary data from disk when DataManger init.
    # Currently it only reads data from users.txt
    public function readConfig(){
        # create the .config dir if not exist
        if (!file_exists($this->configPath)){
            mkdir($this->configPath);
        }
        $usersPath = $this->configPath . "users.txt";
        $sharedKeysPath = $this->configPath. "sharedKeys.txt";

        # create user config if not exist
        if (!file_exists($usersPath)){
            $userConfFile = fopen($usersPath, "w") or die("Unable to open file!");
            fwrite($userConfFile, "[]");
            fclose($userConfFile);

        # otherwise read user data from file
        } else {
            $userConfFile = fopen($usersPath, "r+") or die("Unable to open file!");
            $usersJson = fread($userConfFile, filesize($usersPath));
            # create user instances from data read from file
            foreach (json_decode($usersJson, true) as $user) {
                $newUser = User::fromDecodedJson($user);
                if($newUser != null){
                    $this->users[$newUser->getName()] = $newUser;
                }
            } 
            fclose($userConfFile);
        }
        # create sharedkey data file if not exist
        if (!file_exists($sharedKeysPath)){
            $sharedKeysFile = fopen($sharedKeysPath, "w") or die("Unable to open file!");
            fwrite($sharedKeysFile, "{}");
            fclose($sharedKeysFile);
        } else {
            $sharedKeysFile = fopen($sharedKeysPath, "r+") or die("Unable to open file!");
            $usersJson = fread($sharedKeysFile, filesize($sharedKeysPath));
            # create user instances from data read from file
            $this->fileShareKeys = json_decode($usersJson, true);
            fclose($sharedKeysFile);
        }
    }

    # save users/sharing data back to disk
    function saveConfig()
    {
        $usersPath = $this->configPath . "users.txt";
        $sharedKeysPath = $this->configPath. "sharedKeys.txt";

        # form a json encoded string from user objs
        $str = "[";
        foreach ($this->users as $user) {
            $str  = $str . $user->toJson() . ",";
        }
        $str = substr($str, 0, -1);
        $str = $str . "]";

        # sharedkey array to json str
        $skeys = json_encode($this->fileShareKeys);

        # write back to disk
        $userConfFile = fopen($usersPath, "w") or die("Unable to open file!");
        fwrite($userConfFile, $str);
        fclose($userConfFile);

        $sharedKeysFile = fopen($sharedKeysPath,"w") or die("Unable to open file!");
        fwrite($sharedKeysFile, $skeys);
        fclose($sharedKeysFile);
    }

    # Add user to users.txt and our runtime user list when new user registered.
    public function addUser($username){
        # if name existed
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

    # create a unique string for file sharing
    public function shareFile($user, $path){
        $uuid = uniqid($user.'AT');
        $path = rawurldecode($path);
        $relativePath = $user.'/'.$path;
        if(in_array($relativePath, $this->fileShareKeys)){
            unset($this->fileShareKeys[array_search($relativePath, $this->fileShareKeys)]);
        }
        # if key existed, re-generate.
        while(array_key_exists($uuid, $this->fileShareKeys)){
            $uuid = uniqid($user.'AT');
        }
        
        # map the key to shared file's path
        $this->fileShareKeys[$uuid] = $relativePath;
        $this->saveConfig();
        return $uuid;
    }

    public function receiveFile($user, $path, $uuid){
        $sharedPath = null;
        if(array_key_exists($uuid, $this->fileShareKeys)){
            $sharedPath = $this->fileShareKeys[$uuid];
            # One time usage, pop from the list.
            unset($this->fileShareKeys[$uuid]);
            $this->saveConfig();
            # true if operation is succeed, or some internal error occured.
            return IOUtil::shareTo($sharedPath, $user, $path);
        } else {
            return false;
        }

    }
}
