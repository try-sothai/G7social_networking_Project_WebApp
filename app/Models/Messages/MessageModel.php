<?php
class MessageModel { // Define a class named "MessageModel" to handle database interactions related to messaging functionality in the application
    private $db; // Declare a private property "$db" to hold the database connection object, which will be used for executing queries related to messages
    public function __construct($conn) { // Define the constructor method for the "MessageModel" class, which takes a database connection object as a parameter and assigns it to the "$db" property for use in other methods of the class
        $this->db = $conn; // Assign the provided database connection object to the "$db" property
    } // End of the constructor method

    /* ===== INSERT MESSAGE ===== */
    public function sendMessage($senderId, $receiverId, $messageBody) {

    $stmt = $this->db->prepare("
        INSERT INTO messages 
        (SenderID, ReceiverID, MessageBody, SentAt)
        VALUES (:sender, :receiver, :body, NOW())
    ");

    return $stmt->execute([
        ':sender'   => $senderId,
        ':receiver' => $receiverId,
        ':body'     => $messageBody
    ]);
}

    /* ===== GET CHAT HISTORY ===== */
    public function getChatHistory($user1, $user2) {

    $stmt = $this->db->prepare("
        SELECT MessageID, SenderID, ReceiverID, MessageBody, SentAt
        FROM messages
        WHERE 
            (SenderID = :u1 AND ReceiverID = :u2)
            OR
            (SenderID = :u2 AND ReceiverID = :u1)
        ORDER BY SentAt ASC
    ");

    $stmt->execute([
        ':u1' => $user1,
        ':u2' => $user2
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}// End of the "getChatHistory" method
} // End of the "MessageModel" class definition