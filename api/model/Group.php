<?php
class Group{
    public $id;
    public $uid;
    public $uuid;
    public $name;
    public $creator;

    private function __construct($id, $uid, $uuid, $name, $creator)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->uuid = $uuid;
        $this->name = $name;
        $this->creator = $creator;
    }

    public static function getGroupById($id){
        global $conn;
        $stmt = $conn->prepare("select grp.id, grp.uid, grp.uuid, grp.name, u.username as creator from grp left join user u on grp.uid = u.id where grp.id = ?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($id, $uid, $uuid, $name, $creator);
        if($stmt->fetch()){
            $u = new static($id, $uid, $uuid, $name, $creator);
            $stmt->close();
            if($u->id==null) return null;
            return $u;
        }
        $stmt->close();
        return null;
    }

    public static function getGroupByUUID($uuid){
        global $conn;
        $stmt = $conn->prepare("select grp.id, grp.uid, grp.uuid, grp.name, u.username as creator from grp left join user u on grp.uid = u.id where grp.uuid = ?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $uuid);
        $stmt->execute();
        $stmt->bind_result($id, $uid, $uuid, $name, $creator);
        if($stmt->fetch()){
            $u = new static($id, $uid, $uuid, $name, $creator);
            $stmt->close();
            if($u->id==null) return null;
            return $u;
        }
        $stmt->close();
        return null;
    }

    public static function addGroup($uid, $name){
        global $conn;

        $uuid = null;
        while(true){
            $uuid = bin2hex(random_bytes(16));
            $stmt = $conn->prepare("select count(*) from grp where uuid=?");
            if (!$stmt) {
                printf("Query Prep Failed: %s\n", $conn->error);
                exit;
            }
            $stmt->bind_param("s", $uuid);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            if($count==0){
                break;
            }
        }

        $stmt = $conn->prepare("insert into grp (uid, name, uuid) values (?,?,?)");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param("iss", $uid, $name, $uuid);
        if($stmt->execute()){
            $id = $conn->insert_id;
            $stmt->close();
            static::joinGroup($uuid, $uid);
            return ["id"=>$id, "uuid"=>$uuid];
        } else {
            $stmt->close();
            return null;
        }
    }

    public static function joinGroup($uuid, $uid){
        global $conn;
        // get group id
        $g = static::getGroupByUUID($uuid);
        $gid = null;
        if(!$g){
            return false;
        } else {
            $gid = $g->id;
        }
        // check if already joined
        $stmt = $conn->prepare("select count(*) from groupMember where gid=? and uid=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param("ii",$gid, $uid);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        if($count>0){
            return $g;
        }

        // join
        $stmt = $conn->prepare("insert into groupMember (uid, gid) values (?,?)");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param("ii", $uid, $gid);
        if($stmt->execute()){
            $stmt->close();
            return $g;
        } else {
            $stmt->close();
            return null;
        }
    }

    public function toDict(){
        return ["id"=>$this->id, "uid"=>$this->uid, "uuid"=>$this->uuid, "name"=>$this->name, "creator"=>$this->creator];
    }

    public static function getMyGroups($uid){
        global $conn;
        $stmt = $conn->prepare("select grp.id, grp.name from grp, groupMember where groupMember.uid=? and groupMember.gid=grp.id");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->bind_result($gid, $name);
        $arr = array();
        while($stmt->fetch()){
            array_push($arr, ["gid"=>$gid, "name"=>$name]);
        }
        $stmt->close();
        return $arr;
    }
}
?>