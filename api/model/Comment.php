<?php
require dirname(__FILE__) . '/DB.php';
class Comment
{
    # comment data
    public $id;
    public $userId;
    public $storyId;
    public $comment;
    public $time;
    public $seen;
    public $predId;

    # some useful user info
    public $username;
    public $userPhoto;

    # data of the comment replied
    public $predComment;
    public $predUsername;
    public $predUserPhoto;

    private function __construct(
        $id, $userId, $storyId, $comment, $predId, $time, $seen, 
        $username, $userPhoto,
        $predComment, $predUsername, $predUserPhoto)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->storyId = $storyId;
        $this->comment = $comment;
        $this->time = $time;
        $this->seen = $seen;
        $this->predId = $predId;
        $this->username = $username;
        $this->userPhoto = $userPhoto;
        $this->predComment = $predComment;
        $this->predUsername = $predUsername;
        $this->predUserPhoto = $predUserPhoto;
    }

    # get a specific comment
    public static function getCommentsById($id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "select 
            comment.id, comment.userId, comment.storyId, comment.comment, comment.predecessor, comment.time, comment.seen, 
            u.username, u.photo, 
            c.comment as predComment, u2.username, u2.photo from comment
            left join comment c on c.id = comment.predecessor and comment.predecessor!=1
            left join user u on comment.userId = u.id
            left join user u2 on c.userId = u2.id
            where comment.id=?"
        );
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result(
            $cid, $userId, $storyId, $comment, $predId, $time, $seen, 
            $username, $userPhoto,
            $predComment, $predUsername, $predUserPhoto
        );
        if($stmt->fetch()){
            $stmt->close();
            $c = new static(
                $cid, $userId, $storyId, $comment, $predId, $time, $seen, 
                $username, $userPhoto,
                $predComment, $predUsername, $predUserPhoto
            );
            if($c->id==null) return null;
            return $c;
        }
        $stmt->close();
        return null;
    }

    # get a page of comment of a story from db
    public static function getCommentList($sid, $begin, $num)
    {
        global $conn;

        
        $stmt = $conn->prepare(
            "select 
            comment.id, comment.userId, comment.storyId, comment.comment, comment.predecessor, comment.time, comment.seen, 
            u.username, u.photo, 
            c.comment as predComment, u2.username, u2.photo from comment
            left join comment c on c.id = comment.predecessor and comment.predecessor!=1
            left join user u on comment.userId = u.id
            left join user u2 on c.userId = u2.id
            where comment.storyId=? order by comment.id desc limit ?,?"
        );
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('iii', $sid, $begin, $num);
        $stmt->execute();
        $stmt->bind_result(
            $cid, $userId, $storyId, $comment, $predId, $time, $seen, 
            $username, $userPhoto,
            $predComment, $predUsername, $predUserPhoto
        );;
        $arr = array();
        while ($stmt->fetch()) {
            $c = new static(
                $cid, $userId, $storyId, $comment, $predId, $time, $seen, 
                $username, $userPhoto,
                $predComment, $predUsername, $predUserPhoto
            );
            array_push($arr, $c);
        }
        return $arr;
    }

    # submit a comment to the story
    public static function addComment($uid, $sid, $comment, $predId=1){
        global $conn;
        $stmt = $conn->prepare("insert into comment (userId, storyId, comment, predecessor) values (?,?,?,?)");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('iisi', $uid, $sid, $comment, $predId);
        if($stmt->execute()){
            $id = $conn->insert_id;
            $stmt->close();
            return $id;
        } else {
            $stmt->close();
            return null;
        }
        
    }

    # edit
    public static function editComment($id, $ctx){
        global $conn;
        $stmt = $conn->prepare("update comment set comment=? where id=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('si', $ctx, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            return false;
        }
    }

    # del
    public static function delete($id){
        global $conn;
        $stmt = $conn->prepare("delete from comment where id=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        if ($stmt->execute()){
            $stmt->close();
            return true;
        }
        $stmt->close();
        return false;
    }

    # return the number of comments
    public static function countComments($sid){
        global $conn;
        $stmt = $conn->prepare("select count(1) from comment where storyId=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $sid);
        $stmt->execute();
        $stmt->bind_result($count);
        if ($stmt->fetch()) {
            $stmt->close();
            return $count;
        }
        $stmt->close();
        return 0;
    }
}
