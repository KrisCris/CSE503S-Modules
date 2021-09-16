<?php
class IOUtil{
    public static $path = "../../../../module2res/";

    public static function saveFile($user, $file){
        $userPath = self::$path.$user.'/';
        if(!file_exists($userPath)){
            mkdir($userPath);
        }
        $savePath = $userPath.$file["name"];
        // echo $savePath.'<br>';
        // echo $file["tmp_name"].'<br>';
        return move_uploaded_file($file["tmp_name"], $savePath);
    }

    public static function removeFile($user, $path){
        $filepath = self::$path.$user."/".$path;
        if(!file_exists($filepath)){
            echo "$filepath not exist";
            return;
        } else {
            return unlink($filepath);
        }

    }
    
}
?>