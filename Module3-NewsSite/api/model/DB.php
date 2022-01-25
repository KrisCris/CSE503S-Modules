<?php
$conn = new mysqli('localhost', 'root', 'root', 'module3');
mysqli_set_charset($conn, 'utf8');
if($conn->connect_errno) {
	printf("Connection Failed: %s\n", $conn->connect_error);
	exit;
}
?>