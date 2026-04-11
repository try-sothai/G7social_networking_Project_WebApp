<?php
session_start();

require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Helpers/admin.php';
require_once '../../Models/Admin/AdminModel.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

lc_admin_require_admin($conn);

$profileModel = new ProfileModel($conn);
$adminModel = new AdminModel($conn);
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUser = $profileModel->getUserData($currentUserId);
$stats = $adminModel->getOverviewStats();

$search = trim((string)($_GET['q'] ?? ''));
$roleFilter = trim((string)($_GET['role_filter'] ?? ''));
$statusFilter = trim((string)($_GET['status_filter'] ?? ''));
$reportStatus = trim((string)($_GET['report_status'] ?? ''));
$reportSearch = trim((string)($_GET['report_q'] ?? ''));

$users = $adminModel->getUsers($search, $roleFilter, $statusFilter);
$reports = $adminModel->getReports($reportStatus, $reportSearch);
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$profilePicPath = 'https://via.placeholder.com/150';
if (!empty($currentUser['ProfilePic'])) {
    $candidate = __DIR__ . '/../../../public/img/' . $currentUser['ProfilePic'];
    if (is_file($candidate)) {
        $profilePicPath = '../../../public/img/' . $currentUser['ProfilePic'];
    }
}

function admin_badge_class(string $value): string
{
    $map = [
        'SuperAdmin' => 'danger',
        'Admin' => 'info',
        'User' => 'neutral',
        'Active' => 'success',
        'Suspended' => 'warning',
        'Banned' => 'danger',
        'Pending' => 'warning',
        'Reviewed' => 'info',
        'Resolved' => 'success',
        'Dismissed' => 'neutral',
    ];

    return $map[$value] ?? 'neutral';
}

function admin_render_return_fields(string $search, string $roleFilter, string $statusFilter, string $reportStatus, string $reportSearch): void
{
    foreach ([
        'q' => $search,
        'role_filter' => $roleFilter,
        'status_filter' => $statusFilter,
        'report_status' => $reportStatus,
        'report_q' => $reportSearch,
    ] as $key => $value) {
        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
    }
}

function admin_short_text(?string $value, int $length = 110): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return 'No details';
    }

    if (mb_strlen($value, 'UTF-8') <= $length) {
        return $value;
    }

    return mb_substr($value, 0, $length - 3, 'UTF-8') . '...';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LocalConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(lc_public_asset('css/Home/dashboard.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(lc_public_asset('css/Admin/admin.css')) ?>">
</head>
<body class="admin-dashboard">
    <?php lc_shell_render_sidebar(['active' => 'admin', 'show_create_post' => false]); ?>

    <div class="main-content">
        <?php
            ob_start();
        ?>
            <i class="fa-solid fa-user-shield"></i>
        <?php
            $adminNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_placeholder' => 'Search admin data...',
                'notification_html' => $adminNotificationHtml,
                'user_name' => (string)($currentUser['Username'] ?? ($_SESSION['username'] ?? 'Admin')),
                'profile_image' => $profilePicPath,
            ]);
        ?>

        <div class="admin-page">
            <div class="admin-hero">
                <div>
                    <p class="admin-kicker">Control Center</p>
                    <h1>Admin Dashboard</h1>
                    <p class="admin-subtitle">Manage users, account access, and incoming content reports from one place.</p>
                </div>
                <div class="admin-role-chip">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span><?= htmlspecialchars(lc_current_user_role()) ?></span>
                </div>
            </div>

            <?php if (is_array($flash) && !empty($flash['message'])): ?>
                <div class="admin-flash <?= htmlspecialchars((string)$flash['type']) ?>">
                    <?= htmlspecialchars((string)$flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-stats-grid">
                <div class="admin-stat-card">
                    <span class="admin-stat-label">Total Users</span>
                    <strong><?= number_format((int)$stats['total_users']) ?></strong>
                    <span class="admin-stat-note">All registered accounts</span>
                </div>
                <div class="admin-stat-card">
                    <span class="admin-stat-label">Active Accounts</span>
                    <strong><?= number_format((int)$stats['active_users']) ?></strong>
                    <span class="admin-stat-note">Allowed to sign in</span>
                </div>
                <div class="admin-stat-card">
                    <span class="admin-stat-label">Admin Team</span>
                    <strong><?= number_format((int)$stats['admins']) ?></strong>
                    <span class="admin-stat-note">Admin + SuperAdmin users</span>
                </div>
                <div class="admin-stat-card">
                    <span class="admin-stat-label">Restricted Users</span>
                    <strong><?= number_format((int)$stats['restricted_users']) ?></strong>
                    <span class="admin-stat-note">Suspended or banned</span>
                </div>
                <div class="admin-stat-card">
                    <span class="admin-stat-label">Pending Reports</span>
                    <strong><?= number_format((int)$stats['pending_reports']) ?></strong>
                    <span class="admin-stat-note">Need moderator review</span>
                </div>
                <div class="admin-stat-card">
                    <span class="admin-stat-label">Resolved Reports</span>
                    <strong><?= number_format((int)$stats['resolved_reports']) ?></strong>
                    <span class="admin-stat-note">Closed moderation items</span>
                </div>
            </div>

            <section class="admin-section">
                <div class="admin-section-head">
                    <div>
                        <h2>User Management</h2>
                        <p>Search members, update admin roles, and control account status.</p>
                    </div>
                </div>

                <form class="admin-filter-bar" method="get">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search username or email">
                    <select name="role_filter">
                        <option value="">All roles</option>
                        <?php foreach (['User', 'Admin', 'SuperAdmin'] as $role): ?>
                            <option value="<?= htmlspecialchars($role) ?>" <?= $roleFilter === $role ? 'selected' : '' ?>><?= htmlspecialchars($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status_filter">
                        <option value="">All statuses</option>
                        <?php foreach (['Active', 'Suspended', 'Banned'] as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="report_status" value="<?= htmlspecialchars($reportStatus) ?>">
                    <input type="hidden" name="report_q" value="<?= htmlspecialchars($reportSearch) ?>">
                    <button type="submit">Filter Users</button>
                </form>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Role Action</th>
                                <th>Status Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users === []): ?>
                                <tr>
                                    <td colspan="6" class="admin-empty">No users matched your filters.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="admin-user-cell">
                                            <div class="admin-user-avatar">
                                                <?= strtoupper(substr((string)($user['Username'] ?? 'U'), 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars((string)$user['Username']) ?></strong>
                                                <div><?= htmlspecialchars((string)($user['Email'] ?? 'No email')) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="admin-badge <?= admin_badge_class((string)$user['Role']) ?>"><?= htmlspecialchars((string)$user['Role']) ?></span></td>
                                    <td><span class="admin-badge <?= admin_badge_class((string)$user['AccountStatus']) ?>"><?= htmlspecialchars((string)$user['AccountStatus']) ?></span></td>
                                    <td><?= htmlspecialchars((string)($user['CreatedAt'] ?? 'Unknown')) ?></td>
                                    <td>
                                        <form method="post" action="<?= htmlspecialchars(BASE_URL . 'app/Controllers/Admin/admin_actions.php') ?>" class="admin-inline-form">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="target_user_id" value="<?= (int)$user['UserID'] ?>">
                                            <?php admin_render_return_fields($search, $roleFilter, $statusFilter, $reportStatus, $reportSearch); ?>
                                            <select name="new_role">
                                                <?php foreach (['User', 'Admin', 'SuperAdmin'] as $role): ?>
                                                    <option value="<?= htmlspecialchars($role) ?>" <?= (string)$user['Role'] === $role ? 'selected' : '' ?>><?= htmlspecialchars($role) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit">Save</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="post" action="<?= htmlspecialchars(BASE_URL . 'app/Controllers/Admin/admin_actions.php') ?>" class="admin-inline-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="target_user_id" value="<?= (int)$user['UserID'] ?>">
                                            <?php admin_render_return_fields($search, $roleFilter, $statusFilter, $reportStatus, $reportSearch); ?>
                                            <select name="new_status">
                                                <?php foreach (['Active', 'Suspended', 'Banned'] as $status): ?>
                                                    <option value="<?= htmlspecialchars($status) ?>" <?= (string)$user['AccountStatus'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit">Apply</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-section">
                <div class="admin-section-head">
                    <div>
                        <h2>Report Review</h2>
                        <p>Review post reports and update their moderation status.</p>
                    </div>
                </div>

                <form class="admin-filter-bar" method="get">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="role_filter" value="<?= htmlspecialchars($roleFilter) ?>">
                    <input type="hidden" name="status_filter" value="<?= htmlspecialchars($statusFilter) ?>">
                    <input type="text" name="report_q" value="<?= htmlspecialchars($reportSearch) ?>" placeholder="Search reason, reporter, target, or post text">
                    <select name="report_status">
                        <option value="">All report states</option>
                        <?php foreach (['Pending', 'Reviewed', 'Resolved', 'Dismissed'] as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>" <?= $reportStatus === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Filter Reports</button>
                </form>

                <div class="admin-report-list">
                    <?php if ($reports === []): ?>
                        <div class="admin-empty-card">No reports found yet. Once users report posts, they will appear here.</div>
                    <?php endif; ?>

                    <?php foreach ($reports as $report): ?>
                        <article class="admin-report-card">
                            <div class="admin-report-top">
                                <div>
                                    <div class="admin-report-meta">
                                        <span class="admin-badge <?= admin_badge_class((string)$report['Status']) ?>"><?= htmlspecialchars((string)$report['Status']) ?></span>
                                        <span><?= htmlspecialchars((string)$report['EntityType']) ?> #<?= (int)$report['EntityID'] ?></span>
                                        <span>Created <?= htmlspecialchars((string)$report['CreatedAt']) ?></span>
                                    </div>
                                    <h3><?= htmlspecialchars((string)$report['Reason']) ?></h3>
                                </div>
                            </div>

                            <div class="admin-report-grid">
                                <div>
                                    <label>Reporter</label>
                                    <p><?= htmlspecialchars((string)$report['ReporterName']) ?><?php if (!empty($report['ReporterEmail'])): ?> | <?= htmlspecialchars((string)$report['ReporterEmail']) ?><?php endif; ?></p>
                                </div>
                                <div>
                                    <label>Target User</label>
                                    <p><?= htmlspecialchars((string)($report['TargetUserName'] ?? 'Unknown user')) ?></p>
                                </div>
                                <div class="admin-report-span">
                                    <label>Post Preview</label>
                                    <p><?= htmlspecialchars(admin_short_text((string)($report['PostContent'] ?? ''), 180)) ?></p>
                                </div>
                                <div class="admin-report-span">
                                    <label>Reporter Details</label>
                                    <p><?= htmlspecialchars(admin_short_text((string)($report['Details'] ?? ''), 180)) ?></p>
                                </div>
                                <div>
                                    <label>Reviewed By</label>
                                    <p><?= htmlspecialchars((string)($report['ReviewerName'] ?? 'Not reviewed yet')) ?></p>
                                </div>
                                <div>
                                    <label>Reviewed At</label>
                                    <p><?= htmlspecialchars((string)($report['ReviewedAt'] ?? 'Pending review')) ?></p>
                                </div>
                            </div>

                            <div class="admin-report-actions">
                                <?php foreach (['Reviewed', 'Resolved', 'Dismissed', 'Pending'] as $status): ?>
                                    <form method="post" action="<?= htmlspecialchars(BASE_URL . 'app/Controllers/Admin/admin_actions.php') ?>">
                                        <input type="hidden" name="action" value="update_report">
                                        <input type="hidden" name="report_id" value="<?= (int)$report['ReportID'] ?>">
                                        <input type="hidden" name="new_report_status" value="<?= htmlspecialchars($status) ?>">
                                        <?php admin_render_return_fields($search, $roleFilter, $statusFilter, $reportStatus, $reportSearch); ?>
                                        <button type="submit" class="<?= $status === 'Resolved' ? 'success' : ($status === 'Dismissed' ? 'muted' : '') ?>">
                                            Mark <?= htmlspecialchars($status) ?>
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>
    <?php lc_shell_render_user_bootstrap($currentUserId, (string)($currentUser['Username'] ?? 'Admin'), $profilePicPath); ?>
    <script src="<?= htmlspecialchars(lc_public_asset('js/common/theme-toggle.js')) ?>"></script>
    <script src="<?= htmlspecialchars(lc_public_asset('js/Home/responsive.js')) ?>"></script>
</body>
</html>
