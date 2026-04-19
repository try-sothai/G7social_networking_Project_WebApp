<?php
class ProfileModel {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getUserData($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE UserID = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getProfileStats($user_id) {
        $stats = [];
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM posts WHERE UserID = ?");
        $stmt->execute([$user_id]);
        $stats['posts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM friends WHERE (RequesterID = ? OR ReceiverID = ?) AND Status = 'Accepted'");
        $stmt->execute([$user_id, $user_id]);
        $stats['friends'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return $stats;
    }
    

    // Updated likePost to insert into interactions table
    public function likePost($post_id, $user_id) {
        $stmt = $this->conn->prepare("SELECT InteractionID FROM interactions WHERE PostID = ? AND UserID = ? AND Type IN ('like', 'love', 'haha')");
        $stmt->execute([$post_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $stmt = $this->conn->prepare("DELETE FROM interactions WHERE PostID = ? AND UserID = ? AND Type IN ('like', 'love', 'haha')");
            return $stmt->execute([$post_id, $user_id]) ? 'unliked' : false;
        } else {
            $stmt = $this->conn->prepare("INSERT INTO interactions (PostID, UserID, Type, CreatedAt) VALUES (?, ?, 'like', NOW())");
            return $stmt->execute([$post_id, $user_id]) ? 'liked' : false;
        }
    }

    // Updated addComment to insert into interactions table
    public function addComment($post_id, $user_id, $comment) {
        $stmt = $this->conn->prepare("INSERT INTO interactions (PostID, UserID, Type, CommentText, CreatedAt) VALUES (?, ?, 'comment', ?, NOW())");
        return $stmt->execute([$post_id, $user_id, $comment]);
    }

    // Updated getComments to fetch from interactions table
    public function getComments($post_id) {
        $stmt = $this->conn->prepare("
            SELECT i.CommentText as Comment, i.CreatedAt, u.UserID, u.Username, u.ProfilePic 
            FROM interactions i 
            JOIN users u ON i.UserID = u.UserID 
            WHERE i.PostID = ? AND i.Type = 'comment' 
            ORDER BY i.CreatedAt DESC 
            LIMIT 10
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add these methods to your ProfileModel class

public function getLikeCount($post_id) {
    $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM interactions WHERE PostID = ? AND Type IN ('like', 'love', 'haha')");
    $stmt->execute([$post_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['count'] : 0;
}

public function getCommentCount($post_id) {
    $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM interactions WHERE PostID = ? AND Type = 'comment'");
    $stmt->execute([$post_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['count'] : 0;
}

public function getExplorePosts($category = null, $limit = 20) {
    try {
        if ($category && $category != 'All') {
            $sql = "
                SELECT p.*, u.Username, u.ProfilePic 
                FROM posts p 
                JOIN users u ON p.UserID = u.UserID 
                WHERE u.AccountStatus = 'Active' AND (p.Category = :category OR p.Content LIKE :search OR p.ImagePath LIKE :search)
                ORDER BY p.CreatedAt DESC 
                LIMIT :limit
            ";
            $stmt = $this->conn->prepare($sql);
            $searchTerm = "%$category%";
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':search', $searchTerm);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "
                SELECT p.*, u.Username, u.ProfilePic 
                FROM posts p 
                JOIN users u ON p.UserID = u.UserID 
                WHERE u.AccountStatus = 'Active'
                ORDER BY p.CreatedAt DESC 
                LIMIT :limit
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getExplorePosts: " . $e->getMessage());
        return [];
    }
}



public function checkLikeStatus($post_id, $user_id) {
    try {
        $stmt = $this->conn->prepare("SELECT InteractionID FROM interactions WHERE PostID = ? AND UserID = ? AND Type IN ('like', 'love', 'haha')");
        $stmt->execute([$post_id, $user_id]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Database error in checkLikeStatus: " . $e->getMessage());
        return false;
    }
}

    // Call the Stored Procedure to fetch the feed created in the Database
    public function getUserFeed($user_id, $limit = 10) {
        $stmt = $this->conn->prepare("CALL GetUserFeed(:user_id, :limit)");
        $stmt->execute([':user_id' => $user_id, ':limit' => $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPost($user_id, $content, $image_path = null, $category = 'General') {
        $stmt = $this->conn->prepare("INSERT INTO posts (UserID, Content, ImagePath, Category, CreatedAt) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$user_id, $content, $image_path, $category]);
    }

  public function getNotifications($user_id) {
    $stmt = $this->conn->prepare("
        SELECT n.NotificationID, n.UserID, n.TriggerID, n.Type, n.CreatedAt, n.IsRead, u.Username, u.ProfilePic 
        FROM notifications n 
        JOIN users u ON n.TriggerID = u.UserID 
        WHERE n.UserID = ? 
        ORDER BY n.CreatedAt DESC 
        LIMIT 20
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    public function getUnreadNotificationCount($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE UserID = ? AND IsRead = 0");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['count'] : 0;
    }
    public function getUserInterests($user_id) {
    $stmt = $this->conn->prepare("SELECT * FROM user_interests WHERE UserID = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>
