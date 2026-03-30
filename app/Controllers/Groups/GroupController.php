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
            header("Location: " . BASE_URL . "app/Views/Login/login_gmail.php");
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
        echo json_encode($result);
    }
    
    /**
     * Create group
     */
    public function createGroup() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "app/Views/Login/login_gmail.php");
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
                    header("Location: " . BASE_URL . "app/Views/Groups/groups.php?success=created");
                    exit();
                } else {
                    $errors[] = $group_id;
                }
            }
            
            return ['errors' => $errors, 'form_data' => $_POST];
        }
        
        return [];
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
}
?>
