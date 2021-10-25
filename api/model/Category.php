<?php
require dirname(__FILE__) . '/DB.php';
class Category {
    public $id;
    public $uid;
    public $name;
    public $color;

    private function __construct($id, $uid, $name, $color)
    {
        $this->$id = $id;
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
}
?>

