<?php
$conn = new mysqli('localhost', 'root', 'root', 'module5');
mysqli_set_charset($conn, 'utf8mb4');
if($conn->connect_errno) {
	printf("Connection Failed: %s\n", $conn->connect_error);
	exit;
}
?>