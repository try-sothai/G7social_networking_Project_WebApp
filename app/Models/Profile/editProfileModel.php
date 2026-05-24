<?php
require_once '../../../config/db_config.php';

class editProfileModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fetch user data
    public function getUserData($user_id) {
        $sql = "SELECT Username, Email, Bio, ProfilePic, CoverPic, Locations FROM users WHERE UserID = ?"; 
        $stmt = $this->conn->prepare($sql); // Always use -> for object method calls
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Count Posts, Followers, and Following
    public function getProfileStats($userId) {
        $stats = ['posts' => 0, 'followers' => 0, 'following' => 0];
        
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM posts WHERE UserID = ?");
        $stmt->execute([$userId]);
        $stats['posts'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM friends WHERE ReceiverID = ? AND Status = 'accepted'");
        $stmt->execute([$userId]);
        $stats['followers'] = $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM friends WHERE RequesterID = ? AND Status = 'accepted'");
        $stmt->execute([$userId]);
        $stats['following'] = $stmt->fetchColumn();

        return $stats;
    }

    // Update profile information
    public function updateProfile($user_id, $username, $bio, $profile_pic, $Locations, $cover_pic = null) {
        $sql = "UPDATE users SET Username = ?, Bio = ?, ProfilePic = ?, Locations = ?, CoverPic = ? WHERE UserID = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$username, $bio, $profile_pic, $Locations, $cover_pic, $user_id]);
    }

    // Fetch user interests
    public function getInterests($user_id) {
        $stmt = $this->conn->prepare("
            SELECT i.InterestName
            FROM user_interests ui
            JOIN interests i ON ui.InterestID = i.InterestID
            WHERE ui.UserID = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Update interests (delete old ones, insert new ones)
    public function updateInterests($user_id, $interests) {
        $stmt = $this->conn->prepare("DELETE FROM user_interests WHERE UserID = ?");
        $stmt->execute([$user_id]);

        if (!empty($interests)) {
            foreach ($interests as $interest) {
                $interest = trim($interest);
                if ($interest === '') continue;

                // Find or create interest in master table
                $this->conn->prepare("INSERT IGNORE INTO interests (InterestName) VALUES (?)")
                    ->execute([$interest]);

                $row = $this->conn->prepare("SELECT InterestID FROM interests WHERE InterestName = ? LIMIT 1");
                $row->execute([$interest]);
                $interestId = $row->fetchColumn();

                if ($interestId) {
                    $this->conn->prepare("INSERT IGNORE INTO user_interests (UserID, InterestID) VALUES (?, ?)")
                        ->execute([$user_id, $interestId]);
                }
            }
        }
        return true;
    }
}
?>