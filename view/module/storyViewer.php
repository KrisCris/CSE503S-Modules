<?php
if (isset($_POST["delete"]) && $_POST["delete"]==1){
    Story::deleteStory($_POST['storyId'])?  :$_GET["storyId"] = $_POST['storyId'];
}
if (isset($_GET["storyId"])) {
    # make sure it is not in other modes
    if (!isset($_GET["newStory"]) && !isset($_GET["editStory"])) {
        if ($s = Story::getStoryById($_GET["storyId"])) { ?>
        <div class="storyViewer">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <input type="submit" value="Back">
            </form>
            <h1><?php echo $s->title; ?></h1>
            <p><?php echo $s->content; ?></p>
            <?php if ($s->link){ ?> <a href="<?php echo $s->link; ?>"><?php echo 'Link: '.$s->link; ?></a> <?php } ?>
        </div>

        <?php
        # if logged in and is author of the story, enable editing mode.
        if (isset($_SESSION["uid"])) {
            if ($u->id == $s->userId) {
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <input type="hidden" name="delete" value=1>
            <input type="hidden" name="storyId" value=<?php echo $s->id; ?>>
            <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>">
            <input type="submit" value="Delete">
        </form>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
            <input type="hidden" name="editStory" value=1>
            <input type="hidden" name="storyId" value=<?php echo $s->id; ?>>
            <input type="submit" value="Edit">
        </form>
<?php }
            }
        } else {
            echo "<h1>Story Not Found!</h1>";
        }
    }
} ?>