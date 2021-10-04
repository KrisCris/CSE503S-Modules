<?php
if (isset($_SESSION["uid"])){
# check editor mode
if((isset($_GET["newStory"]) && $_GET["newStory"]==1)|| (isset($_GET["editStory"]) && $_GET["editStory"]==1 && isset($_GET["storyId"]))){ 
    $title = "";
    $content = "";
    $link = "";
    if(isset($_GET["editStory"]) && $_GET["editStory"]==1){
        $s = Story::getStoryById($_GET['storyId']);
        if ($s->userId == $_SESSION['uid']){
            $title = $s->title;
            $content = $s->content;
            $link = $s->link;
        } else {
            unset($_GET["editStory"]);
            unset($_GET["storyId"]);
        }
    }
?>

<!-- render htmls -->
<div class="editor">
    <form class="editor" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <textarea <?php  ?> name="title" id="title" cols="30" rows="3" placeholder="Title"><?php echo $title; ?></textarea><br>
        <textarea name="content" id="content" cols="30" rows="20" placeholder="Content"><?php echo $content; ?></textarea><br>
        <textarea name="link" id="link" cols="30" rows="3" placeholder="Link"><?php echo $link; ?></textarea><br>
        <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>">
        <input type="hidden" name="submitStory" value="1">
        <input type="hidden" name="storyId" value="<?php echo !isset($_GET["storyId"])?-1:$_GET['storyId'];?>">
        <input class="editorBtn btn1" type="submit" value="Submit">
    </form>
    <form class="editor" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
        <?php if (isset($_GET["editStory"])){?> <input type="hidden" name="storyId" value="<?php echo $_GET['storyId']?>"> <?php }?>
        <input class="editorBtn" type="submit" value="Cancel" />
    </form>
</div>

<?php }}?>