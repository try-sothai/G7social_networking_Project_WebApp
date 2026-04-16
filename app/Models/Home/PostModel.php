<?php
class PostModel {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getRecentPosts(int $limit = 25, int $groupId = 0): array {
        $stmt = $this->db->prepare("
            SELECT
                p.PostID,
                p.UserID,
                p.Content,
                p.ImagePath,
                p.Privacy,
                p.Location,
                p.Feeling,
                p.CreatedAt,
                u.Username,
                u.ProfilePic
            FROM posts p
            INNER JOIN users u ON u.UserID = p.UserID
            WHERE u.AccountStatus = 'Active'
            ORDER BY p.CreatedAt DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', max(1, min($limit, 100)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createPost(int $userId, string $content, string $imagePath, string $privacy = 'Public', ?string $location = null, ?string $feeling = null, string $category = 'General'): bool {
        // ទាញយកពាក្យ Hashtags ទាំងអស់ពី Content (គាំទ្រទាំងភាសាខ្មែរ និងអង់គ្លេស)
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $content, $matches);
        $hashtags = implode(',', $matches[1]);
        
        try {
            // ហៅ Stored Procedure មកប្រើ (បញ្ចូលទាំង parameters ថ្មីៗ)
            $stmt = $this->db->prepare("
                CALL CreatePostWithHashtags(:user_id, :content, :image_path, :privacy, :location, :feeling, :category, :hashtags)
            ");
            return $stmt->execute([
                ':user_id' => $userId,
                ':content' => $content,
                ':image_path' => $imagePath,
                ':privacy' => $privacy,
                ':location' => $location,
                ':feeling' => $feeling,
                ':category' => $category,
                ':hashtags' => $hashtags
            ]);
        } catch (PDOException $e) {
            // ប្រព័ន្ធ Backup ពេល Database មិនទាន់មាន Procedure
            $stmt = $this->db->prepare("
                INSERT INTO posts (UserID, Content, ImagePath, Privacy, Location, Feeling, Category, CreatedAt)
                VALUES (:user_id, :content, :image_path, :privacy, :location, :feeling, :category, NOW())
            ");
            return $stmt->execute([
                ':user_id' => $userId,
                ':content' => $content,
                ':image_path' => $imagePath,
                ':privacy' => $privacy,
                ':location' => $location,
                ':feeling' => $feeling,
                ':category' => $category
            ]);
        }
    }

    public function getPostByIdForUser(int $postId, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT PostID, UserID, Content, ImagePath
            FROM posts
            WHERE PostID = :post_id AND UserID = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updatePostForUser(int $postId, int $userId, string $content, string $imagePath): bool {
        $stmt = $this->db->prepare("
            UPDATE posts
            SET Content = :content, ImagePath = :image_path
            WHERE PostID = :post_id AND UserID = :user_id
        ");
        $stmt->execute([
            ':content' => $content,
            ':image_path' => $imagePath,
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);
        return $stmt->rowCount() >= 0;
    }

    public function deletePostForUser(int $postId, int $userId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM posts
            WHERE PostID = :post_id AND UserID = :user_id
        ");
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }
}
