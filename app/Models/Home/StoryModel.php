<?php
class StoryModel {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getRecentStories($hours = 24) {
        $stmt = $this->db->prepare("
            SELECT
                s.StoryID,
                s.UserID,
                s.Content,
                s.StoryMedia,
                s.CreatedAt,
                s.ExpiresAt,
                u.Username,
                u.ProfilePic
            FROM stories s
            INNER JOIN users u ON u.UserID = s.UserID
            WHERE s.ExpiresAt > NOW() OR s.CreatedAt >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
            ORDER BY s.CreatedAt DESC
        ");
        $stmt->bindValue(':hours', (int)$hours, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createStory($userId, $storyMedia, $content = null) {
        $stmt = $this->db->prepare("
            INSERT INTO stories (UserID, StoryMedia, Content, CreatedAt)
            VALUES (:user_id, :story_media, :content, NOW())
        ");
        return $stmt->execute([
            ':user_id' => $userId,
            ':story_media' => $storyMedia,
            ':content' => $content
        ]);
    }

    public function getStoryById($storyId) {
        $stmt = $this->db->prepare("SELECT StoryID, UserID, Content, StoryMedia, ExpiresAt FROM stories WHERE StoryID = :story_id LIMIT 1");
        $stmt->execute([':story_id' => $storyId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteStoryByIdForUser($storyId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM stories WHERE StoryID = :story_id AND UserID = :user_id");
        $stmt->execute([
            ':story_id' => $storyId,
            ':user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }
}
