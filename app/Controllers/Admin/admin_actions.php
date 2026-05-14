<?php
session_start();

require_once '../../../config/db_config.php';
require_once '../../Helpers/admin.php';
require_once '../../Models/Admin/AdminModel.php';

lc_admin_require_admin($conn);

function admin_redirect_back(): void
{
    $query = [];
    foreach (['q', 'role_filter', 'status_filter', 'report_status', 'report_q'] as $key) {
        if (!isset($_POST[$key]) || trim((string)$_POST[$key]) === '') {
            continue;
        }
        $query[$key] = trim((string)$_POST[$key]);
    }

    $location = BASE_URL . 'app/Views/Admin/dashboard.php';
    if ($query !== []) {
        $location .= '?' . http_build_query($query);
    }

    header('Location: ' . $location);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect_back();
}

$model = new AdminModel($conn);
$actorId = (int)($_SESSION['user_id'] ?? 0);
$action = trim((string)($_POST['action'] ?? ''));
$result = ['success' => false, 'message' => 'Invalid admin action.'];

switch ($action) {
    case 'update_role':
        $result = $model->updateUserRole(
            $actorId,
            (int)($_POST['target_user_id'] ?? 0),
            (string)($_POST['new_role'] ?? '')
        );
        break;

    case 'update_status':
        $result = $model->updateUserStatus(
            $actorId,
            (int)($_POST['target_user_id'] ?? 0),
            (string)($_POST['new_status'] ?? '')
        );
        break;

    case 'update_report':
        $result = $model->updateReportStatus(
            $actorId,
            (int)($_POST['report_id'] ?? 0),
            (string)($_POST['new_report_status'] ?? ''),
            (string)($_POST['admin_note'] ?? '')
        );
        break;

    case 'delete_post':
        $result = $model->deletePost(
            $actorId,
            (int)($_POST['post_id'] ?? 0)
        );
        break;
}

$_SESSION['admin_flash'] = [
    'type' => !empty($result['success']) ? 'success' : 'error',
    'message' => (string)($result['message'] ?? 'Admin action finished.'),
];

lc_admin_sync_session($conn);
admin_redirect_back();
