<?php
class EventModel {
    private $db;
    private $eventIdColumn = null;

    public function __construct($conn) {
        $this->db = $conn;
    }

    private function getEventIdColumn() {
        if ($this->eventIdColumn !== null) {
            return $this->eventIdColumn;
        }

        $default = 'EventID';
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM events");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                $name = (string)($col['Field'] ?? '');
                if ($name === 'EventID' || $name === 'id' || $name === 'ID') {
                    $this->eventIdColumn = $name;
                    return $this->eventIdColumn;
                }
            }
        } catch (Throwable $e) {
            // fallback below
        }

        $this->eventIdColumn = $default;
        return $this->eventIdColumn;
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

    public function getEventByIdForUser($event_id, $user_id) {
        $idCol = $this->getEventIdColumn();
        $stmt = $this->db->prepare("
            SELECT *
            FROM events
            WHERE {$idCol} = :event_id AND UserID = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            ':event_id' => $event_id,
            ':user_id' => $user_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateEventForUser($event_id, $user_id, $title, $description, $location, $event_datetime, $image_path = null) {
        $idCol = $this->getEventIdColumn();
        $sql = "
            UPDATE events
            SET Title = :title,
                Description = :description,
                Location = :location,
                EventDateTime = :event_datetime
        ";

        $params = [
            ':title' => $title,
            ':description' => $description,
            ':location' => $location,
            ':event_datetime' => $event_datetime,
            ':event_id' => $event_id,
            ':user_id' => $user_id
        ];

        if ($image_path !== null) {
            $sql .= ", ImagePath = :image_path";
            $params[':image_path'] = $image_path;
        }

        $sql .= " WHERE {$idCol} = :event_id AND UserID = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteEventForUser($event_id, $user_id) {
        $idCol = $this->getEventIdColumn();
        $stmt = $this->db->prepare("
            DELETE FROM events
            WHERE {$idCol} = :event_id AND UserID = :user_id
            LIMIT 1
        ");
        return $stmt->execute([
            ':event_id' => $event_id,
            ':user_id' => $user_id
        ]);
    }
}
