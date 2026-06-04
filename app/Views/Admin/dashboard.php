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
$topPosts = $adminModel->getTopPosts(5);
$recentUsers = $adminModel->getRecentlyActiveUsers(5);
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$profilePicPath = getAvatarUrl($currentUser['ProfilePic'] ?? null);

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
        'Resolved' => 'success'
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
        return '';
    }

    if (mb_strlen($value, 'UTF-8') <= $length) {
        return $value;
    }

    return mb_substr($value, 0, $length - 3, 'UTF-8') . '...';
}

function admin_strike_badge(int $strikes): string
{
    if ($strikes === 0) return '';
    if ($strikes >= 5) {
        $cls = 'danger';
        $label = "⚠️ {$strikes} Strikes — AUTO BANNED";
    } elseif ($strikes >= 3) {
        $cls = 'warning';
        $label = "⚠️ {$strikes} Strikes — AUTO SUSPENDED";
    } else {
        $cls = 'info';
        $label = "⚠️ {$strikes} Strike" . ($strikes > 1 ? 's' : '');
    }
    return '<span class="admin-badge ' . $cls . '" title="Resolved reports against this user">' . htmlspecialchars($label) . '</span>';
}

function admin_post_preview(array $report): string
{
    $content = trim((string)($report['PostContent'] ?? ''));
    $mediaType = (string)($report['PostMediaType'] ?? 'text');
    $hasMedia = !empty($report['PostMediaPath']) || !empty($report['PostImagePath']);

    if ($content !== '') {
        $short = admin_short_text($content, 200);
        $icon = $mediaType === 'video' ? '🎬 ' : ($hasMedia ? '🖼️ ' : '');
        return $icon . htmlspecialchars($short);
    }

    if ($mediaType === 'video' || (!empty($report['PostMediaPath']) && preg_match('/\.(mp4|mov|avi|mkv|webm)$/i', (string)$report['PostMediaPath']))) {
        return '🎬 <em>[Video post — no caption]</em>';
    }

    if ($hasMedia || $mediaType === 'image') {
        return '🖼️ <em>[Image post — no caption]</em>';
    }

    return '<em style="color:#94a3b8">[Post deleted or content unavailable]</em>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LocalConnect</title>
    <?php lc_shell_render_head_assets(); ?>    
    <link rel="stylesheet" href="<?= htmlspecialchars(lc_public_asset('css/Home/dashboard.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(lc_public_asset('css/Admin/admin.css')) ?>">
    <!-- Libraries for PDF Export -->
    <script src="../../../public/js/vendor/jspdf.umd.min.js"></script>
    <script src="../../../public/js/vendor/jspdf.plugin.autotable.min.js"></script>
    <style>
        /* 🎨 Typography Improvements */
        .admin-page,
        .admin-page input,
        .admin-page select,
        .admin-page button {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            color: #334155;
            line-height: 1.6;
        }
        
        .admin-page h1, 
        .admin-page h2, 
        .admin-page h3 {
            color: #0f172a;
            letter-spacing: -0.02em;
            font-weight: 700;
        }

        /* 📐 Page Alignment & Spacing */
        .admin-page {
            margin-top: 20px;
            max-width: 100% !important; /* Force page to stretch full width to match Header */
            padding: 0 !important; /* Remove padding that pushes the content inward */
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

        /* 🔗 Post link in report card */
        .admin-post-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
        }
        .admin-post-link:hover { text-decoration: underline; }

        /* 🌙 Dark mode note textarea */
        body.dark-mode .admin-note-input {
            background: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: rgba(148,163,184,0.3) !important;
        }

        /* 🛡️ Report Action Buttons Styling */
        .admin-report-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        .admin-report-actions button {
            padding: 8px 16px !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #ffffff !important; /* White text for better contrast */
        }
        .admin-btn-pending { background: #f59e0b !important; }
        .admin-btn-pending:hover { background: #d97706 !important; }
        .admin-btn-reviewed { background: #3b82f6 !important; }
        .admin-btn-reviewed:hover { background: #2563eb !important; }
        .admin-btn-resolved { background: #10b981 !important; }
        .admin-btn-resolved:hover { background: #059669 !important; }

        /* 🌙 Dark Mode Support for Admin */
        body.dark-mode .admin-dashboard,
        body.dark-mode .admin-dashboard h1, 
        body.dark-mode .admin-dashboard h2, 
        body.dark-mode .admin-dashboard h3,
        body.dark-mode .admin-kicker,
        body.dark-mode .admin-stat-label,
        body.dark-mode .admin-stat-note,
        body.dark-mode .admin-subtitle,
        body.dark-mode .admin-table td,
        body.dark-mode .admin-report-card label,
        body.dark-mode .admin-report-card p {
            color: #e2e8f0 !important;
        }
        
        body.dark-mode .admin-stat-card,
        body.dark-mode .admin-section,
        body.dark-mode .admin-report-card,
        body.dark-mode .export-menu {
            background: #111827 !important;
            border: 1px solid rgba(148, 163, 184, 0.22) !important;
            box-shadow: none !important;
        }
        
        body.dark-mode .admin-filter-bar input,
        body.dark-mode .admin-filter-bar select,
        body.dark-mode .export-date-filter input,
        body.dark-mode .admin-inline-form select {
            background: #0f172a;
            color: #e2e8f0;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }
        
        body.dark-mode .admin-table th {
            background: #0f172a !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22) !important;
        }
        
        body.dark-mode .admin-table td,
        body.dark-mode .admin-report-top,
        body.dark-mode .admin-report-actions {
            border-color: rgba(148, 163, 184, 0.22) !important;
        }

        /* Export dropdown — full dark mode */
        body.dark-mode .export-date-filter {
            background: #0f172a !important;
            border-color: rgba(148, 163, 184, 0.22) !important;
        }
        body.dark-mode .export-date-filter label {
            color: #94a3b8 !important;
        }
        body.dark-mode .export-date-filter input {
            background: #1e293b !important;
            color: #e2e8f0 !important;
            border-color: rgba(148, 163, 184, 0.3) !important;
            color-scheme: dark;
        }
        body.dark-mode .export-menu a {
            color: #cbd5e1 !important;
        }
        body.dark-mode .export-menu a:hover {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
        }

        body.dark-mode .admin-export-btn {
            background: #1e293b;
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.22);
        }
        body.dark-mode .admin-export-btn:hover {
            background: #334155;
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
                padding: 15px !important;
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
                min-width: 800px; /* Allow horizontal scrolling left and right */
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

            <!-- Analytics Widgets: Top Posts + Recently Active Users -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;">

                <!-- post_analytics view -->
                <div class="admin-section" style="margin-bottom:0; padding:20px;">
                    <h3 style="margin:0 0 16px; font-size:16px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-fire" style="color:#f59e0b;"></i> Top Posts by Engagement
                    </h3>
                    <?php if (empty($topPosts)): ?>
                        <p style="color:#94a3b8; font-size:13px;">No posts with engagement yet.</p>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <?php foreach ($topPosts as $tp): ?>
                                <div style="display:flex; align-items:flex-start; gap:12px; padding:10px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-weight:600; font-size:13px; margin-bottom:2px;">
                                            <?= htmlspecialchars($tp['Username']) ?>
                                        </div>
                                        <div style="font-size:12px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;">
                                            <?= htmlspecialchars(substr((string)($tp['Content'] ?? '[media]'), 0, 60)) ?>
                                        </div>
                                        <div style="display:flex; gap:10px; margin-top:5px; font-size:11px; color:#64748b;">
                                            <span><i class="fa-solid fa-heart" style="color:#ef4444;"></i> <?= (int)$tp['total_reactions'] ?></span>
                                            <span><i class="fa-solid fa-comment" style="color:#3b82f6;"></i> <?= (int)$tp['total_comments'] ?></span>
                                            <span><i class="fa-solid fa-share" style="color:#10b981;"></i> <?= (int)$tp['total_shares'] ?></span>
                                        </div>
                                    </div>
                                    <div style="text-align:center; background:#6366f1; color:#fff; border-radius:8px; padding:6px 10px; font-size:12px; font-weight:700; white-space:nowrap; flex-shrink:0;">
                                        &#9889; <?= (int)$tp['engagement_score'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- vw_user_profiles view -->
                <div class="admin-section" style="margin-bottom:0; padding:20px;">
                    <h3 style="margin:0 0 16px; font-size:16px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-circle" style="color:#10b981; font-size:10px;"></i> Recently Active Users
                    </h3>
                    <?php if (empty($recentUsers)): ?>
                        <p style="color:#94a3b8; font-size:13px;">No recent activity recorded.</p>
                    <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <?php foreach ($recentUsers as $ru): ?>
                                <?php
                                    $ruPic = !empty($ru['ProfilePic']) && $ru['ProfilePic'] !== 'default.png'
                                        ? BASE_URL . 'public/uploads/avatars/' . htmlspecialchars($ru['ProfilePic'])
                                        : BASE_URL . 'public/img/default-avatar.svg';
                                    $lastSeen = !empty($ru['LastActive']) ? date('M j, g:i A', strtotime($ru['LastActive'])) : 'Unknown';
                                ?>
                                <div style="display:flex; align-items:center; gap:12px; padding:8px 10px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">
                                    <img src="<?= $ruPic ?>" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #e2e8f0;" onerror="this.src='<?= BASE_URL ?>public/img/default-avatar.svg'">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-weight:600; font-size:13px;"><?= htmlspecialchars($ru['Username']) ?></div>
                                        <div style="font-size:11px; color:#94a3b8;"><?= htmlspecialchars($lastSeen) ?></div>
                                    </div>
                                    <span class="admin-badge <?= admin_badge_class($ru['Role']) ?>"><?= htmlspecialchars($ru['Role']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
            <!-- /Analytics Widgets -->

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
                                <?php if (lc_current_user_role() === 'SuperAdmin'): ?>
                                <th>Delete</th>
                                <?php endif; ?>
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
                                    <?php if (lc_current_user_role() === 'SuperAdmin'): ?>
                                    <td>
                                        <?php if ((string)$user['Role'] !== 'SuperAdmin' && (int)$user['UserID'] !== $currentUserId): ?>
                                        <form method="post" action="<?= htmlspecialchars(BASE_URL . 'app/Controllers/Admin/admin_actions.php') ?>"
                                              id="del-form-<?= (int)$user['UserID'] ?>">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="target_user_id" value="<?= (int)$user['UserID'] ?>">
                                            <?php admin_render_return_fields($search, $roleFilter, $statusFilter, $reportStatus, $reportSearch); ?>
                                            <button type="button"
                                                    onclick="adminConfirmDelete('del-form-<?= (int)$user['UserID'] ?>', '<?= htmlspecialchars((string)$user['Username'], ENT_QUOTES) ?>', '<?= htmlspecialchars((string)($user['Email'] ?? ''), ENT_QUOTES) ?>')"
                                                    style="background:#ef4444;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-weight:600;font-size:13px;cursor:pointer;">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span style="color:#cbd5e1;font-size:12px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
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
                        <?php foreach (['Pending', 'Reviewed', 'Resolved'] as $status): ?>
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
                        <?php
                            $postId       = (int)($report['PostID'] ?? $report['EntityID'] ?? 0);
                            $targetUserId = (int)($report['TargetUserID'] ?? 0);
                            $strikes      = (int)($report['TargetUserStrikes'] ?? 0);
                            $profileUrl   = $targetUserId > 0
                                ? htmlspecialchars(BASE_URL . 'app/Views/Profile/profile.php?user_id=' . $targetUserId)
                                : '#';
                        ?>
                        <article class="admin-report-card">
                            <div class="admin-report-top">
                                <div>
                                    <div class="admin-report-meta">
                                        <span class="admin-badge <?= admin_badge_class((string)$report['Status']) ?>"><?= htmlspecialchars((string)$report['Status']) ?></span>
                                        <?php if ($postId > 0): ?>
                                            <a href="<?= $profileUrl ?>" target="_blank" class="admin-post-link" title="View post author profile">
                                                Post #<?= $postId ?> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px"></i>
                                            </a>
                                        <?php else: ?>
                                            <span>Post #<?= $postId ?></span>
                                        <?php endif; ?>
                                        <span>Reported <?= htmlspecialchars((string)$report['CreatedAt']) ?></span>
                                    </div>
                                    <h3><?= htmlspecialchars((string)$report['Reason']) ?></h3>
                                </div>
                            </div>

                            <div class="admin-report-grid">
                                <div>
                                    <label>Reporter</label>
                                    <p>
                                        <?= htmlspecialchars((string)$report['ReporterName']) ?>
                                        <?php if (!empty($report['ReporterEmail'])): ?>
                                            <br><small style="color:#64748b"><?= htmlspecialchars((string)$report['ReporterEmail']) ?></small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <label>Target User</label>
                                    <p>
                                        <?php if ($targetUserId > 0): ?>
                                            <a href="<?= $profileUrl ?>" target="_blank" style="color:#2563eb;text-decoration:none">
                                                <?= htmlspecialchars((string)($report['TargetUserName'] ?? 'Unknown')) ?>
                                                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px"></i>
                                            </a>
                                        <?php else: ?>
                                            <?= htmlspecialchars((string)($report['TargetUserName'] ?? 'Unknown user')) ?>
                                        <?php endif; ?>
                                        <?php if ($strikes > 0): ?>
                                            <br><?= admin_strike_badge($strikes) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="admin-report-span">
                                    <label>Post Preview
                                        <?php if ($postId > 0 && $targetUserId > 0): ?>
                                            — <a href="<?= $profileUrl ?>" target="_blank" style="color:#2563eb;font-size:12px;font-weight:600">View on Profile <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px"></i></a>
                                        <?php endif; ?>
                                    </label>
                                    <p><?= admin_post_preview($report) ?></p>
                                    <?php if (!empty($report['Details'])): ?>
                                        <p style="margin-top:6px;color:#64748b;font-size:13px"><strong>Reporter note:</strong> <?= htmlspecialchars(admin_short_text((string)$report['Details'], 300)) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label>Reviewed By</label>
                                    <p><?= htmlspecialchars((string)($report['ReviewerName'] ?? 'Not reviewed yet')) ?></p>
                                </div>
                                <div>
                                    <label>Reviewed At</label>
                                    <p><?= htmlspecialchars((string)($report['ReviewedAt'] ?? 'Pending review')) ?></p>
                                </div>
                                <?php if (!empty($report['AdminNote'])): ?>
                                    <div class="admin-report-span" style="background:#f0fdf4;border-left:3px solid #10b981;padding:10px 14px;border-radius:6px">
                                        <label style="color:#059669">Admin Note</label>
                                        <p><?= htmlspecialchars((string)$report['AdminNote']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Admin Note + Status Actions -->
                            <div class="admin-report-actions" style="flex-direction:column;gap:12px">
                                <div style="width:100%">
                                    <label for="note_<?= (int)$report['ReportID'] ?>" style="font-size:13px;font-weight:600;color:#475569;display:block;margin-bottom:4px">
                                        <i class="fa-solid fa-note-sticky"></i> Admin Note (optional — saved with any status change)
                                    </label>
                                    <textarea id="note_<?= (int)$report['ReportID'] ?>"
                                        class="admin-note-input"
                                        data-report-id="<?= (int)$report['ReportID'] ?>"
                                        rows="2"
                                        placeholder="Write a note explaining the decision..."
                                        style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;font-family:inherit;resize:vertical;box-sizing:border-box"><?= htmlspecialchars((string)($report['AdminNote'] ?? '')) ?></textarea>
                                </div>
                                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                                    <?php foreach (['Reviewed', 'Resolved', 'Pending'] as $st): ?>
                                        <form method="post" action="<?= htmlspecialchars(BASE_URL . 'app/Controllers/Admin/admin_actions.php') ?>" class="admin-status-form" data-report-id="<?= (int)$report['ReportID'] ?>">
                                            <input type="hidden" name="action" value="update_report">
                                            <input type="hidden" name="report_id" value="<?= (int)$report['ReportID'] ?>">
                                            <input type="hidden" name="new_report_status" value="<?= htmlspecialchars($st) ?>">
                                            <input type="hidden" name="admin_note" class="admin-note-carrier" value="">
                                            <?php admin_render_return_fields($search, $roleFilter, $statusFilter, $reportStatus, $reportSearch); ?>
                                            <button type="submit" class="admin-btn-<?= strtolower($st) ?>">Mark <?= htmlspecialchars($st) ?></button>
                                        </form>
                                    <?php endforeach; ?>

                                    <?php if ($postId > 0): ?>
                                        <form method="post" action="<?= htmlspecialchars(BASE_URL . 'app/Controllers/Admin/admin_actions.php') ?>"
                                              onsubmit="return confirm('Delete Post #<?= $postId ?>? This cannot be undone.')">
                                            <input type="hidden" name="action" value="delete_post">
                                            <input type="hidden" name="post_id" value="<?= $postId ?>">
                                            <?php admin_render_return_fields($search, $roleFilter, $statusFilter, $reportStatus, $reportSearch); ?>
                                            <button type="submit" style="background:#ef4444!important;color:#fff!important;padding:8px 16px;border:none;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer">
                                                <i class="fa-solid fa-trash"></i> Delete Post
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="admin-delete-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:32px 28px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;animation:modalPop 0.2s ease;">
            <div style="width:64px;height:64px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:28px;color:#ef4444;"></i>
            </div>
            <h3 style="margin:0 0 8px;font-size:20px;color:#0f172a;">Delete Account?</h3>
            <p id="admin-delete-modal-name" style="font-weight:700;font-size:16px;color:#ef4444;margin:0 0 4px;"></p>
            <p id="admin-delete-modal-email" style="font-size:13px;color:#64748b;margin:0 0 16px;"></p>
            <p style="font-size:14px;color:#475569;margin:0 0 28px;line-height:1.6;">
                This will permanently delete this account and all their posts.<br>
                <strong style="color:#ef4444;">This cannot be undone.</strong>
            </p>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button onclick="adminDeleteCancel()" style="flex:1;max-width:150px;padding:11px 20px;border:1.5px solid #cbd5e1;border-radius:10px;background:#fff;color:#334155;font-weight:600;font-size:14px;cursor:pointer;">
                    Cancel
                </button>
                <button id="admin-delete-confirm-btn" style="flex:1;max-width:150px;padding:11px 20px;border:none;border-radius:10px;background:#ef4444;color:#fff;font-weight:700;font-size:14px;cursor:pointer;">
                    <i class="fa-solid fa-trash"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
    <style>
        @keyframes modalPop {
            from { transform:scale(0.92); opacity:0; }
            to   { transform:scale(1);   opacity:1; }
        }
        body.dark-mode #admin-delete-modal > div {
            background: #1e293b;
        }
        body.dark-mode #admin-delete-modal h3 {
            color: #f1f5f9;
        }
        body.dark-mode #admin-delete-modal > div > p:last-of-type {
            color: #94a3b8;
        }
        body.dark-mode #admin-delete-modal button:first-child {
            background: #0f172a;
            color: #e2e8f0;
            border-color: rgba(148,163,184,0.3);
        }
    </style>

    <?php lc_shell_render_overlay(); ?>
    <?php lc_shell_render_user_bootstrap($currentUserId, (string)($currentUser['Username'] ?? 'Admin'), $profilePicPath); ?>
    <script src="<?= htmlspecialchars(lc_public_asset('js/common/theme-toggle.js')) ?>"></script>
    <script src="<?= htmlspecialchars(lc_public_asset('js/Home/responsive.js')) ?>"></script>
    <script>
        // Delete confirmation modal
        let _adminDeleteFormId = null;
        function adminConfirmDelete(formId, username, email) {
            _adminDeleteFormId = formId;
            document.getElementById('admin-delete-modal-name').textContent = username;
            document.getElementById('admin-delete-modal-email').textContent = email;
            const modal = document.getElementById('admin-delete-modal');
            modal.style.display = 'flex';
            document.getElementById('admin-delete-confirm-btn').onclick = function () {
                if (_adminDeleteFormId) {
                    document.getElementById(_adminDeleteFormId).submit();
                }
            };
        }
        function adminDeleteCancel() {
            _adminDeleteFormId = null;
            document.getElementById('admin-delete-modal').style.display = 'none';
        }
        // Close modal when clicking backdrop
        document.getElementById('admin-delete-modal').addEventListener('click', function(e) {
            if (e.target === this) adminDeleteCancel();
        });

        // Copy the admin note textarea value into every status-change form for that report
        document.querySelectorAll('.admin-status-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var reportId = form.dataset.reportId;
                var textarea = document.getElementById('note_' + reportId);
                var carrier  = form.querySelector('.admin-note-carrier');
                if (textarea && carrier) {
                    carrier.value = textarea.value.trim();
                }
            });
        });
    </script>
    
    <script>
        // Load data from PHP into JavaScript
        const exportUsersData = <?= json_encode($users) ?>;
        const exportReportsData = <?= json_encode($reports) ?>;

        function toggleExportMenu(menuId, e) {
            e.preventDefault();
            // Close other menus
            document.querySelectorAll('.export-menu.show-menu').forEach(menu => {
                if(menu.id !== menuId) menu.classList.remove('show-menu');
            });
            document.getElementById(menuId).classList.toggle('show-menu');
        }

        // Close menu when clicking elsewhere
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

            // Get date range values from inputs
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

            // Filter data by date range (if provided)
            let filteredData = rawData.filter(item => {
                let itemDate = new Date(item.CreatedAt.replace(' ', 'T')); // Normalize format for all browsers
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

            // Prepare data based on the selected export type
            if (dataset === 'users') {
                headers = ["ID", "Username", "Email", "Role", "Status", "Joined"];
                rows = filteredData.map(u => [u.UserID, u.Username, u.Email, u.Role, u.AccountStatus, u.CreatedAt]);
                title = "Admin - Users List";
                filename = "admin_users_list";
            } else if (dataset === 'reports') {
                headers = ["ID", "Type", "Status", "Reason", "Reporter", "Target User", "Created At"];
                rows = filteredData.map(r => [r.ReportID, 'Post', r.Status, r.Reason, r.ReporterName, (r.TargetUserName || 'N/A'), r.CreatedAt]);
                title = "Admin - Reports List";
                filename = "admin_reports_list";
            }

            if (type === 'csv') {
                // Wrap cells in double quotes to prevent issues with commas in values
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
                const doc = new jsPDF(dataset === 'reports' ? 'l' : 'p'); // Use Landscape for Reports because they are wide
                doc.text(title, 14, 15);
                doc.setFontSize(10);
                doc.text(dateRangeText, 14, 22); // Show date range
                doc.text("Total Records: " + rows.length, 14, 28); // Show total count
                doc.autoTable({ startY: 32, head: [headers], body: rows }); // Push table down slightly
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
