<?php
// User IO
class IOUtil
{
    # path to file storage. 
    # This path is not accessible from web browser so it is save.
    public static $path = "/var/www/module2res/";

    public static function saveFile($user, $file, $path = "")
    {
        # user's path
        $userPath = self::$path . $user . '/' . $path;
        # form the absolute file path
        $savePath = $userPath . $file["name"];
        # rename if existed
        $savePath = self::fixFileExisted($savePath);
        # store the file to that path
        return move_uploaded_file($file["tmp_name"], $savePath);
    }

    public static function mkdir($user, $path, $currentPath = "")
    {
        # form the absolute path to the target folder
        $userPath = self::$path . $user . '/' . $currentPath . '/' . $path . '/';
        # making sure folder doesn't exist before create it.
        if (!file_exists($userPath)) {
            mkdir($userPath);
            return true;
        } else {
            return false;
        }
    }

    public static function removeFile($user, $path)
    {
        # we have to encode the path otherwise it will be messed up by post request.
        $path = rawurldecode($path);
        # form the absolute path
        $filepath = self::formPath($user, $path);
        if (!file_exists($filepath)) {
            echo "$filepath not exist";
            return;
        } else {
            if (is_dir($filepath)) {
                # recursively remove data in the folder before delete the folder itself.
                self::delTree($filepath);
            } else {
                # just delete the file
                return unlink($filepath);
            }
        }
    }

    # credit https://www.php.net/manual/zh/function.rmdir.php#110489
    public static function delTree($dir)
    {
        # get files/folders other than . and ..
        $files = array_diff(scandir($dir), array('.', '..'));
        # recursively call delTree if the file is a directory, otherwise just delete it.
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        # remove the folder itself after its empty
        return rmdir($dir);
    }

    # list files in current directory
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

    # just download it.
    public static function downloadFile($user, $path)
    {
        # clean buffer
        ob_clean();
        # form a sbsolute path, get file name
        $path = rawurldecode($path);
        $filepath = self::formPath($user, $path);
        $filename = explode("/", $path);
        $filename = $filename[sizeof($filename) - 1];

        # file IO
        $fp = fopen($filepath, "r");
        $filesize = filesize($filepath);

        # setup proper header for file transmission
        header("Content-type:application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");
        header("Accept-Ranges:bytes");
        header("Accept-Length:$filesize");

        # output data
        $buffer = 1024;
        $buffer_count = 0;
        while (!feof($fp) && $filesize - $buffer_count > 0) {
            $data = fread($fp, $buffer);
            $buffer_count += $buffer;
            echo $data;
        }
        fclose($fp);
    }

    # read file and output on browser. 
    # Browser still begin to download if the file is unable to show by it
    public static function readFile($user, $path)
    {
        # clean buffer;
        ob_clean();
        # fix path
        $path = rawurldecode($path);
        $filepath = self::formPath($user, $path);
        $filename = explode("/", $path);
        $filename = $filename[sizeof($filename) - 1];
        # get file type so that browser will know
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($filepath);
        header("Content-Type: " . $mime);
        header('content-disposition: inline; filename="' . $filename . '";');
        readfile($filepath);
    }

    # just a lazy function to form path
    public static function formPath($user, $path)
    {
        return self::$path . $user . '/' . $path;
    }

    # unzip application/zip typed file
    public static function unzip($user, $path)
    {
        # fix path
        $path = rawurldecode($path);
        $filepath = self::formPath($user, $path);
        $destpath = substr($filepath, 0, strrpos($filepath, '.'));

        # dir name existed, rename it
        $destpath = self::fixFolderExisted($destpath);

        $zip = new ZipArchive;
        $res = $zip->open($filepath);
        # if file is extractable, then do it.
        if ($res === true) {
            $zip->extractTo($destpath);
            $zip->close();
            return true;
        }
        return false;
    }

    # credit https://stackoverflow.com/questions/4914750/how-to-zip-a-whole-folder-using-php
    public static function zip($user, $path)
    {
        # we have to encode the path otherwise it will be messed up by post request.
        $path = rawurldecode($path);
        # form the absolute path
        $filepath = self::formPath($user, $path);
        // $filepath = substr($filepath, strrpos($filepath, '/')+1, strlen($filepath)-(strrpos($filepath, '/')+1));
        if(strrpos($filepath, '/')==strlen($filepath)-1){
            $filepath = substr($filepath,0, -1);
        }
        $fname = $filepath.".zip";
        $fname = self::fixFileExisted($fname);

        if(count(scandir($filepath))<3){
            return false;
        }

        $zip = new ZipArchive();
        $zip->open($fname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($filepath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($filepath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }
        // Zip archive will be created only after closing object
        $zip->close();
    }

    # copy one file to another place.
    public static function shareTo($sharedPath, $user, $destPath)
    {
        $sharedPath = self::$path . $sharedPath;
        $filename = basename($sharedPath);
        if (is_dir($sharedPath)) {
            return self::dirCopy($sharedPath, self::formPath($user, $destPath) . $filename);
        } else {
            return copy($sharedPath, self::formPath($user, $destPath) . $filename);
        }
    }

    # credit https://www.php.net/manual/zh/function.copy.php#104020
    public static function dirCopy($src, $dest)
    {
        # dir name existed, rename it
        $dest = self::fixFolderExisted($dest);

        if (is_dir($src)) {
            # create the dir before copy
            mkdir($dest);
            $files = scandir($src);
            foreach ($files as $file)
                # don't copy . and ..
                if ($file != "." && $file != "..") {
                    # continuously mkdir
                    self::dirCopy("$src/$file", "$dest/$file");
                }
            # copy files to the dir
        } else if (file_exists($src)) copy($src, $dest);
        return true;
    }

    # rename if already existed
    public static function fixFolderExisted($dest)
    {
        $idx = 2;
        while (file_exists($dest)) {
            $dest = $dest . "_$idx";
            $idx++;
        }
        return $dest;
    }

    public static function fixFileExisted($dest)
    {
        $idx = 2;
        while (file_exists($dest)) {
            $path_parts = pathinfo($dest);
            $fn = $path_parts['filename'] . "_$idx";
            $dest = substr($dest, 0, strrpos($dest, "/") + 1) . $fn . "." . $path_parts["extension"];
            $idx++;
        }
        return $dest;
    }
}
