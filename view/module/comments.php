<div id="comments">
    <!-- comment editor -->
    <form class="cmtForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <?php 
            $commentTxt="";
            $btnTxt="Comment";
            $placeholderCmt = "Comment on the story!";
            if(isset($_GET['editComment']) && $_GET['editComment']==1 && isset($_GET['commentId'])){
                $c = Comment::getCommentsById($_GET['commentId']);
                if ($c->userId == $_SESSION['uid']){
                    $commentTxt = $c->comment;
                    $btnTxt = "Save";
                } else {
                    unset($_GET['editComment']);
                    unset($_GET['commentId']);
                }
            }

            if(isset($_GET['replyTo']) && $_GET['replyTo'] >1){
                $placeholderCmt =  "Reply to ".Comment::getCommentsById($_GET['replyTo'])->username;
                $btnTxt="Reply";
            } else {
                unset($_GET['replyTo']);
            }
        ?>
        <textarea name="comment" id="commentEditor" cols="30" rows="10" placeholder="<?php echo $placeholderCmt ?>"><?php echo $commentTxt?></textarea>
        <input type="hidden" name="commentOn" value="<?php echo $_GET["storyId"]; ?>">
        <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>">
        
        <?php if (isset($_GET['editComment'])){?><input type="hidden" name="editComment" value="<?php echo $_GET["commentId"]; ?>"><?php } ?>
        <?php if (isset($_GET['replyTo'])){?><input type="hidden" name="replyTo" value="<?php echo $_GET['replyTo']; ?>"><?php } ?>
        <input id="cmtBtn" type="submit" value="<?php echo $btnTxt?>">
    </form>
    <form class="cmtForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
        <input type="hidden" name="storyId" value="<?php echo $s->id?>">
        <input class="cmtBtn" type="submit" value="Cancel" />
    </form>


    <!-- comment list -->
    <?php
        foreach ( Comment::getCommentList($_GET['storyId'], 0, 1000) as $comment) {?>
            <div class="comment">
            <p class="id"># <?php echo $comment->id;?></p>
            <?php if($comment->predId!=1){ ?>
                <div class="repliedComment">
                    <p class="username"><?php echo $comment->predUsername;?></p>
                    <img src="<?php echo User::getPhotoFromPath($comment->predUserPhoto); ?>" alt="user photo">
                    <p>: </p>
                    <p class="msg"><?php echo $comment->predComment;?></p>
                </div><br>
            <?php }?>
                <div class="myComment">
                    <p class="msg"><?php echo $comment->comment;?></p>
                    <div class="metaComment">
                        <p>Time: <?php echo $comment->time;?></p>
                        <p>Auther: <?php echo $comment->username;?></p>
                        <img src="<?php echo User::getUserById($comment->userId)->getPhoto(); ?>" alt="user photo">
                    </div>
                </div>
                <!-- registered user features -->
                <?php if(isset($_SESSION['uid'])){ ?>
                    <!-- owner feature -->
                    <?php if($_SESSION['uid'] == $comment->userId){ ?>
                        <div class="commentOwner">
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                <input type="hidden" name="deleteComment" value=1>
                                <input type="hidden" name="storyId" value="<?php echo $s->id ?>">
                                <input type="hidden" name="commentId" value="<?php echo $comment->id;?>">
                                <input type="hidden" name="token" value="<?php echo $_SESSION["token"] ?>">
                                <input type="submit" value="Delete">
                            </form>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
                                <input type="hidden" name="editComment" value=1> 
                                <input type="hidden" name="storyId" value="<?php echo $s->id ?>">
                                <input type="hidden" name="commentId" value="<?php echo $comment->id;?>">

                                <input type="submit" value="Edit">
                            </form>
                        </div>
                    <?php } ?>
                    <div class="registeredUser">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
                            <input type="hidden" name="replyTo" value="<?php echo $comment->id;?>">
                            <input type="hidden" name="storyId" value="<?php echo $s->id ?>">
                            <input type="submit" value="Reply">
                        </form>
                    </div>
                <?php } ?>
            </div>
        <?php }?>


</div>