<?php
class PostModel {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getRecentPosts(int $limit = 25, int $groupId = 0): array {
        $sql = "
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
            INNER JOIN users u ON u.UserID = p.UserID";

        $whereClauses = ["u.AccountStatus = 'Active'"];
        $params = [];

        if ($groupId > 0) {
            $whereClauses[] = "p.GroupID = :group_id";
            $params[':group_id'] = $groupId;
        } else {
            $whereClauses[] = "(p.GroupID IS NULL OR p.GroupID = 0)";
        }

        $sql .= " WHERE " . implode(' AND ', $whereClauses);
        $sql .= " ORDER BY p.CreatedAt DESC LIMIT :limit";
        $params[':limit'] = max(1, min($limit, 100));

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createPost(int $userId, string $content, string $imagePath, string $privacy = 'Public', ?string $location = null, ?string $feeling = null, string $category = 'General', int $groupId = 0): bool {
        // Extract all hashtags from content (supports both Khmer and English)
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $content, $matches);
        $hashtags = implode(',', $matches[1]);
        $groupIdValue = ($groupId > 0) ? $groupId : null;
        
        try {
            // Call the Stored Procedure (includes all new parameters)
            $stmt = $this->db->prepare("
                CALL CreatePostWithHashtags(:user_id, :content, :image_path, :privacy, :location, :feeling, :category, :hashtags, :group_id)
            ");
            return $stmt->execute([
                ':user_id' => $userId,
                ':content' => $content,
                ':image_path' => $imagePath,
                ':privacy' => $privacy,
                ':location' => $location,
                ':feeling' => $feeling,
                ':category' => $category,
                ':hashtags' => $hashtags,
                ':group_id' => $groupIdValue
            ]);
        } catch (PDOException $e) {
            // Fallback when the stored procedure does not yet exist in the database
            $stmt = $this->db->prepare("
                INSERT INTO posts (UserID, Content, ImagePath, Privacy, Location, Feeling, Category, GroupID, CreatedAt)
                VALUES (:user_id, :content, :image_path, :privacy, :location, :feeling, :category, :group_id, NOW())
            ");
            return $stmt->execute([
                ':user_id' => $userId,
                ':content' => $content,
                ':image_path' => $imagePath,
                ':privacy' => $privacy,
                ':location' => $location,
                ':feeling' => $feeling,
                ':category' => $category,
                ':group_id' => $groupIdValue
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
