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
                <?php
                session_start();
                if (!isset($_SESSION["username"])) {
                    header("Location: index.php");
                } else {
                    $username = $_SESSION["username"];
                    require dirname(__FILE__) . '/../api/model/DataManager.php';
                    $DM = DataManager::getInstance();
                    if (!$DM->hasUser($username)) {
                        header("Location: index.php");
                    }
                }
                echo $_SESSION["username"];

                ?>
            </span>
            :)
        </p>
    </div>
    <form action="../api/logout.php" method="post">
        <input id="logout" type="submit" value="Logout">
    </form>
    <div id="filelist">
        <p class="filelistprompt">Following Are Your Files!</p>
        <p></p>
        <div>
            <?php
            # File manipulation
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
                if (isset($_POST["unzip"]) && $_POST["unzip"] == 1) {
                    IOUtil::unzip($username, $fpath);
                    # no exit; since we want to have file list refreshed
                }
            }
            # File Listing
            $innerPath = "";
            if (isset($_POST["innerPath"])) {
                $innerPath = $_POST["innerPath"];
                $innerPath = rawurldecode($innerPath);
            }
            $containerPath = IOUtil::$path . $username . "/" . $innerPath;
            $finfo = new finfo(FILEINFO_MIME_TYPE); ?>

            <p class="filelistprompt">Current Path: <?php echo $containerPath; ?></p>

            <form id="upload" action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post" enctype="multipart/form-data">
                <input type="file" name="file" id="file"><br>
                <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                <input type="submit" value="Upload">
                <?php
                # Upload
                if (isset($_FILES["file"])) {
                    $file = $_FILES["file"];
                    // $innerPath = $_POST["innerPath"]
                    echo IOUtil::saveFile($username, $file, $innerPath) ? '' : 'An Error Occured!';
                }
                ?>
            </form>

            <form id="mkdir" action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post">
                <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                <input type="text" name="dirName" placeholder=<?php
                                                                # mkdir
                                                                if (isset($_POST["mkdir"]) && $_POST["mkdir"] == 1) {
                                                                    $dirName = $_POST["dirName"];
                                                                    if (!preg_match('/^[\w_\.\-]+$/', $dirName) || $dirName == "") {
                                                                        echo "InvalidName!";
                                                                    } else {
                                                                        if (IOUtil::mkdir($username, $dirName, $innerPath)) {;
                                                                        } else {
                                                                            echo "ErrorOccured!";
                                                                        }
                                                                    }
                                                                } else {
                                                                    echo "FolderName";
                                                                }
                                                                ?>><br>
                <input type="text" name="mkdir" value="1" class="hide">
                <input type="submit" value="Create Folder">
            </form>

            <?php
            if ($innerPath != "") { ?>
                <div class="fileCard">
                    <img src='res/icon_back.png' alt="icon"  style="zoom:100%;"><br><br><br>
                    <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                        <input type="text" name="innerPath" class="hide" value=<?php
                                                                            $outPath = substr($innerPath, 0, strrpos($innerPath, '/'));
                                                                            $outPath = substr($outPath, 0, strrpos($outPath, '/'));
                                                                            echo $outPath == "" ? rawurlencode("") : rawurlencode($outPath) . '/' ?>>
                        <input class="btn" type="submit" value="Back" style="margin-left:70px;">
                    </form>
                </div>
            <?php }

            foreach (IOUtil::listUserFiles($username, $innerPath) as $idx => $filename) {
                $filePath = $containerPath . $filename;
                $relativePath = $innerPath . $filename;
                // if ($innerPath == "") {
                if ($filename == "." || $filename == "..") {
                    continue;
                }
                // }
            ?>
                <div class="fileCard">
                    <img alt="icon" src=<?php if (is_dir($filePath)) echo "res/icon_folder.gif";
                                else echo "res/icon_file.gif"; ?>  style="zoom:20%;">
                    <p><?php echo $filename ?></p>
                    <?php if (!is_dir($filePath)) { ?>
                        <!-- File Exclusive -->
                        <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                            <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                            <input type="text" name="preview" class="hide" value="1">
                            <input class="btn" type="submit" value="Preview">
                        </form>
                        <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                            <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                            <input type="text" name="download" class="hide" value="1">
                            <input class="btn" type="submit" value="Download">
                        </form>
                    <?php } else { ?>
                        <!-- Folder Exclusive -->
                        <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                            <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($relativePath . '/') ?>>
                            <input class="btn" type="submit" value="Open">
                        </form>
                    <?php } ?>
                    <!-- Common -->
                    <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                        <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                        <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                        <input type="text" name="delete" class="hide" value="1">
                        <input class="btn" type="submit" value="Delete">
                    </form>
                    <?php
                    $mime = $finfo->file($filePath);
                    if ($mime == "application/zip") { ?>
                    <!-- Unzip -->
                        <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                            <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                            <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                            <input type="text" name="unzip" class="hide" value="1">
                            <input class="btn" type="submit" value="Unzip">
                        </form>
                    <?php } ?>
                </div>
            <?php
            }

            ?>
        </div>

    </div>
</body>

</html>