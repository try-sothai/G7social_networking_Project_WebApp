<?php
// app/Models/GroupModel.php

class GroupModel {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db; // $db គឺជា PDO connection
    }
    
    /**
     * Get all public groups or groups user is member of
     */
    public function getAllGroups($user_id = null) {
        try {
            if ($user_id) {
                $query = "SELECT g.*, 
                            (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND Status = 'Approved') as member_count,
                            (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND UserID = :user_id AND Status = 'Approved') as is_member
                          FROM `groups` g 
                          WHERE g.Privacy = 'Public' OR 
                            (g.Privacy = 'Private' AND EXISTS (
                                SELECT 1 FROM `group_members` 
                                WHERE GroupID = g.GroupID AND UserID = :user_id2 AND Status = 'Approved'
                            ))
                          ORDER BY g.CreatedAt DESC";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $query = "SELECT g.*, 
                            (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND Status = 'Approved') as member_count,
                            0 as is_member
                          FROM `groups` g 
                          WHERE g.Privacy = 'Public'
                          ORDER BY g.CreatedAt DESC";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error in getAllGroups: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get groups joined by user
     */
    public function getJoinedGroups($user_id) {
        try {
            $query = "SELECT g.*, 
                        (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND Status = 'Approved') as member_count,
                        1 as is_member
                      FROM `groups` g
                      INNER JOIN `group_members` gm ON g.GroupID = gm.GroupID
                      WHERE gm.UserID = :user_id AND gm.Status = 'Approved'
                      ORDER BY gm.JoinedAt DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getJoinedGroups: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get popular groups (most members)
     */
    public function getPopularGroups($user_id = null, $limit = 10) {
        try {
            $query = "SELECT g.*, 
                        (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND Status = 'Approved') as member_count";
            
            if ($user_id) {
                $query .= ", (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND UserID = :user_id AND Status = 'Approved') as is_member";
            } else {
                $query .= ", 0 as is_member";
            }
            
            $query .= " FROM `groups` g 
                        WHERE g.Privacy = 'Public'
                        ORDER BY member_count DESC 
                        LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            
            if ($user_id) {
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getPopularGroups: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get single group by ID
     */
    public function getGroupById($group_id, $user_id = null) {
        try {
            $query = "SELECT g.*, 
                        (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND Status = 'Approved') as member_count,
                        u.Username as creator_name";
            
            if ($user_id) {
                $query .= ", (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND UserID = :user_id AND Status = 'Approved') as is_member";
            } else {
                $query .= ", 0 as is_member";
            }
            
            $query .= " FROM `groups` g
                        INNER JOIN users u ON g.CreatedBy = u.UserID
                        WHERE g.GroupID = :group_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
            
            if ($user_id) {
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getGroupById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new group
     */
    public function createGroup($data) {
        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO `groups` (GroupName, GroupDescription, Privacy, Category, GroupAvatar, GroupBanner, CreatedBy, CreatedAt) 
                      VALUES (:group_name, :description, :privacy, :category, :avatar, :banner, :created_by, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':group_name', $data['group_name'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':privacy', $data['privacy'], PDO::PARAM_STR);
            $stmt->bindParam(':category', $data['category'], PDO::PARAM_STR);
            $stmt->bindParam(':avatar', $data['avatar'], PDO::PARAM_STR);
            $stmt->bindParam(':banner', $data['banner'], PDO::PARAM_STR);
            $stmt->bindParam(':created_by', $data['created_by'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $group_id = $this->conn->lastInsertId();
                
                // Add creator as admin member
                $memberQuery = "INSERT INTO `group_members` (GroupID, UserID, Role, Status, JoinedAt) VALUES (:group_id, :user_id, 'Admin', 'Approved', NOW())";
                $memberStmt = $this->conn->prepare($memberQuery);
                $memberStmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
                $memberStmt->bindParam(':user_id', $data['created_by'], PDO::PARAM_INT);
                $memberStmt->execute();
                
                $this->conn->commit();
                return $group_id;
            }
            
            $this->conn->rollBack();
            return "Failed to insert group: " . implode(", ", $stmt->errorInfo());
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error in createGroup: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }
    
    /**
     * Join a group
     */
    public function joinGroup($group_id, $user_id) {
        try {
            // Check if already a member
            $checkQuery = "SELECT * FROM `group_members` WHERE GroupID = :group_id AND UserID = :user_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
            $checkStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Already a member'];
            }
            
            // Get group privacy
            $groupQuery = "SELECT Privacy FROM `groups` WHERE GroupID = :group_id";
            $groupStmt = $this->conn->prepare($groupQuery);
            $groupStmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
            $groupStmt->execute();
            $group = $groupStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$group) {
                return ['success' => false, 'message' => 'Group not found'];
            }
            
            $status = ($group['Privacy'] == 'Public') ? 'Approved' : 'Pending';
            $role = 'Member';
            
            $query = "INSERT INTO `group_members` (GroupID, UserID, Role, Status, JoinedAt) VALUES (:group_id, :user_id, :role, :status, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => $status == 'Approved' ? 'Successfully joined group!' : 'Join request sent to admin!',
                    'status' => $status
                ];
            }
            
            return ['success' => false, 'message' => 'Database error'];
        } catch (PDOException $e) {
            error_log("Error in joinGroup: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get group statistics
     */
    public function getGroupStats($user_id = null) {
        try {
            $stats = [];
            
            // Total public groups
            $totalQuery = "SELECT COUNT(*) as total FROM `groups` WHERE Privacy = 'Public'";
            $totalStmt = $this->conn->prepare($totalQuery);
            $totalStmt->execute();
            $stats['total_groups'] = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Groups joined by user
            if ($user_id) {
                $joinedQuery = "SELECT COUNT(*) as joined FROM `group_members` WHERE UserID = :user_id AND Status = 'Approved'";
                $joinedStmt = $this->conn->prepare($joinedQuery);
                $joinedStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $joinedStmt->execute();
                $stats['joined_groups'] = $joinedStmt->fetch(PDO::FETCH_ASSOC)['joined'];
            } else {
                $stats['joined_groups'] = 0;
            }
            
            // New groups today
            $todayQuery = "SELECT COUNT(*) as today FROM groups WHERE DATE(CreatedAt) = CURDATE()";
            $todayStmt = $this->conn->prepare($todayQuery);
            $todayStmt->execute();
            $stats['new_today'] = $todayStmt->fetch(PDO::FETCH_ASSOC)['today'];
            
            return $stats;
        } catch (PDOException $e) {
            error_log("Error in getGroupStats: " . $e->getMessage());
            return [
                'total_groups' => 0,
                'joined_groups' => 0,
                'new_today' => 0
            ];
        }
    }
    
    /**
     * Search groups
     */
    public function searchGroups($keyword, $user_id = null) {
        try {
            $searchTerm = "%{$keyword}%";
            
            $query = "SELECT g.*, 
                        (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND Status = 'Approved') as member_count";
            
            if ($user_id) {
                $query .= ", (SELECT COUNT(*) FROM `group_members` WHERE GroupID = g.GroupID AND UserID = :user_id AND Status = 'Approved') as is_member";
            } else {
                $query .= ", 0 as is_member";
            }
            
            $query .= " FROM `groups` g 
                        WHERE (g.GroupName LIKE :keyword OR g.GroupDescription LIKE :keyword2) 
                        AND g.Privacy = 'Public'
                        ORDER BY g.CreatedAt DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':keyword', $searchTerm, PDO::PARAM_STR);
            $stmt->bindParam(':keyword2', $searchTerm, PDO::PARAM_STR);
            
            if ($user_id) {
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in searchGroups: " . $e->getMessage());
            return [];
        }
    }

    public function updateGroupForOwner($group_id, $owner_id, $group_name, $description, $privacy, $category) {
        try {
            $query = "UPDATE `groups`
                      SET GroupName = :group_name,
                          GroupDescription = :description,
                          Privacy = :privacy,
                          Category = :category
                      WHERE GroupID = :group_id AND CreatedBy = :owner_id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':group_name' => $group_name,
                ':description' => $description,
                ':privacy' => $privacy,
                ':category' => $category,
                ':group_id' => $group_id,
                ':owner_id' => $owner_id
            ]);
        } catch (PDOException $e) {
            error_log("Error in updateGroupForOwner: " . $e->getMessage());
            return false;
        }
    }

    public function deleteGroupForOwner($group_id, $owner_id) {
        try {
            $this->conn->beginTransaction();

            $check = $this->conn->prepare("SELECT GroupID FROM `groups` WHERE GroupID = :group_id AND CreatedBy = :owner_id LIMIT 1");
            $check->execute([
                ':group_id' => $group_id,
                ':owner_id' => $owner_id
            ]);
            if (!$check->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->rollBack();
                return false;
            }

            $deleteMembers = $this->conn->prepare("DELETE FROM `group_members` WHERE GroupID = :group_id");
            $deleteMembers->execute([':group_id' => $group_id]);

            $deleteGroup = $this->conn->prepare("DELETE FROM `groups` WHERE GroupID = :group_id AND CreatedBy = :owner_id");
            $deleteGroup->execute([
                ':group_id' => $group_id,
                ':owner_id' => $owner_id
            ]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error in deleteGroupForOwner: " . $e->getMessage());
            return false;
        }
    }
}
?>
