<?php
require dirname(__FILE__) . '/DB.php';
class User
{
    private $id;
    private $username;
    private $time;
    private $photo;
    private $admin;

    private function __construct($id, $username, $time, $photo, $admin)
    {
        $this->id = $id;
        $this->username = $username;
        $this->time = $time;
        $this->photo = $photo;
        $this->admin = $admin;
    }

    public function getId(){
        return $this->id;
    }

    public function getUsername(){
        return $this->username;
    }

    public function getPhoto()
    {
        $type = pathinfo($this->photo, PATHINFO_EXTENSION);
        $data = file_get_contents($this->photo);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    public static function getGuestPhoto()
    {
        $type = pathinfo('/media/module3res/userPhoto/defaultPhoto.png', PATHINFO_EXTENSION);
        $data = file_get_contents('/media/module3res/userPhoto/defaultPhoto.png');
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    public function isAdmin(){
        return $this->admin;
    }


    public static function getUserById($id){
        global $conn;
        $stmt = $conn->prepare("select id, username, time, photo, admin from user where id=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($id, $username, $time, $photo, $admin);
        if($stmt->fetch()){
            $u = new static($id, $username, $time, $photo, $admin);
            $stmt->close();
            return $u;
        }
        $stmt->close();
        return null;
    }

    public static function register($username, $password, $admin=0){
        global $conn;
        # if exists
        $stmt = $conn->prepare("select COUNT(*) from user where username=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if($count>0){
            return null;
        }

        # register
        $passhash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("insert into user (username, password, admin) values (?,?,?)");
        $stmt->bind_param('ssi', $username, $passhash, $admin);
        if($stmt->execute()){
            return static::login($username, $password);
        }
        return null;
    }

    public static function login($username, $inputPass){
        global $conn;
        $stmt = $conn->prepare("select id, username, time, photo, admin, password from user where username=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('s',$username);
        $stmt->execute();
        $stmt->bind_result($id, $username, $time, $photo, $admin, $password);
        if($stmt->fetch()){
            $stmt->close();
            if (!password_verify($inputPass, $password)){
                return null;
            }
            return new static($id, $username, $time, $photo, $admin);
        }
        $stmt->close();
        return null;
    }
}
?>