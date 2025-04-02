<?php
$servername = "localhost";
$username = "root";
$password = "Lakehead123"; 
$database = "userspage"; 


$conn = new mysqli($servername, $username, $password, $database);


if ($conn->connect_error) {
    die("Error to connect: " . $conn->connect_error);
}

echo "";
?>
