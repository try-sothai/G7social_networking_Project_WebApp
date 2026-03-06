<?php
// models/UserModel.php

class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ទាញព័ត៌មានលម្អិតរបស់ User
    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE UserID = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ទាញស្ថិតិសរុប (Posts, Followers, Following)
    public function getUserStats($id) {
        // រាប់ចំនួន Posts
        $stmt_posts = $this->conn->prepare("SELECT COUNT(*) as total FROM posts WHERE UserID = :id");
        $stmt_posts->execute(['id' => $id]);
        $posts = $stmt_posts->fetch(PDO::FETCH_ASSOC)['total'];

        // រាប់ចំនួន Followers (អ្នកដែល Follow យើង)
        $stmt_followers = $this->conn->prepare("SELECT COUNT(*) as total FROM friends WHERE ReceiverID = :id AND Status = 'accepted'");
        $stmt_followers->execute(['id' => $id]);
        $followers = $stmt_followers->fetch(PDO::FETCH_ASSOC)['total'];

        // រាប់ចំនួន Following (អ្នកដែលយើង Follow គេ)
        $stmt_following = $this->conn->prepare("SELECT COUNT(*) as total FROM friends WHERE RequesterID = :id AND Status = 'accepted'");
        $stmt_following->execute(['id' => $id]);
        $following = $stmt_following->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'posts'     => $posts,
            'followers' => $followers,
            'following' => $following
        ];
    }
}