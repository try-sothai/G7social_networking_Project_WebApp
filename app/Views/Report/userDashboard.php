<?php

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once '../../Models/Friends/FriendModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';

// Security: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "app/Views/Login/login_gmail.php");
    exit();
}

// Admin → redirect to admin analytics page
if (lc_current_user_is_admin()) {
    header("Location: " . BASE_URL . "app/Views/Report/userReport.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$profileModel = new ProfileModel($conn);
$u_d = $profileModel->getUserData($user_id);
$friendModel = new FriendModel($conn);
$unread_notifications = $friendModel->getUnreadNotificationsCount($user_id);

$profilePicPath = getAvatarUrl($u_d['ProfilePic'] ?? null);

// ── Personal Stats ─────────────────────────────────────────────────────────
try {
    // Total posts
    $stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE UserID = ?");
    $stmt->execute([$user_id]);
    $myTotalPosts = (int)$stmt->fetchColumn();

    // Posts this month vs last month (growth %)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE UserID = ? AND MONTH(CreatedAt) = MONTH(CURRENT_DATE()) AND YEAR(CreatedAt) = YEAR(CURRENT_DATE())");
    $stmt->execute([$user_id]);
    $myPostsThisMonth = (int)$stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE UserID = ? AND MONTH(CreatedAt) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(CreatedAt) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))");
    $stmt->execute([$user_id]);
    $myPostsLastMonth = (int)$stmt->fetchColumn();

    $myPostsGrowth = $myPostsLastMonth > 0
        ? round((($myPostsThisMonth - $myPostsLastMonth) / $myPostsLastMonth) * 100)
        : ($myPostsThisMonth > 0 ? 100 : 0);

    // Likes received on my posts
    $stmt = $conn->prepare("SELECT COUNT(*) FROM interactions WHERE Type IN ('like','love','haha') AND PostID IN (SELECT PostID FROM posts WHERE UserID = ?)");
    $stmt->execute([$user_id]);
    $myLikesReceived = (int)$stmt->fetchColumn();

    // Comments received on my posts
    $stmt = $conn->prepare("SELECT COUNT(*) FROM interactions WHERE Type = 'comment' AND PostID IN (SELECT PostID FROM posts WHERE UserID = ?)");
    $stmt->execute([$user_id]);
    $myCommentsReceived = (int)$stmt->fetchColumn();

    // Shares received
    $stmt = $conn->prepare("SELECT COUNT(*) FROM shares WHERE OriginalPostID IN (SELECT PostID FROM posts WHERE UserID = ?)");
    $stmt->execute([$user_id]);
    $mySharesReceived = (int)$stmt->fetchColumn();

    // Friends count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM friends WHERE (RequesterID = ? OR ReceiverID = ?) AND Status = 'Accepted'");
    $stmt->execute([$user_id, $user_id]);
    $myFriendsCount = (int)$stmt->fetchColumn();

    // Engagement trend — single efficient query, fill missing days with 0
    $stmt = $conn->prepare("
        SELECT DATE(i.CreatedAt) AS date,
            SUM(CASE WHEN i.Type IN ('like','love','haha') THEN 1 ELSE 0 END) AS likes,
            SUM(CASE WHEN i.Type = 'comment'              THEN 1 ELSE 0 END) AS comments
        FROM interactions i
        WHERE i.PostID IN (SELECT PostID FROM posts WHERE UserID = ?)
          AND i.CreatedAt >= DATE_SUB(CURRENT_DATE(), INTERVAL 29 DAY)
        GROUP BY DATE(i.CreatedAt)
    ");
    $stmt->execute([$user_id]);
    $trendByDate = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $trendByDate[$row['date']] = ['likes' => (int)$row['likes'], 'comments' => (int)$row['comments']];
    }
    $myEngagementTrend = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $myEngagementTrend[] = [
            'date'     => $date,
            'likes'    => $trendByDate[$date]['likes']    ?? 0,
            'comments' => $trendByDate[$date]['comments'] ?? 0,
        ];
    }

    // My 5 most recent posts with engagement counts
    $stmt = $conn->prepare("
        SELECT p.PostID, p.Content, p.ImagePath, p.CreatedAt,
            COALESCE(SUM(CASE WHEN i.Type IN ('like','love','haha') THEN 1 ELSE 0 END), 0) AS likes,
            COALESCE(SUM(CASE WHEN i.Type = 'comment'              THEN 1 ELSE 0 END), 0) AS comments
        FROM posts p
        LEFT JOIN interactions i ON p.PostID = i.PostID
        WHERE p.UserID = ?
        GROUP BY p.PostID, p.Content, p.ImagePath, p.CreatedAt
        ORDER BY p.CreatedAt DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $myRecentPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $myTotalPosts = $myLikesReceived = $myCommentsReceived = $myFriendsCount = $mySharesReceived = 0;
    $myPostsGrowth = 0;
    $myEngagementTrend = [];
    $myRecentPosts = [];
}

// Profile completeness
$fields = ['Username', 'Bio', 'ProfilePic', 'CoverPic', 'Phone', 'Locations', 'Website'];
$filled = count(array_filter($fields, fn($f) => !empty($u_d[$f]) && $u_d[$f] !== 'default.png' && $u_d[$f] !== 'default_cover.jpg'));
$profileCompleteness = (int)(($filled / count($fields)) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 — My Dashboard</title>
    <?php lc_shell_render_head_assets(); ?>    
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/Report/userReport.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(lc_public_asset('css/Home/dashboard.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ── Recent Posts ─────────────────────────── */
        .post-list { display: flex; flex-direction: column; gap: 12px; }

        .post-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e8edf2;
            transition: box-shadow 0.18s;
        }

        .post-row:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.06); }

        .post-thumb {
            width: 52px; height: 52px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            background: #e2e8f0;
        }

        .post-thumb-placeholder {
            width: 52px; height: 52px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .post-body { flex: 1; min-width: 0; }

        .post-text {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
            font-size: 0.75rem;
            color: #64748b;
        }

        .post-meta span { display: flex; align-items: center; gap: 4px; }

        /* ── Profile Summary ──────────────────────── */
        .profile-summary-card {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 2px 14px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .profile-summary-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .profile-summary-top img {
            width: 60px; height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dbeafe;
        }

        .profile-summary-name { font-weight: 700; font-size: 1rem; color: #1e293b; }
        .profile-summary-sub  { font-size: 0.78rem; color: #64748b; margin-top: 2px; }

        .profile-stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            text-align: center;
        }

        .profile-stat-box {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 10px 6px;
        }

        .profile-stat-box .val { font-weight: 700; font-size: 1.1rem; color: #1e293b; }
        .profile-stat-box .lbl { font-size: 0.7rem; color: #64748b; margin-top: 2px; }

        /* ── Profile Completeness ─────────────────── */
        .completeness-bar-wrap {
            background: #f1f5f9;
            border-radius: 20px;
            height: 8px;
            overflow: hidden;
        }

        .completeness-bar {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #2563eb, #8b5cf6);
            transition: width 0.6s ease;
        }

        .completeness-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            color: #64748b;
            margin-bottom: 6px;
        }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 0.83rem;
            font-weight: 600;
            text-decoration: none;
            color: #2563eb;
            background: #eff6ff;
            border: none;
            cursor: pointer;
            transition: 0.18s;
        }

        .quick-action-btn:hover { background: #dbeafe; }
        .quick-action-btn i { width: 16px; text-align: center; }

        /* ── Dark mode ────────────────────────────── */
        body.dark-mode .post-row         { background: #0f172a; border-color: rgba(148,163,184,0.12); }
        body.dark-mode .post-text        { color: #e2e8f0; }
        body.dark-mode .profile-summary-card { background: #1e293b; }
        body.dark-mode .profile-summary-name { color: #e2e8f0; }
        body.dark-mode .profile-stat-box { background: #0f172a; }
        body.dark-mode .profile-stat-box .val { color: #e2e8f0; }
        body.dark-mode .completeness-bar-wrap { background: #0f172a; }
        body.dark-mode .quick-action-btn { background: rgba(37,99,235,0.12); color: #60a5fa; }
        body.dark-mode .quick-action-btn:hover { background: rgba(37,99,235,0.2); }

        @media (max-width: 1024px) {
            .two-columns { display: flex !important; flex-direction: column; }
            .stats-grid  { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: 1fr !important; }
            .profile-stats-row { grid-template-columns: repeat(3, 1fr); }
        }

        /* ── Date Range Picker ──────────────────────────────────────── */
        .drp-wrap { position: relative; }
        .drp-btn {
            display: flex; align-items: center; gap: 7px;
            padding: 8px 14px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            font-size: 0.83rem; font-weight: 600; color: #334155;
            cursor: pointer; transition: all 0.18s; white-space: nowrap;
            font-family: inherit;
        }
        .drp-btn:hover { background: #f1f5f9; border-color: #94a3b8; }
        .drp-btn .fa-calendar { color: #2563eb; }
        .drp-menu {
            display: none; position: absolute; right: 0; top: calc(100% + 6px);
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12); min-width: 210px; z-index: 60;
            overflow: hidden;
        }
        .drp-menu.open { display: block; animation: drpFade 0.18s ease; }
        @keyframes drpFade {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .drp-opt {
            padding: 10px 16px; font-size: 0.85rem; color: #334155;
            cursor: pointer; transition: background 0.15s;
        }
        .drp-opt:hover  { background: #f1f5f9; color: #0f172a; }
        .drp-opt.active { color: #2563eb; font-weight: 600; background: #eff6ff; }
        .drp-custom-box {
            padding: 10px 14px; border-top: 1px solid #e2e8f0;
            display: flex; flex-direction: column; gap: 8px;
        }
        .drp-custom-box label { font-size: 12px; font-weight: 600; color: #64748b; }
        .drp-custom-box input[type="date"] {
            display: block; width: 100%; margin-top: 4px;
            padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 6px;
            font-size: 13px; font-family: inherit;
        }
        .drp-apply {
            background: #2563eb; color: #fff; border: none; border-radius: 6px;
            padding: 7px 14px; font-size: 13px; font-weight: 600;
            cursor: pointer; margin-top: 2px; font-family: inherit;
        }
        .drp-apply:hover { background: #1d4ed8; }
        body.dark-mode .drp-btn { background: #1e293b; border-color: rgba(148,163,184,0.2); color: #e2e8f0; }
        body.dark-mode .drp-btn:hover { background: #0f172a; }
        body.dark-mode .drp-menu { background: #1e293b; border-color: rgba(148,163,184,0.2); }
        body.dark-mode .drp-opt { color: #e2e8f0; }
        body.dark-mode .drp-opt:hover { background: #0f172a; }
        body.dark-mode .drp-opt.active { background: rgba(37,99,235,0.2); color: #60a5fa; }
        body.dark-mode .drp-custom-box { border-color: rgba(148,163,184,0.2); }
        body.dark-mode .drp-custom-box input[type="date"] {
            background: #0f172a; color: #e2e8f0;
            border-color: rgba(148,163,184,0.3); color-scheme: dark;
        }
    </style>
</head>
<body>
    <?php lc_shell_render_sidebar(['active' => 'report']); ?>

    <div class="main-content">
        <?php
            ob_start();
        ?>
            <i class="fa-solid fa-bell"></i>
            <?php if ($unread_notifications > 0): ?>
                <span class="notification-badge"><?= $unread_notifications > 99 ? '99+' : $unread_notifications ?></span>
            <?php endif; ?>
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="notification-header" style="padding:12px 16px;font-weight:600;border-bottom:1px solid #e5e7eb;text-align:left;">Notifications</div>
                <div id="notificationsList" class="notification-list"></div>
            </div>
        <?php
            $notifHtml = ob_get_clean();
            lc_shell_render_header([
                'search_input_id'       => 'dashSearch',
                'search_placeholder'    => 'Search my posts...',
                'notification_html'     => $notifHtml,
                'notification_button_id'=> 'notificationBtn',
                'user_name'             => (string)($u_d['Username'] ?? 'User'),
                'profile_image'         => $profilePicPath,
            ]);
        ?>

        <div class="container">
            <!-- ── Header ── -->
            <div class="report-header">
                <div>
                    <h1>My Dashboard</h1>
                    <p style="color:#64748b; margin-top:4px; font-size:0.9rem;">
                        Welcome back, <strong><?= htmlspecialchars($u_d['Username'] ?? 'User') ?></strong>! Here's your activity overview.
                    </p>
                </div>
                <div class="drp-wrap">
                    <button class="drp-btn" id="myDrpBtn" onclick="myDrpToggle(event)">
                        <i class="fa-regular fa-calendar"></i>
                        <span id="myDrpLabel">Last 30 Days</span>
                        <i class="fa-solid fa-chevron-down" style="font-size:0.7rem;"></i>
                    </button>
                    <div class="drp-menu" id="myDrpMenu">
                        <div class="drp-opt" onclick="mySetPreset(7,this)">Last 7 Days</div>
                        <div class="drp-opt active" onclick="mySetPreset(30,this)">Last 30 Days</div>
                        <div class="drp-opt" onclick="mySetPreset(90,this)">Last 3 Months</div>
                        <div class="drp-opt" onclick="mySetPreset(180,this)">Last 6 Months</div>
                        <div class="drp-opt" onclick="myShowCustom()">Custom Range...</div>
                        <div class="drp-custom-box" id="myDrpCustom" style="display:none;">
                            <label>From
                                <input type="date" id="myDrpFrom">
                            </label>
                            <label>To
                                <input type="date" id="myDrpTo">
                            </label>
                            <button class="drp-apply" onclick="myApplyCustom()">Apply</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Stats Cards ── -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($myTotalPosts) ?></h3>
                        <p>My Posts</p>
                        <span class="trend <?= $myPostsGrowth >= 0 ? 'positive' : 'negative' ?>">
                            <i class="fa-solid fa-arrow-<?= $myPostsGrowth >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs($myPostsGrowth) ?>% this month
                        </span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($myLikesReceived) ?></h3>
                        <p>Likes Received</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon teal">
                        <i class="fa-solid fa-comment"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($myCommentsReceived) ?></h3>
                        <p>Comments Received</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon indigo">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($myFriendsCount) ?></h3>
                        <p>Friends</p>
                    </div>
                </div>
            </div>

            <!-- ── Engagement Chart ── -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3>My Engagement Trends</h3>
                    <p id="myChartSubtitle">Likes &amp; Comments on my posts (Last 30 Days)</p>
                </div>
                <canvas id="myEngagementChart"></canvas>
            </div>

            <!-- ── Two Column ── -->
            <div class="two-columns">
                <!-- My Recent Posts -->
                <div class="activity-feed">
                    <div class="section-header">
                        <h3>My Recent Posts</h3>
                        <a href="<?= BASE_URL ?>app/Views/Profile/profile.php" class="view-all">VIEW ALL</a>
                    </div>

                    <?php if (empty($myRecentPosts)): ?>
                        <div class="no-activities">
                            <i class="fa-regular fa-newspaper"></i>
                            <p>You haven't posted anything yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="post-list">
                            <?php foreach ($myRecentPosts as $post): ?>
                                <?php
                                    $rawImg = $post['ImagePath'] ?? '';
                                    if ($rawImg && str_contains($rawImg, '[')) {
                                        $decoded = json_decode($rawImg, true);
                                        $rawImg = is_array($decoded) ? ($decoded[0] ?? '') : $rawImg;
                                    }
                                    $postContent = trim(str_starts_with($post['Content'] ?? '', '[SHARED_POST]')
                                        ? 'Shared a post'
                                        : ($post['Content'] ?? ''));
                                ?>
                                <div class="post-row">
                                    <?php if ($rawImg): ?>
                                        <img class="post-thumb"
                                             src="<?= htmlspecialchars(getImageUrl($rawImg)) ?>"
                                             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';"
                                             alt="">
                                        <div class="post-thumb-placeholder" style="display:none;">
                                            <i class="fa-solid fa-image"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="post-thumb-placeholder">
                                            <i class="fa-solid fa-pen-nib"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="post-body">
                                        <div class="post-text">
                                            <?= htmlspecialchars($postContent ?: 'No content') ?>
                                        </div>
                                        <div class="post-meta">
                                            <span><i class="fa-solid fa-heart" style="color:#e63946;"></i> <?= (int)$post['likes'] ?></span>
                                            <span><i class="fa-solid fa-comment" style="color:#2a9d8f;"></i> <?= (int)$post['comments'] ?></span>
                                            <span><i class="fa-regular fa-clock"></i> <?= time_ago($post['CreatedAt']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Profile Summary -->
                <div class="profile-summary-card">
                    <div class="profile-summary-top">
                        <img src="<?= htmlspecialchars($profilePicPath) ?>"
                             alt="<?= htmlspecialchars($u_d['Username'] ?? '') ?>"
                             onerror="this.onerror=null;this.src='../../../public/img/default-avatar.svg'">
                        <div>
                            <div class="profile-summary-name"><?= htmlspecialchars($u_d['Username'] ?? 'User') ?></div>
                            <div class="profile-summary-sub"><?= htmlspecialchars($u_d['Bio'] ?? 'No bio yet') ?></div>
                        </div>
                    </div>

                    <div class="profile-stats-row">
                        <div class="profile-stat-box">
                            <div class="val"><?= number_format($myTotalPosts) ?></div>
                            <div class="lbl">Posts</div>
                        </div>
                        <div class="profile-stat-box">
                            <div class="val"><?= number_format($myFriendsCount) ?></div>
                            <div class="lbl">Friends</div>
                        </div>
                        <div class="profile-stat-box">
                            <div class="val"><?= number_format($mySharesReceived) ?></div>
                            <div class="lbl">Shares</div>
                        </div>
                    </div>

                    <!-- Profile Completeness -->
                    <div>
                        <div class="completeness-label">
                            <span><i class="fa-solid fa-circle-check" style="color:#10b981;"></i> Profile Completeness</span>
                            <strong><?= $profileCompleteness ?>%</strong>
                        </div>
                        <div class="completeness-bar-wrap">
                            <div class="completeness-bar" style="width:<?= $profileCompleteness ?>%;"></div>
                        </div>
                        <?php if ($profileCompleteness < 100): ?>
                            <p style="font-size:0.75rem;color:#94a3b8;margin-top:6px;">
                                Complete your profile to get more visibility.
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="<?= BASE_URL ?>app/Views/Profile/profile.php" class="quick-action-btn">
                            <i class="fa-solid fa-user"></i> View My Profile
                        </a>
                        <a href="<?= BASE_URL ?>app/Views/Profile/editProfile.php" class="quick-action-btn">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Profile
                        </a>
                        <a href="<?= BASE_URL ?>app/Views/Friends/AllFriends.php" class="quick-action-btn">
                            <i class="fa-solid fa-user-group"></i> My Friends
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>

    <script>
        // ── Engagement Chart ──────────────────────────────────────────────────
        const _initialTrend = <?= json_encode($myEngagementTrend) ?>;

        const MONTHS = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
        const PRESET_LABELS = {7:'Last 7 Days', 30:'Last 30 Days', 90:'Last 3 Months', 180:'Last 6 Months'};

        function fmtDate(d) { return d.toISOString().slice(0, 10); }
        function chartLabel(dateStr) {
            const dt = new Date(dateStr + 'T00:00:00');
            return MONTHS[dt.getMonth()] + ' ' + dt.getDate();
        }

        let myChart = null;

        function renderChart(data) {
            const labels   = data.map(d => chartLabel(d.date));
            const likes    = data.map(d => d.likes);
            const comments = data.map(d => d.comments);

            if (myChart) {
                myChart.data.labels              = labels;
                myChart.data.datasets[0].data    = likes;
                myChart.data.datasets[1].data    = comments;
                myChart.update();
                return;
            }

            myChart = new Chart(document.getElementById('myEngagementChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Likes',
                            data: likes,
                            borderColor: '#4361ee',
                            backgroundColor: 'rgba(67,97,238,0.1)',
                            tension: 0.4, fill: true,
                            pointBackgroundColor: '#4361ee', pointBorderColor: '#fff',
                            pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                        },
                        {
                            label: 'Comments',
                            data: comments,
                            borderColor: '#f72585',
                            backgroundColor: 'rgba(247,37,133,0.1)',
                            tension: 0.4, fill: true,
                            pointBackgroundColor: '#f72585', pointBorderColor: '#fff',
                            pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10 } },
                        tooltip: {
                            mode: 'index', intersect: false,
                            backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#cbd5e1'
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { precision: 0 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        renderChart(_initialTrend);

        async function myLoadChartData(from, to) {
            const btn = document.getElementById('myDrpBtn');
            btn.disabled = true;
            try {
                const res  = await fetch(`<?= BASE_URL ?>app/Views/Report/userDashboard_ajax.php?from=${from}&to=${to}`);
                if (!res.ok) throw new Error('Failed');
                renderChart(await res.json());
            } catch (e) {
                console.error('Chart load error', e);
            } finally {
                btn.disabled = false;
            }
        }

        // ── Date Range Picker ──────────────────────────────────────────────────
        function myDrpToggle(e) {
            e.stopPropagation();
            document.getElementById('myDrpMenu').classList.toggle('open');
        }

        function mySetPreset(days, el) {
            const to   = new Date();
            const from = new Date();
            from.setDate(to.getDate() - (days - 1));

            const label = PRESET_LABELS[days] || `Last ${days} Days`;
            document.getElementById('myDrpLabel').textContent      = label;
            document.getElementById('myChartSubtitle').textContent = `Likes & Comments on my posts (${label})`;
            document.getElementById('myDrpMenu').classList.remove('open');
            document.querySelectorAll('#myDrpMenu .drp-opt').forEach(o => o.classList.remove('active'));
            el.classList.add('active');

            myLoadChartData(fmtDate(from), fmtDate(to));
        }

        function myShowCustom() {
            const box = document.getElementById('myDrpCustom');
            box.style.display = box.style.display === 'none' ? 'flex' : 'none';

            const today = new Date();
            const past  = new Date();
            past.setDate(today.getDate() - 29);
            const maxStr = fmtDate(today);

            const fromEl = document.getElementById('myDrpFrom');
            const toEl   = document.getElementById('myDrpTo');
            if (!fromEl.value) fromEl.value = fmtDate(past);
            if (!toEl.value)   toEl.value   = maxStr;
            fromEl.max = maxStr;
            toEl.max   = maxStr;
        }

        function myApplyCustom() {
            const from = document.getElementById('myDrpFrom').value;
            const to   = document.getElementById('myDrpTo').value;
            if (!from || !to) return;
            if (from > to) { alert('Start date must be before end date.'); return; }

            const fmt = str => new Date(str + 'T00:00:00').toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
            const label = `${fmt(from)} – ${fmt(to)}`;
            document.getElementById('myDrpLabel').textContent      = label;
            document.getElementById('myChartSubtitle').textContent = `Likes & Comments on my posts (${label})`;
            document.getElementById('myDrpMenu').classList.remove('open');
            document.querySelectorAll('#myDrpMenu .drp-opt').forEach(o => o.classList.remove('active'));

            myLoadChartData(from, to);
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.drp-wrap')) {
                document.getElementById('myDrpMenu').classList.remove('open');
            }
        });

        // ── Search: filter recent posts ────────────────────────────────────────
        const searchInput = document.getElementById('dashSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.toLowerCase().trim();
                document.querySelectorAll('.post-row').forEach(row => {
                    const text = row.querySelector('.post-text')?.textContent?.toLowerCase() ?? '';
                    row.style.display = (!term || text.includes(term)) ? '' : 'none';
                });
            });
        }
    </script>
    <script src="<?= BASE_URL ?>public/js/common/theme-toggle.js"></script>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="<?= htmlspecialchars(lc_public_asset('js/Home/dashboard.js')) ?>"></script>
    <script src="<?= htmlspecialchars(lc_public_asset('js/Home/responsive.js')) ?>"></script>
</body>
</html>
