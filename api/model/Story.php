<?php
require dirname(__FILE__) . '/DB.php';
class Story
{
    # too lazy to create accessors
    public $id;
    public $userId;
    public $title;
    public $content;
    public $link;
    public $time;
    public $click;

    public $username;
    public $userPhoto;

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

    public static function deleteStory($id)
    {
        global $conn;
        $stmt = $conn->prepare("delete from story where id=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        return $stmt->execute() ? true : false;
    }

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
            return $s;
        }
        $stmt->close();
        return null;
    }

    public static function getStoryList($beginId, $num)
    {
        global $conn;
        $stmt = $conn->prepare("select story.id, story.userId, story.title, story.content, story.link, story.time, story.click, u.username, u.photo, sum(r.value) as rates from story left join user u on u.id = story.userId left join rate r on story.id = r.storyId group by story.id order by story.id desc limit ?,?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('ii', $beginId, $num);
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
