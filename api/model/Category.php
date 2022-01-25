<?php
require dirname(__FILE__) . '/DB.php';
class Category {
    public $id;
    public $uid;
    public $name;
    public $color;

    private function __construct($id, $uid, $name, $color)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->name = $name;
        $this->color = $color;
    }

    public static function addCate($uid, $name, $color){
        global $conn;
        $stmt = $conn->prepare("insert into category (uid, name, color) values (?,?,?)");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param("iss", $uid, $name, $color);
        if($stmt->execute()){
            $id = $conn->insert_id;
            $stmt->close();
            return $id;
        } else {
            $stmt->close();
            return null;
        }
    }

    public static function getCates($uid){
        global $conn;
        $stmt = $conn->prepare("select id, uid, name, color from category where category.uid=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $arr = array();
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $stmt->bind_result($id, $uid, $name, $color);
        while($stmt->fetch()){
            $c = new static($id, $uid, $name, $color);
            array_push($arr, $c);
        }
        return $arr;
    }

    public static function removeUnused($id){
        global $conn;
        $stmt = $conn->prepare("select count(*) from event where event.cid=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if($count==0){
            return static::delete($id);
        } return false;
    }

    public static function delete($id){
        global $conn;
        $stmt = $conn->prepare("delete from category where id=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if($stmt->execute()){
            $stmt->close();
            return true;
        }
        $stmt->close();
        return false;
    }

    public function toDict(){
        return ["id"=>$this->id, "uid"=>$this->uid, "name"=>$this->name, "color"=>$this->color];
    }
}
?>

