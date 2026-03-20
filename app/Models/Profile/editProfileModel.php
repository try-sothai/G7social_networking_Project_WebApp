<?php
// session_start();
require_once '../../../config/db_config.php';

class editProfileModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserData($user_id) {
    // បន្ថែម Email ទៅក្នុង SQL Query ខាងក្រោមនេះ
        $sql = "SELECT Username, Email, Bio, ProfilePic, Phone FROM users WHERE UserID = ?"; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProfileStats($userId) {
        $stats = ['posts' => 0, 'followers' => 0, 'following' => 0];

        // រាប់ចំនួន Posts
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM posts WHERE UserID = :id");
        $stmt->execute(['id' => $userId]);
        $stats['posts'] = $stmt->fetchColumn();

        // រាប់ចំនួន Followers (អ្នកដែលផ្ញើមកយើង ហើយយើងបាន Accept)
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM friends WHERE ReceiverID = :id AND Status = 'accepted'");
        $stmt->execute(['id' => $userId]);
        $stats['followers'] = $stmt->fetchColumn();

        // រាប់ចំនួន Following (អ្នកដែលយើងផ្ញើទៅ រួចគេបាន Accept)
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM friends WHERE RequesterID = :id AND Status = 'accepted'");
        $stmt->execute(['id' => $userId]);
        $stats['following'] = $stmt->fetchColumn();

        return $stats;
    }

    public function updateProfile($user_id, $username, $bio, $profile_pic, $phone) {
        $sql = "UPDATE users SET Username = ?, Bio = ?, ProfilePic = ?, Phone = ? WHERE UserID = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$username, $bio, $profile_pic, $phone, $user_id]);
    }

    public function getInterests($user_id) {
        $sql = "SELECT InterestName FROM user_interests WHERE UserID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateInterests($user_id, $interests) {
        // First, delete old interests
        $sql = "DELETE FROM user_interests WHERE UserID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        // Then, insert new interests
        if (!empty($interests)) {
            $sql = "INSERT INTO user_interests (UserID, InterestName) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            foreach ($interests as $interest) {
                $stmt->execute([$user_id, $interest]);
            }
        }
    }
}
