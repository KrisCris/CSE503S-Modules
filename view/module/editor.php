<?php
if (isset($_SESSION["uid"])){
# handle requests
if(isset($_POST["submitStory"]) && isset($_POST["token"]) && $_POST["submitStory"] == 1){
    $title = $_POST["title"];
    $content = $_POST["content"];
    $link = $_POST["link"];
    $storyId = $_POST["storyId"];
    $_GET["storyId"] = Story::addStory($_SESSION['uid'], $title, $content, $link, $storyId);
}

# check editor mode
if((isset($_GET["newStory"]) && $_GET["newStory"]==1)|| (isset($_GET["editStory"]) && $_GET["editStory"]==1 && isset($_GET["storyId"]))){ 
    $title = "";
    $content = "";
    $link = "";
    if(isset($_GET["editStory"]) && $_GET["editStory"]==1){
        $s = Story::getStoryById($_GET['storyId']);
        $title = $s->title;
        $content = $s->content;
        $link = $s->link;
    }
?>

<!-- render htmls -->
<div class="editor">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <textarea <?php  ?> name="title" id="title" cols="30" rows="1" placeholder="Title"><?php echo $title; ?></textarea><br>
        <textarea name="content" id="content" cols="30" rows="10" placeholder="Content"><?php echo $content; ?></textarea><br>
        <textarea name="link" id="link" cols="30" rows="2" placeholder="Link"><?php echo $link; ?></textarea><br>
        <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>">
        <input type="hidden" name="submitStory" value="1">
        <input type="hidden" name="storyId" value="<?php echo !isset($_GET["storyId"])?-1:$_GET['storyId'];?>">
        <input type="submit" value="Submit">
    </form>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <input type="submit" value="Cancel" />
    </form>
</div>

<?php }}?>