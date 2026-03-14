<?php
// session_start();
require_once '../../../config/db_config.php';

class editProfileModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserData($user_id) {
        $sql = "SELECT Username, Bio, ProfilePic FROM users WHERE UserID = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProfileStats($user_id) {
        // ទាញយកចំនួន Post
        $sql_posts = "SELECT COUNT(*) FROM posts WHERE UserID = ?";
        $stmt_posts = $this->conn->prepare($sql_posts);
        $stmt_posts->execute([$user_id]);
        $posts = $stmt_posts->fetchColumn();

        // ទាញយកចំនួន Followers
        $sql_followers = "SELECT COUNT(*) FROM followers WHERE FollowedID = ?";
        $stmt_followers = $this->conn->prepare($sql_followers);
        $stmt_followers->execute([$user_id]);
        $followers = $stmt_followers->fetchColumn();

        // ទាញយកចំនួន Following
        $sql_following = "SELECT COUNT(*) FROM followers WHERE FollowerID = ?";
        $stmt_following = $this->conn->prepare($sql_following);
        $stmt_following->execute([$user_id]);
        $following = $stmt_following->fetchColumn();

        return [
            'posts' => $posts,
            'followers' => $followers,
            'following' => $following
        ];
    }

        public function updateProfile($user_id, $username, $bio, $profile_pic) {
            $sql = "UPDATE users SET Username = ?, Bio = ?, ProfilePic = ? WHERE UserID = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$username, $bio, $profile_pic, $user_id]);
        }
}