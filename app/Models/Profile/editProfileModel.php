<?php
require_once '../../../config/db_config.php';

class editProfileModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ទាញទិន្នន័យ User
    public function getUserData($user_id) {
        $sql = "SELECT Username, Email, Bio, ProfilePic, Locations FROM users WHERE UserID = ?"; 
        $stmt = $this->conn->prepare($sql); // ប្រើ -> ជានិច្ច
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // រាប់ចំនួន Posts, Followers, Following
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
        $stats['following'] = $stats['following'] = $stmt->fetchColumn();

        return $stats;
    }

    // Update ព័ត៌មាន Profile
    public function updateProfile($user_id, $username, $bio, $profile_pic, $Locations) {
        $sql = "UPDATE users SET Username = ?, Bio = ?, ProfilePic = ?, Locations = ? WHERE UserID = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$username, $bio, $profile_pic, $Locations, $user_id]);
    }

    // ទាញយកចំណាប់អារម្មណ៍
    public function getInterests($user_id) {
        $sql = "SELECT InterestName FROM user_interests WHERE UserID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Update ចំណាប់អារម្មណ៍ (លុបចាស់ បញ្ចូលថ្មី)
    public function updateInterests($user_id, $interests) {
        // លុបអាเก่าចោល
        $stmt = $this->conn->prepare("DELETE FROM user_interests WHERE UserID = ?");
        $stmt->execute([$user_id]);

        // បញ្ចូលអាថ្មី
        if (!empty($interests)) {
            $stmt = $this->conn->prepare("INSERT INTO user_interests (UserID, InterestName) VALUES (?, ?)");
            foreach ($interests as $interest) {
                if(!empty($interest)) {
                    $stmt->execute([$user_id, $interest]);
                }
            }
        }
        return true;
    }
}
?>