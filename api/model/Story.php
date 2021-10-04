<?php
require dirname(__FILE__) . '/DB.php';
class Story
{
    # made them all public cuz i am too lazy to create getters/setters
    public $id;
    public $userId;
    public $title;
    public $content;
    public $link;
    public $time;
    public $click;

    # story writer's info
    public $username;
    public $userPhoto;

    # like + dislikes
    public $rate;

    private function __construct($id, $userId, $title, $content, $link, $time, $click, $username, $userPhoto, $rate)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->content = $content;
        $this->link = $link;
        $this->time = $time;
        $this->click = $click;
        $this->username = $username;
        $this->userPhoto = $userPhoto;
        if ($rate == null) $rate = 0;
        $this->rate = $rate;
    }

    # increase number of views each time user visite the story page
    public function viewed(){
        global $conn;
        $this->click++;
        $stmt = $conn->prepare("update story set click=click+1 where id=$this->id");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->execute();
        $stmt->close();
    }

    # add a new story / update a story
    public static function addStory($userId, $title, $content, $link, $storyId = -1)
    {
        global $conn;
        $stmt = $conn->prepare(
            $storyId > 0 ?
            "update story set title=?, content=?, link=? where id=?" : 
            "insert into story (userId, title, content, link) values (?,?,?,?)"
        );
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $storyId > 0 ? 
        $stmt->bind_param('sssi', $title, $content, $link, $storyId) : 
        $stmt->bind_param('isss', $userId, $title, $content, $link);
        if ($stmt->execute()) {
            $id = $storyId > 0 ? $storyId : $conn->insert_id;
            $stmt->close();
            return $id;
        } else {
            $stmt->close();
            return null;
        }
    }

    # del
    public static function deleteStory($id)
    {
        global $conn;
        $stmt = $conn->prepare("delete from story where id=?");
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

    # like & dislike
    public static function rateStory($sid, $uid, $val){
        global $conn;
        # workaround for fixing view num
        $stmt = $conn->prepare("update story set click=click-1 where id=$sid");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->execute();
        $stmt->close();

        # if user already rated the story, just modify the value
        $stmt = $conn->prepare("select rate.id, rate.value from rate where rate.userId=? and rate.storyId=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('ii', $uid, $sid);
        $stmt->execute();
        $stmt->bind_result($rid, $rval);
        if($stmt->fetch()){
            $stmt->close();
            # if same val, meaning user want to cancel the rate, otherwise change the rate.
            if ($val==$rval) {
                $stmt = $conn->prepare("delete from rate where id=?");
                if (!$stmt) {
                    printf("Query Prep Failed: %s\n", $conn->error);
                    exit;
                }
                $stmt->bind_param('i', $rid);
                if ($stmt->execute()){
                    $stmt->close();
                    return true;
                }
                $stmt->close();
                return false;
            } else {
                $stmt = $conn->prepare("update rate set rate.value=? where rate.id=?");
                if (!$stmt) {
                    printf("Query Prep Failed: %s\n", $conn->error);
                    exit;
                }
                $stmt->bind_param('ii', $val, $rid);
                if ($stmt->execute()){
                    $stmt->close();
                    return true;
                }
                $stmt->close();
                return false;
            }
        }
        # add a new rate
        $stmt = $conn->prepare("insert into rate (userId, storyId, value) values (?,?,?)");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('iii', $uid, $sid, $val);
        if ($stmt->execute()){
            $stmt->close();
            return true;
        }
        $stmt->close();
        return false;
    }

    # help html render which rate button we clicked
    public static function getMyRate($uid, $sid){
        global $conn;
        $stmt = $conn->prepare("select rate.value from rate where rate.userId=? and rate.storyId=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('ii', $uid, $sid);
        $stmt->execute();
        $stmt->bind_result($val);
        if($stmt->fetch()){
            $stmt->close();
            return $val;
        } else {
            $stmt->close();
            return 0;
        }
    }

    # get a story in db via its unique id
    public static function getStoryById($id)
    {
        global $conn;
        $stmt = $conn->prepare("select story.id, story.userId, story.title, story.content, story.link, story.time, story.click, user.username, user.photo, sum(rate.value) as rate from story, user, rate where story.id=? and story.userId=user.id and rate.storyId=story.id");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($id, $userId, $title, $content, $link, $time, $click, $username, $userPhoto, $rate);
        if ($stmt->fetch()) {
            $s = new static($id, $userId, $title, $content, $link, $time, $click, $username, $userPhoto, $rate);
            $stmt->close();
            if($s->id==null) return null;
            return $s;
        }
        $stmt->close();
        return null;
    }

    # a list of story..
    public static function getStoryList($begin, $num)
    {
        global $conn;
        $stmt = $conn->prepare("select story.id, story.userId, story.title, story.content, story.link, story.time, story.click, u.username, u.photo, sum(r.value) as rates from story left join user u on u.id = story.userId left join rate r on story.id = r.storyId group by story.id order by story.id desc limit ?,?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('ii', $begin, $num);
        $stmt->execute();
        $stmt->bind_result($id, $userId, $title, $content, $link, $time, $click, $username, $userPhoto, $rate);
        $arr = array();
        while ($stmt->fetch()) {
            $s = new static($id, $userId, $title, $content, $link, $time, $click, $username, $userPhoto, $rate);
            array_push($arr, $s);
        }
        $stmt->close();
        return $arr;
    }

    # number of stories, for split all stories into pages
    public static function countStories()
    {
        global $conn;
        $stmt = $conn->prepare("select count(1) from story");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
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
