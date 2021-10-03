<?php
if (isset($_GET["storyId"])) {
    # skip rendering this part if the page is executing for other tasks.
    if (!isset($_GET["newStory"]) && !isset($_GET["editStory"])) {
        if ($s = Story::getStoryById($_GET["storyId"])) { $s->viewed();?>
            <!-- story -->
            <div class="storyViewer">
                <!-- return btn -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <input id="btnBack" type="submit" value="Back">
                </form>
                <!-- story itself -->
                <div class="content singleView">
                    <h1><?php echo $s->title; ?></h1>
                    <p><?php echo $s->content; ?></p>
                    <?php if ($s->link) { ?> <a href="<?php echo $s->link; ?>"><?php echo 'Link: ' . $s->link; ?></a> <?php } ?>
                </div>
                <!-- some data of the story -->
                <div class="meta singleView">
                    <p>Time: <?php echo $s->time;?></p>
                    <p>Auther: <?php echo $s->username;?></p>
                    <p>Likes: <?php echo $s->rate;?></p>
                    <p>Views: <?php echo $s->click;?></p>
                </div>
            </div>

            <?php
            # operations available for users logged in.
            if (isset($_SESSION["uid"])) {
                if ($u->id == $s->userId) {?>
                    <!-- story owner can modify story -->
                    <div class="singleView">
                        <form class="svBtn" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                            <input type="hidden" name="delete" value=1>
                            <input type="hidden" name="storyId" value=<?php echo $s->id; ?>>
                            <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>">
                            <input type="submit" value="Delete">
                        </form>
                        <form class="svBtn" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
                            <input type="hidden" name="editStory" value=1>
                            <input type="hidden" name="storyId" value=<?php echo $s->id; ?>>
                            <input type="submit" value="Edit">
                        </form>
                    </div>
            <?php } 
                $rate = Story::getMyRate($u->id, $s->id);
            ?>
            <div class="singleView">
                <form class="svBtn" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <input type="hidden" name="rate" value=1>
                    <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>">
                    <input type="hidden" name="storyId" value=<?php echo $s->id; ?>>
                    <input <?php echo $rate == 1 ? "class='btnSelected'": null ?> type="submit" value="Like">
                </form>
                <form class="svBtn" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <input type="hidden" name="rate" value=-1>
                    <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>">
                    <input type="hidden" name="storyId" value=<?php echo $s->id; ?>>
                    <input <?php echo $rate == -1 ? "class='btnSelected'": null ?> type="submit" value="Dislike">
                </form>
            </div>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                
                </form>
            <?php
            }
        } else {
            echo "<h1>Story Not Found!</h1>";
        }
    }
} 
require dirname(__FILE__) . '/comments.php';
?>