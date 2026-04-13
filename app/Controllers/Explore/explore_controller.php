<?php
session_start();
require_once '../../../config/db_config.php';
require_once '../../Models/Explore/ProfileModel.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$model = new ProfileModel($conn);
$user_id = $_SESSION['user_id'];

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get_posts':
                try {
                    $category = isset($_GET['category']) ? $_GET['category'] : null;
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                    
                    // Get posts from model
                    $posts = $model->getExplorePosts($category, $limit);
                    
                    // Add like status and comments for each post
                    foreach ($posts as &$post) {
                        $post['liked'] = $model->checkLikeStatus($post['PostID'], $user_id);
                        $post['comments'] = $model->getComments($post['PostID']);
                        $post['like_count'] = $model->getLikeCount($post['PostID']);
                        $post['comment_count'] = $model->getCommentCount($post['PostID']);
                    }
                    
                    echo json_encode(['success' => true, 'posts' => $posts]);
                } catch (Exception $e) {
                    error_log("Error in get_posts: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;
                
            case 'get_notifications':
                try {
                    $notifications = $model->getNotifications($user_id);
                    $unread_count = $model->getUnreadNotificationCount($user_id);
                    echo json_encode(['success' => true, 'notifications' => $notifications, 'unread_count' => $unread_count]);
                } catch (Exception $e) {
                    error_log("Error in get_notifications: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No action specified']);
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    
    if (!$action) {
        // Try to get from JSON if not in POST
        $json_data = json_decode(file_get_contents('php://input'), true);
        $action = $json_data['action'] ?? null;
    }

    if ($action) {
        switch ($action) {
            case 'like':
                try {
                    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : ($json_data['post_id'] ?? null);
                    if (!$post_id) {
                        echo json_encode(['success' => false, 'error' => 'Post ID required']);
                        break;
                    }
                    
                    $result = $model->likePost($post_id, $user_id);
                    if ($result === 'liked' || $result === 'unliked') {
                        echo json_encode(['success' => true, 'action' => $result]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to process like']);
                    }
                } catch (Exception $e) {
                    error_log("Error in like: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;
                
            case 'comment':
                try {
                    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : ($json_data['post_id'] ?? null);
                    $comment = isset($_POST['comment']) ? $_POST['comment'] : ($json_data['comment'] ?? null);
                    
                    if (!$post_id || !$comment) {
                        echo json_encode(['success' => false, 'error' => 'Post ID and comment required']);
                        break;
                    }
                    
                    $result = $model->addComment($post_id, $user_id, $comment);
                    echo json_encode(['success' => $result]);
                } catch (Exception $e) {
                    error_log("Error in comment: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;
                
            case 'create_post':
                try {
                    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
                    if (empty($content)) {
                        echo json_encode(['success' => false, 'error' => 'Content is required']);
                        break;
                    }
                    
                    $image_path = null;
                    
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                        $upload_dir = '../../../public/uploads/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        $file_type = $_FILES['image']['type'];
                        
                        if (in_array($file_type, $allowed_types)) {
                            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                            $filename = time() . '_' . uniqid() . '.' . $extension;
                            $target_file = $upload_dir . $filename;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                                $image_path = 'uploads/' . $filename;
                            } else {
                                echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
                                break;
                            }
                        } else {
                            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
                            break;
                        }
                    }
                    
                    $result = $model->createPost($user_id, $content, $image_path);
                    echo json_encode(['success' => $result]);
                } catch (Exception $e) {
                    error_log("Error in create_post: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No action specified']);
    }
}
?>