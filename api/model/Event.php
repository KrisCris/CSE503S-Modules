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
    public $shareToken;

    public $cid;
    public $cName;
    public $cColor;

    public $gid;
    public $gName;

    private function __construct($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end, $shareToken, $cName, $color, $gName)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->title = $title;
        $this->detail = $detail;
        $this->isFullDay = $isFullDay;
        $this->start = $start;
        $this->end = $end;
        $this->shareToken = $shareToken;
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
        event.id, event.uid, event.cid, event.gid, event.title, event.detail, event.isFullDay, event.start, event.end, event.shareToken,
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
        $stmt->bind_result($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end, $shareToken, $cName, $color, $gName);
        if ($stmt->fetch()) {
            $e = new static($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end, $shareToken, $cName, $color, $gName);
            $stmt->close();
            if ($e->id == null) return null;
            return $e;
        }
        $stmt->close();
        return null;
    }

    // fetch events in a period, say, a month.
    public static function getEventByRange($uid, $beginTS, $endTS)
    {
        global $conn;
        $stmt = $conn->prepare(
            "
            select event.id,
                event.uid,
                event.cid,
                event.gid,
                event.title,
                event.detail,
                event.isFullDay,
                event.start,
                event.end,
                event.shareToken,
                c.name  as cName,
                c.color as color,
                g.name  as gName
            from event
            left join category c on event.cid = c.id
            left join grp g on event.gid = g.id
            left join groupMember gM on g.id = gM.gid and gM.uid
            where ((event.start >= ? and event.start <= ?) or
                (event.end >= ? and event.end <= ?) or 
                (event.start <= ? and event.end >= ?))
            and (event.uid = ? or gM.uid = ?)
            group by event.id, event.start
            order by event.start asc;"
        );
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('iiiiiiii', $beginTS, $endTS, $beginTS, $endTS, $beginTS, $endTS, $uid, $uid);
        $stmt->execute();
        $stmt->bind_result($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end, $shareToken, $cName, $color, $gName);
        $arr = array();
        while ($stmt->fetch()) {
            $e = new static($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end, $shareToken, $cName, $color, $gName);
            array_push($arr, $e);
        }
        return $arr;
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

    public static function editEvent($id, $uid, $cid, $gid, $title, $detail, $isFullDay, $start, $end = null)
    {
        global $conn;
        $stmt = $conn->prepare("update event set cid=?, gid=?, title=?, detail=?, isFullDay=?, start=?, end=? where id=? and uid=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('iissiiiii', $cid, $gid, $title, $detail, $isFullDay, $start, $end, $id, $uid);
        if ($stmt->execute()) {
            if($stmt->affected_rows){
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                return false;
            }
        } else {
            return false;
        }
    }

    public static function removeEvent($id, $uid)
    {
        $tmpE = static::getEventById($id);
        $cid = null;
        if ($tmpE && $tmpE->cid) {
            $cid = $tmpE->cid;
        }

        global $conn;
        $stmt = $conn->prepare("delete from event where id=? and uid=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('ii', $id, $uid);
        if ($stmt->execute()) {
            if($stmt->affected_rows){
                $stmt->close();
                require 'Category.php';
                Category::removeUnused($cid);
                return true;
            } else {
                $stmt->close();
                return false;
            }
        }
        $stmt->close();
        return false;
    }

    public function toDict()
    {
        return [
            "id" => $this->id,
            "uid" => $this->uid,
            "title" => $this->title,
            "detail" => $this->detail,
            "isFullDay" => $this->isFullDay,
            "start" => $this->start,
            "end" => $this->end,
            "cid" => $this->cid,
            "cName" => $this->cName,
            "cColor" => $this->cColor,
            "gid" => $this->gid,
            "gName" => $this->gName
        ];
    }

    public static function shareEvent($id)
    {
        global $conn;

        $e = static::getEventById($id);

        if ($e) {
            if ($e->gid != null) {
                return null;
            } else {
                if ($e->shareToken == null) {
                    $uuid = null;
                    while (true) {
                        $uuid = bin2hex(random_bytes(16));
                        $stmt = $conn->prepare("select count(*) from event where shareToken=?");
                        if (!$stmt) {
                            printf("Query Prep Failed: %s\n", $conn->error);
                            exit;
                        }
                        $stmt->bind_param("s", $uuid);
                        $stmt->execute();
                        $stmt->bind_result($count);
                        $stmt->fetch();
                        $stmt->close();
                        if ($count == 0) {
                            break;
                        }
                    }

                    $stmt = $conn->prepare("update event set shareToken=? where id=?");
                    if (!$stmt) {
                        printf("Query Prep Failed: %s\n", $conn->error);
                        exit;
                    }
                    $stmt->bind_param("si", $uuid, $id);
                    if ($stmt->execute()) {
                        $id = $conn->insert_id;
                        $stmt->close();
                        return ["id" => $id, "shareToken" => $uuid];
                    } else {
                        $stmt->close();
                        return null;
                    }
                } else {
                    return ["id" => $id, "shareToken" => $e->shareToken];
                }
            }
        } else {
            return null;
        }
    }

    public static function addShare($uid, $token)
    {
        global $conn;
        $stmt = $conn->prepare(
            "select                 
                event.cid,
                event.title,
                event.detail,
                event.isFullDay,
                event.start,
                event.end
            from event
            where event.shareToken=?"
        );
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->bind_result($cid, $title, $detail, $isFullDay, $start, $end);
        if($stmt->fetch()){
            $stmt->close();
            
            $ret = static::addEvent($uid, $cid, null, $title, $detail, $isFullDay, $start, $end);
            if($ret){
                return $ret;
            } else {
                return null;
            }
        } else {
            $stmt->close();
            return null;
        }

    }
}
