<?php
class MessageModel {
    private $db;
    
    public function __construct($conn) {
        $this->db = $conn;
    }

    public function touchPresence($userId) {
        $stmt = $this->db->prepare("
            INSERT INTO user_presence (UserID, IsOnline, LastSeen)
            VALUES (:uid, 1, NOW())
            ON DUPLICATE KEY UPDATE IsOnline = 1, LastSeen = NOW()
        ");
        return $stmt->execute([':uid' => $userId]);
    }

    /* ===== INSERT MESSAGE ===== */
    public function sendMessage($senderId, $receiverId, $messageBody, $type = 'text', $fileUrl = null) {
        $stmt = $this->db->prepare("
            INSERT INTO messages (SenderID, ReceiverID, MessageBody, MessageType, FileUrl, SentAt)
            VALUES (:sender, :receiver, :body, :type, :file, NOW())
        ");
        $stmt->execute([
            ':sender'   => $senderId,
            ':receiver' => $receiverId,
            ':body'     => $messageBody,
            ':type'     => $type,
            ':file'     => $fileUrl
        ]);
        
        return $this->db->lastInsertId();
    }

    /* ===== GET CHAT HISTORY ===== */
    public function getChatHistory($user1, $user2, $afterId = 0) {
        $afterClause = $afterId > 0 ? 'AND m.MessageID > :after_id' : '';
        $stmt = $this->db->prepare("
            SELECT m.MessageID, m.SenderID, m.ReceiverID, m.MessageBody, m.MessageType, m.FileUrl, m.SentAt,
                   u.ProfilePic, u.Username
            FROM messages m
            JOIN users u ON m.SenderID = u.UserID
            WHERE
                ((m.SenderID = :u1 AND m.ReceiverID = :u2)
                OR
                (m.SenderID = :u2 AND m.ReceiverID = :u1))
                $afterClause
            ORDER BY m.SentAt ASC
        ");

        $params = [':u1' => $user1, ':u2' => $user2];
        if ($afterId > 0) $params[':after_id'] = $afterId;
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /* ===== GET FRIENDS WITH LAST MESSAGE ===== */
    public function getFriendsWithLastMessage($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                u.UserID, u.Username, u.ProfilePic, u.Bio,
                CASE 
                    WHEN up.LastSeen IS NOT NULL AND up.LastSeen >= DATE_SUB(NOW(), INTERVAL 2 MINUTE) 
                    THEN 1 ELSE 0 
                END as IsOnline,
                (
                    SELECT MessageBody 
                    FROM messages 
                    WHERE (SenderID = :uid AND ReceiverID = u.UserID) 
                       OR (SenderID = u.UserID AND ReceiverID = :uid)
                    ORDER BY SentAt DESC 
                    LIMIT 1
                ) as last_message,
                (
                    SELECT MessageType 
                    FROM messages 
                    WHERE (SenderID = :uid AND ReceiverID = u.UserID) 
                       OR (SenderID = u.UserID AND ReceiverID = :uid)
                    ORDER BY SentAt DESC 
                    LIMIT 1
                ) as last_message_type,
                (
                    SELECT SentAt 
                    FROM messages 
                    WHERE (SenderID = :uid AND ReceiverID = u.UserID) 
                       OR (SenderID = u.UserID AND ReceiverID = :uid)
                    ORDER BY SentAt DESC 
                    LIMIT 1
                ) as last_message_time,
                (
                    SELECT COUNT(*) 
                    FROM messages 
                    WHERE ReceiverID = :uid AND SenderID = u.UserID AND IsRead = 0
                ) as unread_count
            FROM users u
            LEFT JOIN user_presence up ON up.UserID = u.UserID
            INNER JOIN friends f ON 
                (f.RequesterID = u.UserID AND f.ReceiverID = :uid) OR 
                (f.ReceiverID = u.UserID AND f.RequesterID = :uid)
            WHERE f.Status = 'Accepted' AND u.UserID != :uid AND u.AccountStatus = 'Active'
            ORDER BY last_message_time DESC
        ");
        
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /* ===== SEARCH USERS (Friends Only) ===== */
    public function searchFriends($userId, $searchTerm) {
        $searchTerm = "%{$searchTerm}%";
        $stmt = $this->db->prepare("
            SELECT u.UserID, u.Username, u.ProfilePic, u.Bio,
                   CASE 
                       WHEN up.LastSeen IS NOT NULL AND up.LastSeen >= DATE_SUB(NOW(), INTERVAL 2 MINUTE) 
                       THEN 1 ELSE 0 
                   END as IsOnline,
                   (
                       SELECT COUNT(*) 
                       FROM messages 
                       WHERE ReceiverID = :uid AND SenderID = u.UserID AND IsRead = 0
                   ) as unread_count
            FROM users u
            LEFT JOIN user_presence up ON up.UserID = u.UserID
            INNER JOIN friends f ON 
                (f.RequesterID = u.UserID AND f.ReceiverID = :uid) OR 
                (f.ReceiverID = u.UserID AND f.RequesterID = :uid)
            WHERE f.Status = 'Accepted' AND u.AccountStatus = 'Active'
              AND u.UserID != :uid
              AND (u.Username LIKE :search OR u.Email LIKE :search)
            ORDER BY u.Username ASC
            LIMIT 20
        ");
        
        $stmt->execute([
            ':uid' => $userId,
            ':search' => $searchTerm
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /* ===== SEARCH ALL USERS (For Add Friends) ===== */
    public function searchAllUsers($userId, $searchTerm) {
        $searchTerm = "%{$searchTerm}%";
        $stmt = $this->db->prepare("
            SELECT u.UserID, u.Username, u.ProfilePic, u.Bio,
                   CASE 
                       WHEN f.Status = 'Accepted' THEN 'friend'
                       WHEN f.Status = 'Pending' AND f.RequesterID = :uid THEN 'request_sent'
                       WHEN f.Status = 'Pending' AND f.ReceiverID = :uid THEN 'request_received'
                       ELSE 'none'
                   END as friendship_status
            FROM users u
            LEFT JOIN friends f ON 
                (f.RequesterID = :uid AND f.ReceiverID = u.UserID) OR 
                (f.ReceiverID = :uid AND f.RequesterID = u.UserID)
            WHERE u.UserID != :uid AND u.AccountStatus = 'Active'
              AND (u.Username LIKE :search OR u.Email LIKE :search)
            ORDER BY 
                CASE 
                    WHEN friendship_status = 'friend' THEN 1
                    WHEN friendship_status = 'request_sent' THEN 2
                    WHEN friendship_status = 'request_received' THEN 3
                    ELSE 4
                END,
                u.Username ASC
            LIMIT 30
        ");
        
        $stmt->execute([
            ':uid' => $userId,
            ':search' => $searchTerm
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /* ===== MARK MESSAGES AS READ ===== */
    public function markAsRead($userId, $senderId) {
        $stmt = $this->db->prepare("
            UPDATE messages 
            SET IsRead = 1 
            WHERE ReceiverID = :uid AND SenderID = :sender AND IsRead = 0
        ");
        
        return $stmt->execute([
            ':uid' => $userId,
            ':sender' => $senderId
        ]);
    }
    
    /* ===== GET UNREAD COUNT ===== */
    public function getUnreadCount($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT unread_count as count 
                FROM vw_unread_messages 
                WHERE ReceiverID = :uid
            ");
            
            $stmt->execute([':uid' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM messages 
                WHERE ReceiverID = :uid AND IsRead = 0
            ");
            $stmt->execute([':uid' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        }
    }
    
    /* ===== GET MESSAGE BY ID ===== */
    public function getMessageById($messageId) {
        $stmt = $this->db->prepare("
            SELECT MessageID, SenderID, ReceiverID, MessageBody, MessageType, FileUrl, SentAt, IsRead
            FROM messages 
            WHERE MessageID = :id
        ");
        
        $stmt->execute([':id' => $messageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /* ===== EDIT MESSAGE ===== */
    public function editMessage($messageId, $userId, $newMessageBody) {
        // Verify that the user owns this message
        $stmt = $this->db->prepare("
            SELECT SenderID FROM messages WHERE MessageID = :id
        ");
        $stmt->execute([':id' => $messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$message || $message['SenderID'] != $userId) {
            return false;
        }
        
        // Update the message
        $stmt = $this->db->prepare("
            UPDATE messages 
            SET MessageBody = :body 
            WHERE MessageID = :id
        ");
        
        return $stmt->execute([
            ':body' => $newMessageBody,
            ':id' => $messageId
        ]);
    }
    
    /* ===== DELETE MESSAGE ===== */
    public function deleteMessage($messageId, $userId) {
        // Verify that the user owns this message
        $stmt = $this->db->prepare("
            SELECT SenderID FROM messages WHERE MessageID = :id
        ");
        $stmt->execute([':id' => $messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$message || $message['SenderID'] != $userId) {
            return false;
        }
        
        // Update the message to represent a deleted state for everyone
        $stmt = $this->db->prepare("
            UPDATE messages 
            SET MessageBody = '[Message deleted]',
                MessageType = 'text',
                FileUrl = NULL
            WHERE MessageID = :id
        ");
        
        return $stmt->execute([':id' => $messageId]);
    }
}
?>
