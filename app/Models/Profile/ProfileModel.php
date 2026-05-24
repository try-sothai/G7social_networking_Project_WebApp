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
        $stats = ['posts' => 0, 'friends' => 0, 'following' => 0];

        // Count Posts
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM posts WHERE UserID = :id");
        $stmt->execute(['id' => $userId]);
        $stats['posts'] = $stmt->fetchColumn();

        // Count Friends (ទំi sides — ទាំង send ទាំង receive)
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) FROM friends
            WHERE (RequesterID = :id OR ReceiverID = :id2) AND Status = 'Accepted'
        ");
        $stmt->execute([':id' => $userId, ':id2' => $userId]);
        $stats['friends'] = $stmt->fetchColumn();

        // Count Following (posts the user follows — optional, keep for future)
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM friends WHERE RequesterID = :id AND Status = 'Accepted'");
        $stmt->execute(['id' => $userId]);
        $stats['following'] = $stmt->fetchColumn();

        return $stats;
    }


    public function getUserPosts($userId, $limit = 60, $currentUserId = null) {
        $stmt = $this->conn->prepare("
            SELECT p.PostID, p.Content, p.ImagePath, p.Privacy, p.Location, p.Feeling, p.CreatedAt,
                   (SELECT COUNT(*) FROM interactions WHERE PostID = p.PostID AND Type IN ('like', 'love', 'haha')) AS likes_count,
                   (SELECT COUNT(*) FROM interactions WHERE PostID = p.PostID AND UserID = :current_user_id AND Type IN ('like', 'love', 'haha')) AS is_liked,
                   (SELECT COUNT(*) FROM post_views WHERE PostID = p.PostID) AS view_count
            FROM posts p
            WHERE p.UserID = :id
            ORDER BY p.CreatedAt DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min((int)$limit, 200)), PDO::PARAM_INT);
        $stmt->bindValue(':current_user_id', (int)($currentUserId ?? 0), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile($userId, $username, $bio, $profilePic, $locations, $coverPic = null) {
        $stmt = $this->conn->prepare("UPDATE users SET Username = :username, Bio = :bio, ProfilePic = :profilePic, CoverPic = :coverPic, Locations = :locations WHERE UserID = :id");
        return $stmt->execute([
            'username' => $username,
            'bio' => $bio,
            'profilePic' => $profilePic,
            'coverPic' => $coverPic,
            'locations' => $locations,
            'id' => $userId
        ]);
    }
    public function getInterests($userId) {
        $stmt = $this->conn->prepare("
            SELECT i.InterestName
            FROM user_interests ui
            JOIN interests i ON ui.InterestID = i.InterestID
            WHERE ui.UserID = :id
        ");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
