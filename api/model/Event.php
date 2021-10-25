<?php
require dirname(__FILE__) . '/DB.php';
class Event
{
    public $id;
    public $uid;
    public $title;
    public $detail;
    public $isFullDay;
    public $start;
    public $end;

    public $cid;
    public $cName;
    public $cColor;

    public $gid;
    public $gName;

    private function __construct($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end, $cName, $color, $gName)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->title = $title;
        $this->detail = $detail;
        $this->isFullDay = $isFullDay;
        $this->start = $start;
        $this->end = $end;
        $this->cid = $cid;
        $this->cName = $cName;
        $this->cColor = $color;
        $this->gid = $gid;
        $this->gName = $gName;
    }

    // fetch detail of an event
    public static function getEventById($id)
    {
        global $conn;
        $stmt = $conn->prepare("select
        event.id, event.uid, event.cid, event.gid, event.title, event.detail, event.isFullDay, event.start, event.end,
        c.name as cName, c.color as color,
        g.name as gName
        from event
        left join category c on event.cid = c.id
        left join grp g on event.gid = g.id
        where event.id=?;");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end, $cName, $color, $gName);
        if ($stmt->fetch()) {
            $e = new static($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end, $cName, $color, $gName);
            $stmt->close();
            if ($e->id == null) return null;
            return $e;
        }
        $stmt->close();
        return null;
    }

    // fetch events in a period, say, a month.
    public static function getEventByRange()
    {
        global $conn;
        $stmt = $conn->prepare("select id, title, detail, ");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
    }

    public static function addEvent($uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end = null)
    {
        global $conn;
        $stmt = $conn->prepare("insert into event (uid, cid, gid, title, detail, isFullDay, start, end) values (?,?,?,?,?,?,?,?)");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('iiissiii', $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end);
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            $stmt->close();
            return $id;
        }
        $stmt->close();
        return null;
    }

    public static function editEvent($id, $cid, $gid, $title, $detail, $isFullDay, $start, $end = null)
    {
        global $conn;
        $stmt = $conn->prepare("update event set cid=?, gid=?, title=?, detail=?, isFullDay=?, start=?, end=? where id=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('iissiiii', $cid, $gid, $title, $detail, $isFullDay, $start, $end, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            return false;
        }
    }

    public static function removeEvent($id)
    {
        global $conn;
        $stmt = $conn->prepare("delete from event where id=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();
            require 'Category.php';

            return true;
        }
        $stmt->close();
        return false;
    }

    public function toDict(){
        return [
            "id"=>$this->id,
            "uid"=>$this->uid,
            "title"=>$this->title,
            "detail"=>$this->detail,
            "isFullDay"=>$this->isFullDay,
            "start"=>$this->start,
            "end"=>$this->end,
            "cid"=>$this->cid,
            "cName"=>$this->cName,
            "cColor"=>$this->cColor,
            "gid"=>$this->gid,
            "gName"=>$this->gName
        ];
    }
}
