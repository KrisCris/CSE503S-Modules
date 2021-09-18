<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action=<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="text" name="username" id="username" placeholder="username">
        <input type="text" name="filename" id="filename" placeholder="filename">
        <input type="text" name="rm" id="rm" placeholder="rm-marker 1/0">
        <input type="text" name="dl" id="dl" placeholder="dl_marker 1/0">
        <input type="text" name="ls" id="ls" placeholder="ls_marker 1/0">
        <input type="submit" value="UPLOAD">
    </form>
</body>

</html>

<?php

require '../IOUtil.php';
if (isset($_FILES["file"]) && isset($_POST["username"])) {
    $file = $_FILES["file"];
    $username = $_POST["username"];
    echo "ADD:" . IOUtil::saveFile($username, $file) ? 'true' : 'false' . "<br>";
}


if (isset($_POST["rm"]) && isset($_POST["dl"]) && isset($_POST["filename"])) {
    $filename = $_POST["filename"];
    $rm = $_POST["rm"];
    $dl = $_POST["dl"];

    if ($rm == 1) {
        echo "DEL:" . IOUtil::removeFile($username, $filename) ? 'true' : 'false' . "<br>";
    }
    if ($dl == 1) {
        echo "Download" . IOUtil::downloadFile($username, $filename);
    }
}


if (isset($_POST["ls"])) {
    $ls = $_POST["ls"];
    if ($ls == 1) {
        foreach (IOUtil::listUserFiles($username) as $value) {
            echo $value . "<br>";
        }
    }
}


// $path = "../../../../module2res/";
// $username = 'pingchuanhuang/';
// if(!file_exists($path.$username)){
//     echo $path.$username;
//     mkdir($path.$username);
// }

// $fileuploaded = "word.txt";
// $file = fopen($path.$username.$fileuploaded, "w") or die("existed?");
// fwrite($file, "test");
// fclose($file);

?>