<?php
class IOUtil
{
    public static $path = "/var/www/module2res/";

    public static function saveFile($user, $file)
    {
        $userPath = self::$path . $user . '/';
        if (!file_exists($userPath)) {
            mkdir($userPath);
        }
        $savePath = $userPath . $file["name"];
        return move_uploaded_file($file["tmp_name"], $savePath);
    }

    public static function removeFile($user, $path)
    {
        $filepath = self::formPath($user, $path);
        if (!file_exists($filepath)) {
            echo "$filepath not exist";
            return;
        } else {
            return unlink($filepath);
        }
    }

    public static function listUserFiles($user, $innerPath = '')
    {
        $dirPath = self::formPath($user, $innerPath);
        echo $dirPath . "<br>";
        $files = scandir($dirPath);
        return $files;
    }

    public static function downloadFile($user, $path)
    {
        ob_clean();
        $filepath = self::formPath($user, $path);
        $filename = explode("/",$path);
        $filename = $filename[sizeof($filename)-1];
        
        $fp = fopen($filepath, "r");
        $filesize = filesize($filepath);

        header("Content-type:application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");
        header("Accept-Ranges:bytes");
        header("Accept-Length:$filesize");

        $buffer = 1024;
        $buffer_count = 0;
        while (!feof($fp) && $filesize - $buffer_count > 0) {
            $data = fread($fp, $buffer);
            $buffer_count += $buffer;
            echo $data;
        }
        fclose($fp);
    }

    public static function formPath($user, $path)
    {
        return self::$path . $user . '/' . $path;
    }
}
