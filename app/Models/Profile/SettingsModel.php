<?php
class SettingsModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Retrieve the current password hash for verification
    public function getPasswordHash($userId) {
        $stmt = $this->conn->prepare("SELECT Password FROM users WHERE UserID = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['Password'] : null;
    }

    // Update the user's password with a new hash
    public function updatePassword($userId, $newPasswordHash) {
        $stmt = $this->conn->prepare("UPDATE users SET Password = :password WHERE UserID = :id");
        return $stmt->execute([
            'password' => $newPasswordHash,
            'id' => $userId
        ]);
    }

    // Delete the user's account
    public function deleteAccount($userId) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE UserID = :id");
        return $stmt->execute(['id' => $userId]);
    }
}
?>