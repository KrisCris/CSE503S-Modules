<?php
require dirname(__FILE__) . '/DB.php';
class User{
    private $id;
    private $username;
    private $pw;

    private function __construct($id, $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

    public static function getUserById($id){
        global $conn;
        $stmt = $conn->prepare("select id, username from user where id=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($id, $username);
        if($stmt->fetch()){
            $u = new static($id, $username);
            $stmt->close();
            if($u->id==null) return null;
            return $u;
        }
        $stmt->close();
        return null;
    }

    # reg
    public static function register($username, $password){
        if (!preg_match('/^[\w_\.\-]+$/', $username) || $username == "") {
            return null;
        }
        global $conn;
        # if exists
        $stmt = $conn->prepare("select COUNT(*) from user where username=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $username = strtolower($username);
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
        $stmt = $conn->prepare("insert into user (username, password) values (?,?)");
        $stmt->bind_param('ss', $username, $passhash);
        if($stmt->execute()){
            return static::login($username, $password);
        }
        return null;
    }

    # login
    public static function login($username, $inputPass){
        if (!preg_match('/^[\w_\.\-]+$/', $username) || $username == "") {
            return null;
        }
        global $conn;
        $stmt = $conn->prepare("select id, username, password from user where username=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $username = strtolower($username);
        $stmt->bind_param('s',$username);
        $stmt->execute();
        $stmt->bind_result($id, $username, $password);
        if($stmt->fetch()){
            $stmt->close();
            if (!password_verify($inputPass, $password)){
                return null;
            }
            return new static($id, $username);
        }
        $stmt->close();
        return null;
    }
}
?>