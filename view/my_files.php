<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/my_files.css">
    <title>MyFiles</title>
</head>

<body>
    <a id="avatar" href="https://github.com/KrisCris"><img id="rounded" src="https://avatars.githubusercontent.com/u/38860226?v=4" alt="avatar"></a>
    <div id="hello">
        <p>HELLO WORLD!</p>
        <p>Dear
            <span id="username">
                <?php session_start();
                if (!isset($_SESSION["username"])) {
                    header("Location: index.php");
                }
                echo $_SESSION["username"];
                $username = $_SESSION["username"];
                require dirname(__FILE__) . "/../api/IOUtil.php";
                ?>
            </span>
            :)
        </p>
    </div>
    <form action="../api/logout.php" method="post">
        <input id="logout" type="submit" value="Logout">
    </form>
    <div id="filelist">
        <p id="filelistprompt">Following Are Your Files!</p>
        <form id="upload" action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post" enctype="multipart/form-data">
            <input type="file" name="file" id="file"><br>
            <input type="submit" value="Upload">
            <?php
            # Upload
            if (isset($_FILES["file"])) {
                $file = $_FILES["file"];
                echo IOUtil::saveFile($username, $file) ? '' : 'An Error Occured!';
            }
            ?>
        </form>
        <div>
            <?php
            # Download/Preview
            if (isset($_POST["filePath"])) {
                $fpath = $_POST["filePath"];
                if (isset($_POST["preview"]) && $_POST["preview"] == 1) {
                    IOUtil::readFile($username, $fpath);
                    exit;
                }
                if (isset($_POST["download"]) && $_POST["download"] == 1) {
                    IOUtil::downloadFile($username, $fpath);
                    exit;
                }
                if (isset($_POST["delete"]) && $_POST["delete"] == 1) {
                    IOUtil::removeFile($username, $fpath);
                    # no exit; since we want to have file list refreshed
                }
            }

            # File Listing
            $innerPath = "";
            if (isset($_POST["innerPath"])) {
                $innerPath = $_POST["innerPath"];
            }
            $containerPath = IOUtil::$path . $username . "/" . $innerPath;
            foreach (IOUtil::listUserFiles($username, $innerPath) as $idx => $filename) {
                $filePath = $containerPath . $filename;
                $relativePath = $innerPath . $filename;
                if ($innerPath == "") {
                    if ($filename == "." || $filename == "..") {
                        continue;
                    }
                }
            ?>
                <div id="fileCard">
                    <img src=<?php if (is_dir($filePath)) echo "res/icon_folder.gif";
                                else echo "res/icon_file.gif"; ?> alt="" style="zoom:20%;">
                    <p><?php echo $filename ?></p>
                    <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                        <input type="text" name="filePath" id="hide" value=<?php echo rawurlencode($relativePath) ?>>
                        <input type="text" name="preview" id="hide" value="1">
                        <input id="btn" type="submit" value="Preview">
                    </form>
                    <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                        <input type="text" name="filePath" id="hide" value=<?php echo rawurlencode($relativePath) ?>>
                        <input type="text" name="download" id="hide" value="1">
                        <input id="btn" type="submit" value="Download">
                    </form>
                    <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                        <input type="text" name="filePath" id="hide" value=<?php echo rawurlencode($relativePath) ?>>
                        <input type="text" name="delete" id="hide" value="1">
                        <input id="btn" type="submit" value="Delete">
                    </form>

                </div>
            <?php
            }

            ?>
        </div>

    </div>
</body>

</html>