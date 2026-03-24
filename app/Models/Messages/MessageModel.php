<?php
class MessageModel { // Define a class named "MessageModel" to handle database interactions related to messaging functionality in the application
    private $db; // Declare a private property "$db" to hold the database connection object, which will be used for executing queries related to messages
    public function __construct($conn) { 
        $this->db = $conn;
    }

    /* ===== INSERT MESSAGE ===== */
    public function sendMessage($senderId, $receiverId, $messageBody, $type = 'text', $fileUrl = null) {
        $stmt = $this->db->prepare("
            INSERT INTO messages (SenderID, ReceiverID, MessageBody, MessageType, FileUrl, SentAt)
            VALUES (:sender, :receiver, :body, :type, :file, NOW())
        ");
        return $stmt->execute([
            ':sender'   => $senderId,
            ':receiver' => $receiverId,
            ':body'     => $messageBody,
            ':type'     => $type,
            ':file'     => $fileUrl
        ]);
    }

    /* ===== GET CHAT HISTORY ===== */
    /* ===== GET CHAT HISTORY ===== */
    public function getChatHistory($user1, $user2) {
        // យើង Join តារាង messages ជាមួយ users ដើម្បីយក ProfilePic របស់អ្នកផ្ញើ
        $stmt = $this->db->prepare("
            SELECT m.MessageID, m.SenderID, m.ReceiverID, m.MessageBody, m.SentAt, u.ProfilePic
            FROM messages m
            JOIN users u ON m.SenderID = u.UserID
            WHERE 
                (m.SenderID = :u1 AND m.ReceiverID = :u2)
                OR
                (m.SenderID = :u2 AND m.ReceiverID = :u1)
            ORDER BY m.SentAt ASC
        ");

        $stmt->execute([
            ':u1' => $user1,
            ':u2' => $user2
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} // End of the "MessageModel" class definition