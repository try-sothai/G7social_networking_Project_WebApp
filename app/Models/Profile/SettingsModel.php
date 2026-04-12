<?php
class SettingsModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ទាញយកលេខសម្ងាត់ចាស់ដើម្បីផ្ទៀងផ្ទាត់
    public function getPasswordHash($userId) {
        $stmt = $this->conn->prepare("SELECT Password FROM users WHERE UserID = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['Password'] : null;
    }

    // ធ្វើបច្ចុប្បន្នភាពលេខសម្ងាត់ថ្មី
    public function updatePassword($userId, $newPasswordHash) {
        $stmt = $this->conn->prepare("UPDATE users SET Password = :password WHERE UserID = :id");
        return $stmt->execute([
            'password' => $newPasswordHash,
            'id' => $userId
        ]);
    }

    // លុបគណនីអ្នកប្រើប្រាស់
    public function deleteAccount($userId) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE UserID = :id");
        return $stmt->execute(['id' => $userId]);
    }
}
?>