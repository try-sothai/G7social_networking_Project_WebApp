<?php
session_start();

require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Helpers/admin.php';
require_once '../../Models/Admin/AdminModel.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

if (!isset($_SESSION['user_id'])) {
        header("Location: ../Login/login_gmail.php");
        exit();
    }
    
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
    <!-- Libraries សម្រាប់ Export PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <style>
        /* 🎨 Typography Improvements */
        body.admin-dashboard,
        .admin-dashboard input,
        .admin-dashboard select,
        .admin-dashboard button {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            color: #334155;
            line-height: 1.6;
        }
        
        .admin-dashboard h1, 
        .admin-dashboard h2, 
        .admin-dashboard h3 {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #0f172a;
            letter-spacing: -0.02em;
            font-weight: 700;
        }

        /* 📐 Page Alignment & Spacing */
        .admin-page {
            padding: 20px 0; /* ដកគម្លាតសងខាងចេញដើម្បីឱ្យទំព័រពង្រីកពេញ (Full Width) */
            max-width: 100%;
            width: 100%;
        }

        /*  Primary Blue Buttons (Filter & Actions) */
        .admin-filter-bar button[type="submit"],
        .admin-inline-form button[type="submit"] {
            background: #2563eb !important;
            color: #ffffff !important;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .admin-filter-bar button[type="submit"]:hover,
        .admin-inline-form button[type="submit"]:hover {
            background: #1d4ed8 !important;
        }

        /* 🎛️ Filter Bar Styling */
        .admin-filter-bar {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
        }
        .admin-filter-bar input[type="text"] {
            flex: 2;
        }
        .admin-filter-bar select {
            flex: 1;
        }
        .admin-filter-bar input, .admin-filter-bar select {
            padding: 10px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        /* ⬇️ Export Dropdown Styles */
        .admin-export-btn {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .admin-export-btn:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        .export-dropdown {
            position: relative;
            display: inline-block;
        }
        .export-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 8px;
            background-color: #ffffff;
            min-width: 160px;
            box-shadow: 0px 8px 20px rgba(0,0,0,0.15);
            z-index: 100;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .export-menu.show-menu {
            display: block !important;
            animation: fadeIn 0.2s ease-in-out;
        }
        .export-menu a {
            color: #334155;
            padding: 12px 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 14px;
            transition: background 0.2s;
        }
        .export-menu a:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .export-menu a i { margin-right: 10px; width: 20px; text-align: center; }
        .export-menu a i.fa-file-csv { color: #10b981; }
        .export-menu a i.fa-file-excel { color: #059669; }
        .export-menu a i.fa-file-pdf { color: #e11d48; }
        .export-menu a i.fa-print { color: #3b82f6; }

        /* 📅 Export Date Filter Styles */
        .export-date-filter {
            padding: 10px 15px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #f8fafc;
        }
        .export-date-filter label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .export-date-filter input {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 4px 8px;
            font-family: inherit;
            font-size: 13px;
            width: 135px;
        }

        /* � Responsive Adjustments for Admin Dashboard */
        @media (max-width: 1024px) {
            .admin-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
                gap: 15px;
            }
        }
        @media (max-width: 768px) {
            .admin-page {
                padding: 15px 0;
            }
            .admin-hero {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .admin-filter-bar {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .admin-filter-bar input, 
            .admin-filter-bar select, 
            .admin-filter-bar button {
                width: 100%;
            }
            .admin-table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .admin-table {
                min-width: 800px; /* អនុញ្ញាតឲ្យអូសទៅឆ្វេងស្ដាំបាន */
            }
            .admin-report-grid {
                grid-template-columns: 1fr !important;
            }
            .admin-report-actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .admin-report-actions form,
            .admin-report-actions button {
                width: 100%;
            }
        }
    </style>
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
                <div class="admin-section-head" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h2>User Management</h2>
                        <p>Search members, update admin roles, and control account status.</p>
                    </div>
                    <div class="export-dropdown">
                        <button class="admin-export-btn" onclick="toggleExportMenu('usersExportMenu', event)">
                            <i class="fa-solid fa-download"></i> Export Users
                        </button>
                        <div class="export-menu" id="usersExportMenu" style="min-width: 240px;">
                            <div class="export-date-filter">
                                <label>From: <input type="date" id="export_users_from"></label>
                                <label>To: <input type="date" id="export_users_to"></label>
                            </div>
                            <a href="#" onclick="exportAdminData('csv', 'users', event)"><i class="fa-solid fa-file-csv"></i> CSV</a>
                            <a href="#" onclick="exportAdminData('excel', 'users', event)"><i class="fa-solid fa-file-excel"></i> Excel</a>
                            <a href="#" onclick="exportAdminData('pdf', 'users', event)"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                            <a href="#" onclick="exportAdminData('print', 'users', event)"><i class="fa-solid fa-print"></i> Print</a>
                        </div>
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
                <div class="admin-section-head" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h2>Report Review</h2>
                        <p>Review post reports and update their moderation status.</p>
                    </div>
                    <div class="export-dropdown">
                        <button class="admin-export-btn" onclick="toggleExportMenu('reportsExportMenu', event)">
                            <i class="fa-solid fa-download"></i> Export Reports
                        </button>
                        <div class="export-menu" id="reportsExportMenu" style="min-width: 240px;">
                            <div class="export-date-filter">
                                <label>From: <input type="date" id="export_reports_from"></label>
                                <label>To: <input type="date" id="export_reports_to"></label>
                            </div>
                            <a href="#" onclick="exportAdminData('csv', 'reports', event)"><i class="fa-solid fa-file-csv"></i> CSV</a>
                            <a href="#" onclick="exportAdminData('excel', 'reports', event)"><i class="fa-solid fa-file-excel"></i> Excel</a>
                            <a href="#" onclick="exportAdminData('pdf', 'reports', event)"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                            <a href="#" onclick="exportAdminData('print', 'reports', event)"><i class="fa-solid fa-print"></i> Print</a>
                        </div>
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
    
    <script>
        // ទាញយកទិន្នន័យពី PHP មកទុកក្នុង JavaScript
        const exportUsersData = <?= json_encode($users) ?>;
        const exportReportsData = <?= json_encode($reports) ?>;

        function toggleExportMenu(menuId, e) {
            e.preventDefault();
            // បិទ Menu ផ្សេងទៀត
            document.querySelectorAll('.export-menu.show-menu').forEach(menu => {
                if(menu.id !== menuId) menu.classList.remove('show-menu');
            });
            document.getElementById(menuId).classList.toggle('show-menu');
        }

        // បិទ Menu ពេលចុចកន្លែងផ្សេង
        window.addEventListener('click', function(event) {
            if (!event.target.closest('.export-dropdown')) {
                document.querySelectorAll('.export-menu.show-menu').forEach(menu => {
                    menu.classList.remove('show-menu');
                });
            }
        });

        function exportAdminData(type, dataset, e) {
            e.preventDefault();
            document.querySelectorAll('.export-menu.show-menu').forEach(m => m.classList.remove('show-menu'));
            
            let headers = [];
            let rows = [];
            let title = "";
            let filename = "";

            // ចាប់យកតម្លៃថ្ងៃខែពី Input
            let startDateStr = document.getElementById('export_' + dataset + '_from').value;
            let endDateStr = document.getElementById('export_' + dataset + '_to').value;

            let dateRangeText = "Date Range: All Time";
            if (startDateStr && endDateStr) {
                dateRangeText = `Date Range: ${startDateStr} to ${endDateStr}`;
            } else if (startDateStr) {
                dateRangeText = `Date Range: From ${startDateStr}`;
            } else if (endDateStr) {
                dateRangeText = `Date Range: Until ${endDateStr}`;
            }
            
            let rawData = dataset === 'users' ? exportUsersData : exportReportsData;
            
            // ចម្រាញ់ទិន្នន័យតាមកាលបរិច្ឆេទ (បើមាន)
            let filteredData = rawData.filter(item => {
                let itemDate = new Date(item.CreatedAt.replace(' ', 'T')); // សម្រួល Format ឱ្យត្រូវគ្រប់ Browser
                if (startDateStr) {
                    let start = new Date(startDateStr);
                    start.setHours(0, 0, 0, 0);
                    if (itemDate < start) return false;
                }
                if (endDateStr) {
                    let end = new Date(endDateStr);
                    end.setHours(23, 59, 59, 999);
                    if (itemDate > end) return false;
                }
                return true;
            });

            if (filteredData.length === 0) {
                alert("No data available for the selected date range.");
                return;
            }

            // រៀបចំទិន្នន័យផ្អែកលើប្រភេទដែលចង់ Export
            if (dataset === 'users') {
                headers = ["ID", "Username", "Email", "Role", "Status", "Joined"];
                rows = filteredData.map(u => [u.UserID, u.Username, u.Email, u.Role, u.AccountStatus, u.CreatedAt]);
                title = "Admin - Users List";
                filename = "admin_users_list";
            } else if (dataset === 'reports') {
                headers = ["ID", "Type", "Status", "Reason", "Reporter", "Target User", "Created At"];
                rows = filteredData.map(r => [r.ReportID, r.EntityType, r.Status, r.Reason, r.ReporterName, (r.TargetUserName || 'N/A'), r.CreatedAt]);
                title = "Admin - Reports List";
                filename = "admin_reports_list";
            }

            if (type === 'csv') {
                // ប្រើសញ្ញាធ្មេញកណ្ដុរ "" ដើម្បីការពារបញ្ហាដាច់ Text ដែលមានសញ្ញាក្បៀស (,)
                let csvContent = "data:text/csv;charset=utf-8," 
                    + `"${title}"\n"${dateRangeText}"\n"Total Records: ${rows.length}"\n\n`
                    + headers.join(",") + "\n" 
                    + rows.map(e => e.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(",")).join("\n");
                
                const link = document.createElement("a");
                link.setAttribute("href", encodeURI(csvContent));
                link.setAttribute("download", filename + ".csv");
                document.body.appendChild(link); link.click(); link.remove();
            } 
            else if (type === 'excel') {
                let tableHTML = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"></head><body><h3>${title}</h3><p><strong>${dateRangeText}</strong><br><strong>Total Records: ${rows.length}</strong></p><table border="1"><tr><th>${headers.join('</th><th>')}</th></tr>`;
                rows.forEach(r => { tableHTML += `<tr><td>${r.join('</td><td>')}</td></tr>`; }); tableHTML += `</table></body></html>`;
                const blob = new Blob([tableHTML], { type: 'application/vnd.ms-excel' });
                const link = document.createElement("a"); link.href = URL.createObjectURL(blob); link.download = filename + ".xls";
                document.body.appendChild(link); link.click(); link.remove();
            }
            else if (type === 'pdf') {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF(dataset === 'reports' ? 'l' : 'p'); // ប្រើ Landscape សម្រាប់ Reports ព្រោះវាវែង
                doc.text(title, 14, 15);
                doc.setFontSize(10);
                doc.text(dateRangeText, 14, 22); // បង្ហាញចន្លោះកាលបរិច្ឆេទ
                doc.text("Total Records: " + rows.length, 14, 28); // បង្ហាញចំនួនសរុប
                doc.autoTable({ startY: 32, head: [headers], body: rows }); // រុញតារាងចុះក្រោមបន្តិច
                doc.save(filename + ".pdf");
            }
            else if (type === 'print') {
                let printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write(`<html><head><title>Print - ${title}</title><style>body{font-family:sans-serif} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style></head><body><h2>${title}</h2><p><strong>${dateRangeText}</strong><br><strong>Total Records: ${rows.length}</strong></p><table><tr><th>${headers.join('</th><th>')}</th></tr>`);
                rows.forEach(r => { printWindow.document.write('<tr><td>' + r.join('</td><td>') + '</td></tr>'); }); printWindow.document.write('</table><script>window.print();<\/script></body></html>'); printWindow.document.close();
            }
        }
    </script>
</body>
</html>
