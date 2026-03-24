<?php
$servername = "localhost";  // or your hosting server
$username = "root";         // your DB username
$password = "";             // your DB password
$dbname = "garbage_tracker"; // your existing DB name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
