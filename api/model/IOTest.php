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
        <input type="submit" value="UPLOAD">
    </form>
</body>

</html>

<?php

require '../IOUtil.php';
$file = $_FILES["file"];
$username = $_POST["username"];
if(isset($file) && isset($username)){
    echo "ADD:".IOUtil::saveFile($username,$file)? 'true' : 'false'."<br>";
}


$filename = $_POST["filename"];
$rm = $_POST["rm"];
if(isset($filename) && isset($rm)){
    if($rm == 1){
        echo "DEL:". IOUtil::removeFile($username, $filename)?'true' : 'false'."<br>";
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