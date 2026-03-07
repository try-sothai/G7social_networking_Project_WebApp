<?php
$host = "localhost"; // ឬ "127.0.0.1"
$dbname = "g7social_networking";
$username = "g7social_networking"; // ប្រើ User ដែលអ្នកបានបង្កើតថ្មី
$password = "Sothai@123";          // ប្រើ Password ដែលអ្នកបានកំណត់

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully!"; // បង្ហាញសារនេះដើម្បីបញ្ជាក់ថាដើរ
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>