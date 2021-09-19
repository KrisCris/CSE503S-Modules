<?php
// User IO
class IOUtil
{
    public static $path = "/var/www/module2res/";

    public static function saveFile($user, $file, $path="")
    {
        $userPath = self::$path . $user . '/' .$path;
        if (!file_exists($userPath)) {
            mkdir($userPath);
        }
        $savePath = $userPath . $file["name"];
        return move_uploaded_file($file["tmp_name"], $savePath);
    }

    public static function mkdir($user, $path, $currentPath="")
    {
        $userPath = self::$path . $user . '/' .$currentPath.'/'.$path.'/';
        if (!file_exists($userPath)) {
            mkdir($userPath);
            return true;
        } else {
            return false;
        }
    }

    public static function removeFile($user, $path)
    {
        $path = rawurldecode($path);
        $filepath = self::formPath($user, $path);
        if (!file_exists($filepath)) {
            echo "$filepath not exist";
            return;
        } else {
            if (is_dir($filepath)) {
                self::delTree($filepath);
            } else {
                return unlink($filepath);
            }
        }
    }

    # credit https://www.php.net/manual/zh/function.rmdir.php#110489
    public static function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public static function listUserFiles($user, $innerPath = '')
    {
        # make sure every user has its own dir
        $userPath = self::$path . $user . '/';
        if (!file_exists($userPath)) {
            mkdir($userPath);
        }
        $innerPath = rawurldecode($innerPath);
        $dirPath = self::formPath($user, $innerPath);
        $files = scandir($dirPath);
        return $files;
    }

    public static function downloadFile($user, $path)
    {
        ob_clean();
        $path = rawurldecode($path);
        $filepath = self::formPath($user, $path);
        $filename = explode("/", $path);
        $filename = $filename[sizeof($filename) - 1];

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

    public static function readFile($user, $path)
    {
        ob_clean();
        $path = rawurldecode($path);
        $filepath = self::formPath($user, $path);
        $filename = explode("/", $path);
        $filename = $filename[sizeof($filename) - 1];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($filepath);
        header("Content-Type: " . $mime);
        header('content-disposition: inline; filename="' . $filename . '";');
        readfile($filepath);
    }

    public static function formPath($user, $path)
    {
        return self::$path . $user . '/' . $path;
    }

    public static function unzip($user, $path)
    {
        $path = rawurldecode($path);
        $filepath = self::formPath($user, $path);
        $destpath = substr($filepath, 0, strrpos($filepath, '.')) . '/';
        $zip = new ZipArchive;
        $res = $zip->open($filepath);
        if ($res === true) {
            $zip->extractTo($destpath);
            $zip->close();
        }
    }

    public static function zip($user, $path){

    }
}
