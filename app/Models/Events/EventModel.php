<?php
class EventModel {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function createEvent($user_id, $title, $description, $location, $event_datetime, $image_path) {
        $stmt = $this->db->prepare("
            INSERT INTO events (UserID, Title, Description, Location, EventDateTime, ImagePath, CreatedAt)
            VALUES (:user_id, :title, :description, :location, :event_datetime, :image_path, NOW())
        ");
        
        return $stmt->execute([
            ':user_id' => $user_id,
            ':title' => $title,
            ':description' => $description,
            ':location' => $location,
            ':event_datetime' => $event_datetime,
            ':image_path' => $image_path
        ]);
    }

    public function getUpcomingEvents() {
        $stmt = $this->db->prepare("
            SELECT e.*, u.Username, u.ProfilePic
            FROM events e
            INNER JOIN users u ON e.UserID = u.UserID
            WHERE e.EventDateTime >= NOW()
            ORDER BY e.EventDateTime ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllEvents() {
        $stmt = $this->db->prepare("
            SELECT e.*, u.Username, u.ProfilePic
            FROM events e
            INNER JOIN users u ON e.UserID = u.UserID
            ORDER BY e.EventDateTime DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByUserId($user_id) {
        $stmt = $this->db->prepare("
            SELECT e.*, u.Username, u.ProfilePic
            FROM events e
            INNER JOIN users u ON e.UserID = u.UserID
            WHERE e.UserID = :user_id
            ORDER BY e.EventDateTime DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
