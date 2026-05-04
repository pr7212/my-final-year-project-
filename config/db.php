<?php
$servername = 'sql100.byetcluster.com';
$username = 'if0_41685326';
$password = 'VKMqBw5idpu37L';
$dbname = 'if0_41685326_gmts';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
