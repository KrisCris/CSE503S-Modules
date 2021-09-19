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
    <!-- Just my github avatar and links -->
    <a id="avatar" href="https://github.com/KrisCris"><img id="rounded" src="https://avatars.githubusercontent.com/u/38860226?v=4" alt="avatar"></a>
    
    <!-- Greeting to users -->
    <div id="hello">
        <p>HELLO WORLD!</p>
        <p>Dear
            <!-- Get username if user is logged in -->
            <!-- Otherwise redirect to index.php -->
            <span id="username">
                <?php
                session_start();
                if (!isset($_SESSION["username"])) {
                    header("Location: index.php");
                } else {
                    # Use dirname to make sure the path won't be messed up when it also requiring other php files.
                    require dirname(__FILE__) . '/../api/model/DataManager.php';
                    # Get Singleton Instance DataManager.
                    $DM = DataManager::getInstance();

                    # Get username.
                    $username = $_SESSION["username"];
                    # If username not exist in our list or users.txt managed by DataManager, redirect to index.php
                    if (!$DM->hasUser($username)) {
                        header("Location: index.php");
                    }
                    echo $_SESSION["username"];
                }
                ?>
            </span>
            :)
        </p>
    </div>

    <!-- Button for logout -->
    <form action="../api/logout.php" method="post">
        <input id="logout" type="submit" value="Logout">
    </form>

    <div id="filelist">
        <p class="filelistprompt">Following Are Your Files!</p>
        
        <!-- PHP iteratively rendered file lists. -->
        <div>
            <?php
            # File manipulation
            # We use flag_name == 1 to determine which button is pressed.
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
                if (isset($_POST["zip"]) && $_POST["zip"] == 1){
                    IOUtil::zip($username, $fpath);
                }
            }

            # innerPath is used for tracking current level of directory, 
            # relative path for sub-directory in the user's file space.
            $innerPath = "";
            # When user want to go to some sub-directory.
            if (isset($_POST["innerPath"])) {
                $innerPath = $_POST["innerPath"];
                $innerPath = rawurldecode($innerPath);
            }

            # The path of folder user is currently in.
            $containerPath = IOUtil::$path . $username . "/" . $innerPath;
            # init for finfo, which helps us determine file types.
            $finfo = new finfo(FILEINFO_MIME_TYPE); ?>

            <!-- Current directory indicator -->
            <p class="filelistprompt" id="pathIndicator">Current Path: <?php echo '/'.$innerPath; ?></p>

            <!-- File receive(sharing) button -->
            <form id="receive" action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post">
                <!-- We use this line of code to let the the site stay in current directory after request is made. -->
                <!-- We don't want this line exist when user is not in sub-directory, because the HTML validator would say `value=` is not syntax correct -->
                <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                
                <!-- mkdir -->
                <input type="text" name="sharingKey" placeholder=
                    <?php
                    // We put the code here is only to save some space printing the error information.
                    if (isset($_POST["receive"]) && $_POST["receive"] == 1) {
                        $key = $_POST["sharingKey"];
                        # Making sure the sharing key is legit.
                        if (!preg_match('/^[\w_\.\-]+$/', $key) || $key == "") {
                            echo "InvalidKey!";
                        } else {
                            if ($DM->receiveFile($username, $innerPath, $key)) {;
                            } else {
                                # if php is unable to create the folder, throw error.
                                echo "KeyNotWorking!";
                            }
                        }
                    } else {
                        echo "SharingKey";
                    }
                    ?>><br>
                <!-- the flag indicating which btn is pressed -->
                <input type="text" name="receive" value="1" class="hide">
                <input type="submit" value="Receive File">
            </form>

            <!-- File sharing link -->
            <?php
                if(isset($_POST["share"]) && $_POST["share"] == 1){?>
            <p class = "filelistprompt" id="shareLink">
                <?php
                    $fp = $_POST["filePath"];
                    echo "ShareKey for [".rawurldecode($fp). "] : ".$DM->shareFile($username, $fp);
                ?>
            </p><?php }?>

            <!-- A button for file upload -->
            <!-- We put it here because we need to use innerPath to determine the place to upload -->
            <form id="upload" action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post" enctype="multipart/form-data">
                <input type="file" name="file" id="file"><br>
                <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                <input type="submit" value="Upload">
                <?php
                # Upload
                if (isset($_FILES["file"])) {
                    $file = $_FILES["file"];
                    // We don't limit the name of file, as if the name is not allowed in the file system, user cannot have it in their computer
                    // And since we don't have SQL and things like that, so we don't need to worry about the file name at the moment.
                    // But we do filter the name when output to the website, making sure it won't introduce new codes interpreted by browser.
                    echo IOUtil::saveFile($username, $file, $innerPath) ? '' : 'An Error Occured!';
                }
                ?>
            </form>

            <!-- Button for creating new directory -->
            <form id="mkdir" action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post">
                <!-- We use this line of code to let the the site stay in current directory after request is made. -->
                <!-- We don't want this line exist when user is not in sub-directory, because the HTML validator would say `value=` is not syntax correct -->
                <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                
                <!-- mkdir -->
                <input type="text" name="dirName" placeholder=
                    <?php
                        // We put the code here is only to save some space printing the error information.
                        if (isset($_POST["mkdir"]) && $_POST["mkdir"] == 1) {
                            $dirName = $_POST["dirName"];
                            # Making sure the folder name is legit.
                            if (!preg_match('/^[\w_\.\-]+$/', $dirName) || $dirName == "") {
                                echo "InvalidName!";
                            } else {
                                if (IOUtil::mkdir($username, $dirName, $innerPath)) {;
                                } else {
                                    # if php is unable to create the folder, throw error.
                                    echo "AlreadyExist";
                                }
                            }
                        } else {
                            echo "FolderName";
                        }
                        ?>><br>
                <!-- the flag indicating which btn is pressed -->
                <input type="text" name="mkdir" value="1" class="hide">
                <input type="submit" value="Create Folder">
            </form>

            <!-- Render a back button when user is in a sub-directory. -->
            <?php
            if ($innerPath != "") { ?>
                <div class="fileCard">
                    <img src='res/icon_back.png' alt="icon"  style="zoom:100%;"><br><br><br>
                    <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                        <input type="text" name="innerPath" class="hide" value=
                        <?php
                            // Remove on layer of directory from innerPath
                            $outPath = substr($innerPath, 0, strrpos($innerPath, '/'));
                            $outPath = substr($outPath, 0, strrpos($outPath, '/'));
                            echo $outPath == "" ? rawurlencode("") : rawurlencode($outPath) . '/' 
                        ?>>
                        <input class="btn" type="submit" value="Back" style="margin-left:70px;">
                    </form>
                </div>

            <!-- Iteratively render the file list!!! -->
            <?php }
            foreach (IOUtil::listUserFiles($username, $innerPath) as $idx => $filename) {
                // The absolute path to the file
                $filePath = $containerPath . $filename;
                // The path of file relative to the user's base folder.
                $relativePath = $innerPath . $filename;
                // Hide . and .. as these would causing trouble.
                if ($filename == "." || $filename == "..") {
                    continue;
                }
                ?>
                
                <!-- Template for each file -->
                <div class="fileCard">
                    <!-- icon -->
                    <img alt="icon" src=
                        <?php 
                            // apply different icons for folder and file
                            if (is_dir($filePath)) echo "res/icon_folder.gif";
                            else echo "res/icon_file.gif"; 
                        ?>  
                        style="zoom:20%;">

                    <!-- File name, making sure no script is excutable -->
                    <p><?php echo htmlentities($filename) ?></p>

                    <!-- File Exclusive buttons -->
                    <?php if (!is_dir($filePath)) { ?>
                        <!-- preview -->
                        <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                            <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                            <input type="text" name="preview" class="hide" value="1">
                            <input class="btn" type="submit" value="Preview">
                        </form>
                        <!-- download -->
                        <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                            <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                            <input type="text" name="download" class="hide" value="1">
                            <input class="btn" type="submit" value="Download">
                        </form>
                    <?php } else { ?>

                        <!-- Folder Exclusive buttons -->
                        <!-- open folder -->
                        <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                            <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($relativePath . '/') ?>>
                            <input class="btn" type="submit" value="Open">
                        </form>

                        <!-- zip folders -->
                        <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                            <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                            <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                            <input type="text" name="zip" class="hide" value="1">
                            <input class="btn" type="submit" value="Compress">
                        </form>
                    <?php } ?>

                    <!-- Common buttons-->
                    <!-- delete -->
                    <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                        <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                        <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                        <input type="text" name="delete" class="hide" value="1">
                        <input class="btn" type="submit" value="Delete">
                    </form>

                    <!-- File share btn -->
                    <form class="fc" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                        <input type="text" name="filePath" class="hide" value=<?php echo rawurlencode($relativePath) ?>>
                        <?php if($innerPath!=""){?> <input type="text" name="innerPath" class="hide" value=<?php echo rawurlencode($innerPath) ?>><?php }?>
                        <input type="text" name="share" class="hide" value="1">
                        <input class="btn" type="submit" value="Share">
                    </form>
                    
                    <!-- provide unzip button for zip archives -->
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
            <?php }?>
        </div>
    </div>
</body>

</html>