<?php
require dirname(__FILE__) . '/DB.php';
class User{
    public $id;
    public $username;
    public $pw;
    public $token;

    private function __construct($id, $username, $token=null)
    {
        $this->id = $id;
        $this->username = $username;
        $this->token = $token;
    }

    public static function getUserById($id){
        global $conn;
        $stmt = $conn->prepare("select id, username, token from user where id=?");
        if(!$stmt){
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($id, $username, $token);
        if($stmt->fetch()){
            $u = new static($id, $username, $token);
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
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
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
            $u = new static($id, $username);
            $u->updateToken();
            return $u;
        }
        $stmt->close();
        return null;
    }

    private function updateToken(){
        global $conn;
        $token = null;
        while(true){
            $token = bin2hex(random_bytes(32));
            $stmt = $conn->prepare("select count(*) from user where token=?");
            if (!$stmt) {
                printf("Query Prep Failed: %s\n", $conn->error);
                exit;
            }
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            if($count==0){
                break;
            }
        }

        $stmt = $conn->prepare("update user set token=? where id=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        
        $stmt->bind_param('si', $token, $this->id);
        if ($stmt->execute()) {
            $stmt->close();
            $this->token = $token;
            return true;
        } else {
            return false;
        }
    }

    public static function logout($uid){
        $u = static::getUserById($uid);
        if($u){
            $u->updateToken();
        }
    }

    public static function isLogin($uid, $token){
        global $conn;
        $stmt = $conn->prepare("select username from user where id=? and token=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $conn->error);
            exit;
        }
        $stmt->bind_param('is', $uid, $token);
        $stmt->execute();
        $stmt->bind_result($username);
        if($stmt->fetch()){
            $stmt->close();
            $u = new static($uid, $username, $token);
            return $u;
        }
        $stmt->close();
        return null;
    }
}
?>