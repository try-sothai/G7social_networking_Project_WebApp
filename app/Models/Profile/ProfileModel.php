<?php
class ProfileModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserData($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE UserID = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProfileStats($userId) {
        $stats = ['posts' => 0, 'followers' => 0, 'following' => 0];
        
        // Count Posts
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM posts WHERE UserID = :id");
        $stmt->execute(['id' => $userId]);
        $stats['posts'] = $stmt->fetchColumn();

        // Count Followers
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM friends WHERE ReceiverID = :id AND Status = 'accepted'");
        $stmt->execute(['id' => $userId]);
        $stats['followers'] = $stmt->fetchColumn();

        // Count Following
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM friends WHERE RequesterID = :id AND Status = 'accepted'");
        $stmt->execute(['id' => $userId]);
        $stats['following'] = $stmt->fetchColumn();

        return $stats;
    }
        public function updateProfile($userId, $username, $bio) {
        $stmt = $this->conn->prepare("UPDATE users SET Username = :username, Bio = :bio WHERE UserID = :id");
        return $stmt->execute([
            'username' => $username,
            'bio' => $bio,
            'id' => $userId
        ]);
    }
    
}
?>