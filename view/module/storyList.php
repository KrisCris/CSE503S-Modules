<div id="storyList">
    <?php if(!isset($_GET["newStory"]) && !isset($_GET["storyId"]) && !isset($_GET["editStory"])){
    $count = Story::countStories();
    $perPage = 10;
    $page = ceil($count / $perPage);
    if(isset($_GET['page'])){
        $currentPage = $_GET['page'];
        if($currentPage<$page){
            $begin = $currentPage*$perPage;
        } else {
            $currentPage = $page-1;
            $begin = $currentPage*$perPage;
        }
    } else {
        $currentPage = 0;
        $begin = 0;
    }
    $li = Story::getStoryList($begin, $perPage);

    foreach ($li as $story) { ?>
    <div class="storyCard">
        <div>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']."?storyId=$story->id"); ?>">
                <h3><?php echo $story->title;?></h3>
            </a>
            <p><?php if (strlen($story->content)>100) echo substr($story->content, 0, 100).'......'; else echo $story->content;?></p>
        </div>

        <div>
            <p>Time: <?php echo $story->time;?></p>
            <p>Auther: <?php echo $story->username;?></p>
            <p>Likes: <?php echo $story->rate;?></p>
            <p>Views: <?php echo $story->click;?></p>
        </div>
    </div>
    <?php }

    for($i=0; $i<$page; $i++){ ?>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
        <input type="hidden" name="page" value="<?php echo $i; ?>">
        <input <?php if ($i == $currentPage) echo "class='btnCurrentPage'"; ?> type="submit" value="<?php echo $i+1; ?>">
    </form>
    <?php }
    }

    ?>
</div>