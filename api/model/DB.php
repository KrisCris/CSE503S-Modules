<?php
$conn = new mysqli('localhost', 'root', 'root', 'module3');
mysqli_set_charset($conn, 'utf8');
if($conn->connect_errno) {
	printf("Connection Failed: %s\n", $conn->connect_error);
	exit;
}

// class DB{
// 	private static $instance = null;

// 	private $conn;
// 	private function __construct()
// 	{
// 		$this->conn = new mysqli('localhost', 'root', 'root', 'module3');
// 		if($this->conn->connect_errno){
// 			printf("Connection Failed: %s\n", $this->conn->connect_error);
// 			exit;
// 		}
// 	}

// 	public static function getInstance(){
// 		if (self::$instance != null) {
//             return self::$instance;
//         } else {
//             self::$instance = new static();
//             return self::$instance;
//         }
// 	}

// 	public function select($sql){
// 		$res = $this->conn->query($sql);
// 		if ($res->num_rows > 0){

// 		}
// 	}
// }
?>