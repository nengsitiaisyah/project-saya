<?php
$host = 'localhost';
$user = 'root';  // Sesuaikan dengan pengaturan database Anda
$password = '';  // Sesuaikan dengan pengaturan database Anda
$dbname = 'praujikom';

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
