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


    public function getUserPosts($userId, $limit = 60) {
        $stmt = $this->conn->prepare("
            SELECT PostID, Content, ImagePath, CreatedAt
            FROM posts
            WHERE UserID = :id
            ORDER BY CreatedAt DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min((int)$limit, 200)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile($userId, $username, $bio, $profilePic, $locations) {
        $stmt = $this->conn->prepare("UPDATE users SET Username = :username, Bio = :bio, ProfilePic = :profilePic, Locations = :locations WHERE UserID = :id");
        return $stmt->execute([
            'username' => $username,
            'bio' => $bio,
            'profilePic' => $profilePic,
            'locations' => $locations,
            'id' => $userId
        ]);
    }
    public function getInterests($userId) {
        $stmt = $this->conn->prepare("SELECT InterestName FROM user_interests WHERE UserID = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
