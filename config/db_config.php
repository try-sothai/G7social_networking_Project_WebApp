<?php
$host = "localhost";
$dbname = "g7social_networking";
$username = "root";
$password = ""; // សម្រាប់ Wamp ភាគច្រើនគឺទទេបែបនេះ

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // បើចង់តេស្តថាដើរឬអត់ អាច uncomment បន្ទាត់ខាងក្រោម
    // echo "Connected successfully"; 
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
