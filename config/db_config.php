<?php
$host = "localhost";
$dbname = "g7social_networking";
$username = "root";
$password = "";

define('BASE_URL', '/g7social_networking/G7social_networking/');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
