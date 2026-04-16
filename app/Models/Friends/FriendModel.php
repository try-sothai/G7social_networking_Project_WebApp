<?php
// app/Models/Friends/FriendModel.php

class FriendModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Get all accepted friends of a user
     */
    public function getFriends($user_id) {
        $sql = "SELECT u.UserID, u.Username, u.Email, u.ProfilePic, u.Bio,
                       f.CreatedAt as friend_since
                FROM users u
                INNER JOIN friends f ON
                    (f.RequesterID = u.UserID AND f.ReceiverID = :user_id1) OR 
                    (f.ReceiverID = u.UserID AND f.RequesterID = :user_id2)
                WHERE f.Status = 'Accepted' AND u.UserID != :user_id3 AND u.AccountStatus = 'Active'
                ORDER BY u.Username ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id1' => $user_id, ':user_id2' => $user_id, ':user_id3' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get pending friend requests received
     */
    public function getReceivedRequests($user_id) {
        $sql = "SELECT u.UserID, u.Username, u.Email, u.ProfilePic, u.Bio,
                       f.CreatedAt as request_sent_at
                FROM users u
                INNER JOIN friends f ON f.RequesterID = u.UserID
                WHERE f.ReceiverID = :user_id AND f.Status = 'Pending' AND u.AccountStatus = 'Active'
                ORDER BY f.CreatedAt DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get pending friend requests sent
     */
    public function getSentRequests($user_id) {
        $sql = "SELECT u.UserID, u.Username, u.Email, u.ProfilePic, u.Bio,
                       f.CreatedAt as request_sent_at
                FROM users u
                INNER JOIN friends f ON f.ReceiverID = u.UserID
                WHERE f.RequesterID = :user_id AND f.Status = 'Pending' AND u.AccountStatus = 'Active'
                ORDER BY f.CreatedAt DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get users not friends and not pending (for suggestions)
     */
    public function getSuggestedFriends($user_id, $limit = 10) {
        // ORDER BY RAND() is very slow on large tables. This is a more performant way.
        // 1. Get the max UserID to create a random starting point.
        $maxIdStmt = $this->conn->query("SELECT MAX(UserID) FROM users");
        $maxId = ($maxIdStmt) ? $maxIdStmt->fetchColumn() : 0;
        if (!$maxId) return []; // No users in DB
        $randOffset = rand(0, (int)$maxId);

        $sql = "SELECT u.UserID, u.Username, u.Email, u.ProfilePic, u.Bio, u.Locations
                FROM users u
                WHERE u.AccountStatus = 'Active' AND u.UserID >= :rand_offset AND u.UserID != :user_id
                AND NOT EXISTS (
                    SELECT 1 FROM friends f 
                    WHERE ((f.RequesterID = :user_id1 AND f.ReceiverID = u.UserID) 
                        OR (f.ReceiverID = :user_id2 AND f.RequesterID = u.UserID))
                )
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':rand_offset', $randOffset, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id1', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id2', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Search users by name, email, or location
     */
    public function searchUsers($user_id, $search_term) {
        $search_param = "%{$search_term}%";
        $sql = "SELECT u.UserID, u.Username, u.Email, u.ProfilePic, u.Bio, u.Locations,
                       CASE 
                           WHEN f.Status IS NULL THEN 'None'
                           ELSE f.Status
                       END as friendship_status
                FROM users u
                LEFT JOIN friends f ON 
                    (f.RequesterID = :user_id1 AND f.ReceiverID = u.UserID) OR 
                    (f.ReceiverID = :user_id2 AND f.RequesterID = u.UserID)
                WHERE u.UserID != :user_id3
                  AND u.AccountStatus = 'Active' AND (u.Username LIKE :search1 OR u.Email LIKE :search2 OR u.Locations LIKE :search3)
                ORDER BY u.Username ASC
                LIMIT 20";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id1' => $user_id,
            ':user_id2' => $user_id,
            ':user_id3' => $user_id,
            ':search1' => $search_param,
            ':search2' => $search_param,
            ':search3' => $search_param
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Send friend request
     */
    public function sendFriendRequest($user_id, $friend_id) {
        // Check if already friends or request exists
        $check_sql = "SELECT * FROM friends 
                      WHERE (RequesterID = :user_id1 AND ReceiverID = :friend_id1) 
                         OR (RequesterID = :friend_id2 AND ReceiverID = :user_id2)";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->execute([
            ':user_id1' => $user_id, 
            ':friend_id1' => $friend_id, 
            ':friend_id2' => $friend_id, 
            ':user_id2' => $user_id
        ]);
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            if ($existing['Status'] == 'Accepted') {
                return ['success' => false, 'message' => 'Already friends'];
            } elseif ($existing['Status'] == 'Pending') {
                if ($existing['RequesterID'] == $user_id) {
                    return ['success' => false, 'message' => 'Request already sent'];
                } else {
                    // Auto-accept if request was sent to us
                    return $this->acceptFriendRequest($user_id, $friend_id);
                }
            }
        }
        
        $sql = "INSERT INTO friends (RequesterID, ReceiverID, Status) VALUES (:user_id, :friend_id, 'Pending')";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([':user_id' => $user_id, ':friend_id' => $friend_id])) {
            $this->createNotification($friend_id, $user_id, 'FriendRequest');
            return ['success' => true, 'message' => 'Friend request sent'];
        }
        
        return ['success' => false, 'message' => 'Failed to send request'];
    }
    
    /**
     * Accept friend request
     */
    public function acceptFriendRequest($user_id, $friend_id) {
        try {
            $sql = "CALL AcceptFriendRequest(:friend_id, :user_id)";
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt->execute([':friend_id' => $friend_id, ':user_id' => $user_id])) {
                return ['success' => true, 'message' => 'Friend request accepted'];
            }
        } catch (PDOException $e) {
            // ប្រព័ន្ធ Backup ពេលរក Procedure មិនឃើញ
            $sql = "UPDATE friends SET Status = 'Accepted' 
                    WHERE RequesterID = :friend_id AND ReceiverID = :user_id AND Status = 'Pending'";
            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute([':friend_id' => $friend_id, ':user_id' => $user_id])) {
                return ['success' => true, 'message' => 'Friend request accepted'];
            }
        }
        
        return ['success' => false, 'message' => 'Failed to accept request or request not found'];
    }
    
    /**
     * Reject/Cancel friend request
     */
    public function rejectFriendRequest($user_id, $friend_id) {
        $sql = "DELETE FROM friends 
                WHERE (RequesterID = :user_id1 AND ReceiverID = :friend_id1 AND Status = 'Pending')
                   OR (RequesterID = :friend_id2 AND ReceiverID = :user_id2 AND Status = 'Pending')";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([
            ':user_id1' => $user_id, 
            ':friend_id1' => $friend_id, 
            ':friend_id2' => $friend_id, 
            ':user_id2' => $user_id
        ])) {
            return ['success' => true, 'message' => 'Request rejected'];
        }
        
        return ['success' => false, 'message' => 'Failed to reject request'];
    }
    
    /**
     * Remove friend
     */
    public function removeFriend($user_id, $friend_id) {
        $sql = "DELETE FROM friends 
                WHERE ((RequesterID = :user_id1 AND ReceiverID = :friend_id1) 
                    OR (RequesterID = :friend_id2 AND ReceiverID = :user_id2))
                AND Status = 'Accepted'";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt->execute([
            ':user_id1' => $user_id, 
            ':friend_id1' => $friend_id, 
            ':friend_id2' => $friend_id, 
            ':user_id2' => $user_id
        ])) {
            return ['success' => true, 'message' => 'Friend removed'];
        }
        
        return ['success' => false, 'message' => 'Failed to remove friend'];
    }
    
    /**
     * Get friendship status
     */
    public function getFriendshipStatus($user_id, $friend_id) {
        $sql = "SELECT Status FROM friends 
                WHERE (RequesterID = :user_id1 AND ReceiverID = :friend_id1) 
                   OR (RequesterID = :friend_id2 AND ReceiverID = :user_id2)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id1' => $user_id, 
            ':friend_id1' => $friend_id, 
            ':friend_id2' => $friend_id, 
            ':user_id2' => $user_id
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['Status'] : 'None';
    }
    
    /**
     * Create notification
     */
    private function createNotification($user_id, $trigger_id, $type) {
        $sql = "INSERT INTO notifications (UserID, TriggerID, Type) VALUES (:user_id, :trigger_id, :type)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':user_id' => $user_id, ':trigger_id' => $trigger_id, ':type' => $type]);
    }
    
    /**
     * Get all notifications for a user
     */
    public function getNotifications($user_id, $limit = 20) {
        $sql = "SELECT 
                    n.NotificationID, n.Type, n.IsRead, n.CreatedAt, 
                    u.Username, u.ProfilePic 
                FROM notifications n
                JOIN users u ON n.TriggerID = u.UserID
                WHERE n.UserID = :user_id
                ORDER BY n.CreatedAt DESC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCount($user_id) {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE UserID = :user_id AND IsRead = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['count'] : 0;
    }
    
    /**
     * Get friend count
     */
    public function getFriendCount($user_id) {
        $sql = "SELECT COUNT(*) as count FROM friends 
                WHERE (RequesterID = :user_id1 OR ReceiverID = :user_id2) AND Status = 'Accepted'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id1' => $user_id, ':user_id2' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['count'] : 0;
    }
    
    /**
     * Get mutual friends
     */
    public function getMutualFriends($user_id, $friend_id) {
        $sql = "SELECT u.UserID, u.Username, u.ProfilePic
                FROM users u
                WHERE u.UserID IN (
                    SELECT friend_id FROM (
                        SELECT CASE 
                            WHEN RequesterID = :user_id1 THEN ReceiverID 
                            ELSE RequesterID 
                        END as friend_id
                        FROM friends 
                        WHERE (RequesterID = :user_id2 OR ReceiverID = :user_id3) AND Status = 'Accepted'
                    ) f1
                    WHERE friend_id IN (
                        SELECT friend_id FROM (
                            SELECT CASE 
                                WHEN RequesterID = :friend_id1 THEN ReceiverID 
                                ELSE RequesterID 
                            END as friend_id
                            FROM friends 
                            WHERE (RequesterID = :friend_id2 OR ReceiverID = :friend_id3) AND Status = 'Accepted'
                        ) f2
                    )
                )
                LIMIT 5";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id1' => $user_id,
            ':user_id2' => $user_id,
            ':user_id3' => $user_id,
            ':friend_id1' => $friend_id,
            ':friend_id2' => $friend_id,
            ':friend_id3' => $friend_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get mutual friends count using the Database Function
     */
    public function getMutualFriendsCount($user_id, $friend_id) {
        try {
            $sql = "SELECT GetMutualFriendsCount(:user1, :user2) as count";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user1' => $user_id, ':user2' => $friend_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['count'] : 0;
        } catch (PDOException $e) {
            // ប្រព័ន្ធ Backup ពេលរក Function មិនឃើញ
            $sql = "SELECT COUNT(*) as count FROM friends f1
                    JOIN friends f2 ON (f1.RequesterID = f2.RequesterID OR f1.RequesterID = f2.ReceiverID OR f1.ReceiverID = f2.RequesterID OR f1.ReceiverID = f2.ReceiverID)
                    WHERE (f1.RequesterID = :user1_1 OR f1.ReceiverID = :user1_2)
                      AND (f2.RequesterID = :user2_1 OR f2.ReceiverID = :user2_2)
                      AND f1.Status = 'Accepted' AND f2.Status = 'Accepted' AND f1.FriendshipID <> f2.FriendshipID";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user1_1' => $user_id, ':user1_2' => $user_id, ':user2_1' => $friend_id, ':user2_2' => $friend_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['count'] : 0;
        }
    }
}
?>