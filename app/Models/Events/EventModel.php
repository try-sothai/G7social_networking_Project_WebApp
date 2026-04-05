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

    public function getUpcomingEvents($current_user_id = 0) {
        $stmt = $this->db->prepare("
            SELECT e.*, u.Username, u.ProfilePic,
                   (SELECT COUNT(*) FROM event_participants ep WHERE ep.EventID = e.EventID) as interested_count,
                   (SELECT COUNT(*) FROM event_participants ep2 WHERE ep2.EventID = e.EventID AND ep2.UserID = :uid) as is_interested
            FROM events e
            INNER JOIN users u ON e.UserID = u.UserID
            WHERE e.EventDateTime >= NOW()
            ORDER BY e.EventDateTime ASC
        ");
        $stmt->execute([':uid' => $current_user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllEvents($current_user_id = 0) {
        $stmt = $this->db->prepare("
            SELECT e.*, u.Username, u.ProfilePic,
                   (SELECT COUNT(*) FROM event_participants ep WHERE ep.EventID = e.EventID) as interested_count,
                   (SELECT COUNT(*) FROM event_participants ep2 WHERE ep2.EventID = e.EventID AND ep2.UserID = :uid) as is_interested
            FROM events e
            INNER JOIN users u ON e.UserID = u.UserID
            ORDER BY e.EventDateTime DESC
        ");
        $stmt->execute([':uid' => $current_user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByUserId($user_id, $current_user_id = 0) {
        $stmt = $this->db->prepare("
            SELECT e.*, u.Username, u.ProfilePic,
                   (SELECT COUNT(*) FROM event_participants ep WHERE ep.EventID = e.EventID) as interested_count,
                   (SELECT COUNT(*) FROM event_participants ep2 WHERE ep2.EventID = e.EventID AND ep2.UserID = :uid) as is_interested
            FROM events e
            INNER JOIN users u ON e.UserID = u.UserID
            WHERE e.UserID = :user_id
            ORDER BY e.EventDateTime DESC
        ");
        $stmt->execute([':user_id' => $user_id, ':uid' => $current_user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEvent($eventId, $userId, $title, $description, $location, $event_datetime, $image_path = null) {
        if ($image_path !== null) {
            $stmt = $this->db->prepare("
                UPDATE events SET Title = :title, Description = :description, Location = :location, EventDateTime = :event_datetime, ImagePath = :image_path 
                WHERE EventID = :event_id AND UserID = :user_id
            ");
            $stmt->bindValue(':image_path', $image_path);
        } else {
            $stmt = $this->db->prepare("
                UPDATE events SET Title = :title, Description = :description, Location = :location, EventDateTime = :event_datetime 
                WHERE EventID = :event_id AND UserID = :user_id
            ");
        }
        
        $stmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':location', $location);
        $stmt->bindValue(':event_datetime', $event_datetime);
        
        return $stmt->execute();
    }

    public function deleteEvent($eventId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM events WHERE EventID = :event_id AND UserID = :user_id");
        $stmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function notifyAllUsersNewEvent($creatorId) {
        // Insert notification for all users except the creator
        $stmt = $this->db->prepare("
            INSERT INTO notifications (UserID, TriggerID, Type)
            SELECT UserID, :creator_id, 'NewEvent' 
            FROM users 
            WHERE UserID != :creator_id2
        ");
        
        return $stmt->execute([
            ':creator_id' => $creatorId,
            ':creator_id2' => $creatorId
        ]);
    }

    public function toggleEventInterest($eventId, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM event_participants WHERE EventID = :event_id AND UserID = :user_id");
        $stmt->execute([':event_id' => $eventId, ':user_id' => $userId]);
        if ($stmt->fetch()) {
            $del = $this->db->prepare("DELETE FROM event_participants WHERE EventID = :event_id AND UserID = :user_id");
            return $del->execute([':event_id' => $eventId, ':user_id' => $userId]);
        } else {
            $ins = $this->db->prepare("INSERT INTO event_participants (EventID, UserID, Status, CreatedAt) VALUES (:event_id, :user_id, 'Interested', NOW())");
            return $ins->execute([':event_id' => $eventId, ':user_id' => $userId]);
        }
    }

    public function getEventParticipants($eventId) {
        $stmt = $this->db->prepare("
            SELECT u.UserID, u.Username, u.ProfilePic 
            FROM event_participants ep 
            INNER JOIN users u ON ep.UserID = u.UserID 
            WHERE ep.EventID = :event_id
        ");
        $stmt->execute([':event_id' => $eventId]);
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
