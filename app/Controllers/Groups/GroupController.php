<?php
// app/Controllers/GroupController.php

require_once dirname(__DIR__, 2) . '/Models/Groups/GroupModel.php';
require_once dirname(__DIR__, 2) . '/Models/Profile/ProfileModel.php';

class GroupController {
    private $groupModel;
    private $profileModel;
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->groupModel = new GroupModel($db);
        $this->profileModel = new ProfileModel($db);
    }
    
    /**
     * Display groups page
     */
    public function index() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../Login/login_gmail.php");
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Get filter parameter
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        
        // Get groups based on filter
        switch($filter) {
            case 'joined':
                $groups = $this->groupModel->getJoinedGroups($user_id);
                break;
            case 'popular':
                $groups = $this->groupModel->getPopularGroups($user_id);
                break;
            default:
                $groups = $this->groupModel->getAllGroups($user_id);
        }
        
        // Get search keyword
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        if (!empty($keyword)) {
            $groups = $this->groupModel->searchGroups($keyword, $user_id);
        }
        
        // Get statistics
        $stats = $this->groupModel->getGroupStats($user_id);
        
        // Get user data
        $userData = $this->profileModel->getUserData($user_id);
        
        // Return data to view
        return [
            'groups' => $groups,
            'stats' => $stats,
            'userData' => $userData,
            'currentFilter' => $filter,
            'searchKeyword' => $keyword
        ];
    }
    
    /**
     * Join group (AJAX)
     */
    public function joinGroup() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login to join groups']);
            return;
        }
        
        $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
        $user_id = $_SESSION['user_id'];
        
        if (!$group_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
            return;
        }
        
        $result = $this->groupModel->joinGroup($group_id, $user_id);
        
        // --- Email Notification Logic ---
        if (isset($result['success']) && $result['success']) {
            $groupInfo = $this->getGroupById($group_id);
            if ($groupInfo && isset($groupInfo['CreatedBy'])) {
                $creatorId = $groupInfo['CreatedBy'];
                // កុំផ្ញើសារបើគាត់ជាម្ចាស់គ្រុបស្រាប់
                if ($creatorId != $user_id) {
                    $creatorData = $this->profileModel->getUserData($creatorId);
                    $joinerData = $this->profileModel->getUserData($user_id);
                    if ($creatorData && $joinerData && !empty($creatorData['Email'])) {
                        $this->sendJoinGroupEmail(
                            $creatorData['Email'], 
                            $creatorData['Username'], 
                            $joinerData['Username'], 
                            $groupInfo['GroupName'], 
                            $result['status'] ?? 'Approved'
                        );
                    }
                }
            }
        }
        // --------------------------------

        echo json_encode($result);
    }
    
    /**
     * Remove member from group (AJAX)
     */
    public function removeMember() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = (int)($_POST['group_id'] ?? 0);
        $memberId = (int)($_POST['member_id'] ?? 0);
        
        if ($groupId <= 0 || $memberId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            return;
        }
        
        // ឆែកមើលថាអ្នកចុចពិតជា Owner/Admin មែនឬអត់
        $group = $this->groupModel->getGroupById($groupId);
        if (!$group || (int)$group['CreatedBy'] !== (int)$userId) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: You are not the owner']);
            return;
        }
        
        if ((int)$group['CreatedBy'] === $memberId) {
            echo json_encode(['success' => false, 'message' => 'Owner cannot be removed']);
            return;
        }
        
        if ($this->groupModel->removeMember($groupId, $memberId)) {
            echo json_encode(['success' => true, 'message' => 'Member removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove member']);
        }
    }

    /**
     * Leave group (AJAX)
     */
    public function leaveGroup() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = (int)($_POST['group_id'] ?? 0);
        
        if ($groupId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
            return;
        }
        
        // ឆែកមើលថាបើគាត់ជាម្ចាស់គ្រុប គាត់មិនអាច Leave បានទេ
        $group = $this->groupModel->getGroupById($groupId);
        if ($group && (int)$group['CreatedBy'] === $userId) {
            echo json_encode(['success' => false, 'message' => 'Creator cannot leave the group. You must delete it instead.']);
            return;
        }
        
        if ($this->groupModel->removeMember($groupId, $userId)) {
            echo json_encode(['success' => true, 'message' => 'You have left the group successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to leave group.']);
        }
    }

    /**
     * Create group
     */
    public function createGroup() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../Login/login_gmail.php");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            
            // Validate input
            $errors = [];
            $group_name = trim($_POST['group_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $privacy = $_POST['privacy'] ?? 'Public';
            $category = $_POST['category'] ?? 'Other';
            
            if (empty($group_name)) {
                $errors[] = 'Group name is required';
            }
            
            if (strlen($group_name) > 255) {
                $errors[] = 'Group name is too long';
            }
            
            // Handle avatar upload
            $avatar = 'default_group.png';
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                $uploadResult = $this->uploadImage($_FILES['avatar'], 'groups');
                if ($uploadResult['success']) {
                    $avatar = $uploadResult['filename'];
                } else {
                    $errors[] = $uploadResult['message'];
                }
            }
            
            // Handle banner upload
            $banner = 'default_banner.jpg';
            if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
                $uploadResult = $this->uploadImage($_FILES['banner'], 'groups/banners');
                if ($uploadResult['success']) {
                    $banner = $uploadResult['filename'];
                }
            }
            
            if (empty($errors)) {
                $data = [
                    'group_name' => $group_name,
                    'description' => $description,
                    'privacy' => $privacy,
                    'category' => $category,
                    'avatar' => $avatar,
                    'banner' => $banner,
                    'created_by' => $user_id
                ];
                
                $group_id = $this->groupModel->createGroup($data);
                
                if (is_numeric($group_id)) {
                    $_SESSION['success_message'] = 'Group created successfully!';
                    header("Location: groups.php?success=created");
                    exit();
                } else {
                    $errors[] = $group_id;
                }
            }
            
            return ['errors' => $errors, 'form_data' => $_POST];
        }
        
        return [];
    }
    
    public function getGroupById($groupId) {
        // ទាញទិន្នន័យពី Model ដោយផ្ទាល់
        return $this->groupModel->getGroupById($groupId);
    }

    public function getGroupMembers($groupId) {
        return $this->groupModel->getGroupMembers($groupId);
    }

    /**
     * Update group data
     */
    public function updateGroup() {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = (int)($_POST['group_id'] ?? 0);
        $groupName = trim($_POST['group_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $privacy = $_POST['privacy'] ?? 'Public';
        $category = trim($_POST['category'] ?? 'Other');

        if ($groupId <= 0 || empty($groupName)) {
            return ['success' => false, 'message' => 'Group Name is required'];
        }

        try {
            // Check if user is the creator
            $checkStmt = $this->conn->prepare("SELECT CreatedBy, GroupAvatar, GroupBanner FROM `groups` WHERE GroupID = ?");
            $checkStmt->execute([$groupId]);
            $group = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$group || (int)$group['CreatedBy'] !== (int)$userId) {
                return ['success' => false, 'message' => 'Permission denied: You are not the owner'];
            }

            $avatar = $group['GroupAvatar'];
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                $uploadResult = $this->uploadImage($_FILES['avatar'], 'groups');
                if ($uploadResult['success']) {
                    $avatar = $uploadResult['filename'];
                }
            }
            
            $banner = $group['GroupBanner'];
            if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
                $uploadResult = $this->uploadImage($_FILES['banner'], 'groups/banners');
                if ($uploadResult['success']) {
                    $banner = $uploadResult['filename'];
                }
            }

            // Update group
            $stmt = $this->conn->prepare("UPDATE `groups` SET GroupName = ?, GroupDescription = ?, Privacy = ?, Category = ?, GroupAvatar = ?, GroupBanner = ? WHERE GroupID = ?");
            $stmt->execute([$groupName, $description, $privacy, $category, $avatar, $banner, $groupId]);
            
            return ['success' => true, 'message' => 'Group updated successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete group
     */
    public function deleteGroup() {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }
        
        $userId = $_SESSION['user_id'];
        $groupId = (int)($_POST['group_id'] ?? 0);

        if ($groupId <= 0) {
            return ['success' => false, 'message' => 'Invalid group ID'];
        }

        try {
            // Check if user is the creator
            $checkStmt = $this->conn->prepare("SELECT CreatedBy FROM `groups` WHERE GroupID = ?");
            $checkStmt->execute([$groupId]);
            $ownerId = $checkStmt->fetchColumn();

            if ((int)$ownerId !== (int)$userId) {
                return ['success' => false, 'message' => 'Permission denied: You are not the owner'];
            }

            // Delete group
            $stmt = $this->conn->prepare("DELETE FROM `groups` WHERE GroupID = ?");
            $stmt->execute([$groupId]);
            
            return ['success' => true, 'message' => 'Group deleted successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Upload image helper
     */
    private function uploadImage($file, $subdirectory = '') {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'message' => 'Only JPG, PNG, GIF, and WEBP images are allowed'];
        }
        
        if ($file['size'] > $max_size) {
            return ['success' => false, 'message' => 'Image size must be less than 5MB'];
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $extension;
        
        $upload_dir = dirname(__DIR__, 2) . '/../public/img/' . $subdirectory;
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $target_path = $upload_dir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'message' => 'Failed to upload image'];
    }

    /**
     * មុខងារសម្រាប់ផ្ញើ Email ទៅម្ចាស់គ្រុប
     */
    private function sendJoinGroupEmail($toEmail, $toName, $joinerName, $groupName, $status) {
        $autoloadPath = dirname(__DIR__, 3) . '/vendor/autoload.php';
        if (!file_exists($autoloadPath)) {
            return;
        }
        
        require_once $autoloadPath;
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'shy784162@gmail.com'; 
            $mail->Password   = 'obmnqzigmrupwhbq';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            
            $mail->setFrom('shy784162@gmail.com', 'LocalConnect');
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            
            if ($status == 'Pending') {
                $mail->Subject = "New Join Request for your group: " . $groupName;
                $actionText = "has requested to join your private group";
            } else {
                $mail->Subject = "New Member in your group: " . $groupName;
                $actionText = "has just joined your group";
            }
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                    <h2 style='color: #6366f1; text-align: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;'>Group Notification</h2>
                    <p style='font-size: 16px; color: #334155;'>Hello <strong>{$toName}</strong>,</p>
                    <p style='font-size: 15px; color: #475569; line-height: 1.6;'>Great news! <strong>{$joinerName}</strong> {$actionText} <strong>'{$groupName}'</strong>.</p>
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='http://localhost/G7social_networking_Project_WebApp/app/Views/Groups/groups.php' style='background: #6366f1; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px rgba(99, 102, 241, 0.25);'>View Group</a>
                    </div>
                    <p style='color: #94a3b8; font-size: 13px; margin-top: 30px; text-align: center;'>Best regards,<br><strong>LocalConnect Team</strong></p>
                </div>
            ";
            
            $mail->send();
        } catch (\Exception $e) {
            error_log("Group mail error: " . $mail->ErrorInfo);
        }
    }
}
?>