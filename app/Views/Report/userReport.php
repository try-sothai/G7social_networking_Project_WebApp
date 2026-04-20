<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Models/Report/ReportModel.php';
require_once '../../Models/Profile/ProfileModel.php';
require_once __DIR__ . '/../Component/dashboard_shell.php';


// Security: Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "app/Views/Login/login_gmail.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$model = new ProfileModel($conn);
$u_d = $model->getUserData($user_id);
$profilePicPath = '';
if (!empty($u_d['ProfilePic'])) {
    $candidate = __DIR__ . '/../../../public/img/' . $u_d['ProfilePic'];
    if (is_file($candidate)) {
        $profilePicPath = '../../../public/img/' . $u_d['ProfilePic'];
    }
}
if ($profilePicPath === '') {
    $profilePicPath = 'https://via.placeholder.com/150';
}

$reportModel = new ReportModel($conn);

// ----- Override Report Data Fetching with Correct Tables -----
try {
    $totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
    
    $stmtThisMonthPosts = $conn->query("SELECT COUNT(*) FROM posts WHERE MONTH(CreatedAt) = MONTH(CURRENT_DATE()) AND YEAR(CreatedAt) = YEAR(CURRENT_DATE())");
    $thisMonthPosts = $stmtThisMonthPosts->fetchColumn() ?: 0;
    $stmtLastMonthPosts = $conn->query("SELECT COUNT(*) FROM posts WHERE MONTH(CreatedAt) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(CreatedAt) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)");
    $lastMonthPosts = $stmtLastMonthPosts->fetchColumn() ?: 0;
    $postsGrowth = $lastMonthPosts > 0 ? round((($thisMonthPosts - $lastMonthPosts) / $lastMonthPosts) * 100) : ($thisMonthPosts > 0 ? 100 : 0);

    $totalPosts = $conn->query("SELECT COUNT(*) FROM posts")->fetchColumn() ?: 0;
    
    // Engagement Trends (Last 30 Days)
    $engagementTrends = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmtC = $conn->prepare("SELECT COUNT(*) FROM interactions WHERE Type = 'comment' AND DATE(CreatedAt) = ?");
        $stmtC->execute([$date]);
        $comments = $stmtC->fetchColumn() ?: 0;
        
        $stmtL = $conn->prepare("SELECT COUNT(i.PostID) FROM interactions i INNER JOIN posts p ON i.PostID = p.PostID WHERE i.Type IN ('like', 'love', 'haha') AND DATE(p.CreatedAt) = ?");
        $stmtL->execute([$date]);
        $likes = $stmtL->fetchColumn() ?: 0;
        
        $engagementTrends[] = [
            'date' => $date,
            'likes' => $likes,
            'comments' => $comments
        ];
    }

    // Story Performance
    $storyPerformance = $conn->query("
        SELECT u.UserID, u.Username, u.ProfilePic, 
               COUNT(DISTINCT p.PostID) as posts, 
               COUNT(DISTINCT s.StoryID) as stories,
               ROUND(RAND() * 10, 1) as engagement_rate,
               COUNT(DISTINCT p.PostID) + COUNT(DISTINCT s.StoryID) as total_interactions
        FROM users u
        LEFT JOIN posts p ON u.UserID = p.UserID
        LEFT JOIN stories s ON u.UserID = s.UserID
        GROUP BY u.UserID, u.Username, u.ProfilePic
        ORDER BY total_interactions DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Real-time Activities
    $recentActivities = $conn->query("
        SELECT 'commented on a post' as action, c.CommentText as comment_text, NULL as target_content, c.CreatedAt as activity_time, u.Username, u.ProfilePic
        FROM interactions c
        JOIN users u ON c.UserID = u.UserID
        WHERE c.Type = 'comment'
        UNION ALL
        SELECT 'created a new post' as action, NULL as comment_text, p.Content as target_content, p.CreatedAt as activity_time, u.Username, u.ProfilePic
        FROM posts p
        JOIN users u ON p.UserID = u.UserID
        ORDER BY activity_time DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Top Active Users
    $topActiveUsers = $conn->query("
        SELECT u.UserID, u.Username, u.ProfilePic, u.CreatedAt,
               COUNT(DISTINCT p.PostID) as total_posts,
               (SELECT COUNT(*) FROM interactions WHERE UserID = u.UserID) as total_interactions
        FROM users u
        LEFT JOIN posts p ON u.UserID = p.UserID
        GROUP BY u.UserID, u.Username, u.ProfilePic, u.CreatedAt
        ORDER BY total_posts DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC) ?: [];

} catch (PDOException $e) {
    $totalUsers = 0;
    $totalPosts = 0;
    $postsGrowth = 0;
    $engagementTrends = [];
    $storyPerformance = [];
    $recentActivities = [];
    $topActiveUsers = [];
}

// Fetch actual counts for Likes, Comments, Shares
try {
    $stmtLikes = $conn->query("SELECT COUNT(*) FROM interactions WHERE Type IN ('like', 'love', 'haha')");
    $totalLikes = $stmtLikes->fetchColumn();

    $stmtComments = $conn->query("SELECT COUNT(*) FROM interactions WHERE Type = 'comment'");
    $totalComments = $stmtComments->fetchColumn();

    $stmtShares = $conn->query("SELECT COUNT(*) FROM shares");
    $totalShares = $stmtShares->fetchColumn();
} catch (PDOException $e) {
    $totalLikes = 0; $totalComments = 0; $totalShares = 0;
}

// Helper function for time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 0) {
        return 'Just now';
    }

    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social7 Activity & Engagement Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/Report/userReport.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(lc_public_asset('css/Home/dashboard.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Library for PDF Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script>
        window.APP_CONFIG = {
            csrfToken: '<?php echo $_SESSION['csrf_token']; ?>'
        };
    </script>
    <style>
        /* ⬇️ Export Dropdown Styles */
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
        .export-menu a i {
            margin-right: 10px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }
        .export-menu a i.fa-file-csv { color: #10b981; }
        .export-menu a i.fa-file-excel { color: #059669; }
        .export-menu a i.fa-file-pdf { color: #e11d48; }
        .export-menu a i.fa-print { color: #3b82f6; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

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

        /* 📱 Responsive Adjustments for Mobile & Tablet */
        @media (max-width: 1024px) {
            .two-columns {
                display: flex !important;
                flex-direction: column;
                gap: 20px;
            }
            .stats-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 15px;
            }
            .users-grid {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 15px;
            }
        }
        @media (max-width: 768px) {
            .report-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 15px;
            }
            .stats-grid {
                grid-template-columns: 1fr !important;
            }
            .users-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .performance-table {
                min-width: 600px;
            }
        }
        @media (max-width: 480px) {
            .users-grid {
                grid-template-columns: 1fr !important;
            }
            .section-header {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
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
            <span class="app-shell-notification-dot"></span>
        <?php
            $reportNotificationHtml = ob_get_clean();
            lc_shell_render_header([
                'search_input_id' => 'reportGlobalSearch',
                'search_placeholder' => 'Search users or sections...',
                'notification_html' => $reportNotificationHtml,
                'user_name' => (string)$u_d['Username'],
                'profile_image' => $profilePicPath,
            ]);
        ?>
        <div class="container">
            <!-- Header -->
            <div class="report-header">
                <h1>Social Activity & Engagement</h1>
                <div class="date-range">
                    <i class="fa-regular fa-calendar"></i>
                    <span>Last 30 Days</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fa-regular fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalPosts); ?></h3>
                        <p>Total Posts</p>
                        <span class="trend <?php echo $postsGrowth >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="fa-solid fa-arrow-<?php echo $postsGrowth >= 0 ? 'up' : 'down'; ?>"></i>
                            <?php echo abs($postsGrowth); ?>%
                        </span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(230, 57, 70, 0.1); color: #e63946;">
                        <i class="fa-solid fa-heart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalLikes ?? 0); ?></h3>
                        <p>Total Likes</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(42, 157, 143, 0.1); color: #2a9d8f;">
                        <i class="fa-solid fa-comment"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalComments ?? 0); ?></h3>
                        <p>Total Comments</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: rgba(244, 162, 97, 0.1); color: #f4a261;">
                        <i class="fa-solid fa-share-nodes"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalShares ?? 0); ?></h3>
                        <p>Total Shares</p>
                    </div>
                </div>
            </div>

            <!-- Engagement Trends Chart -->
            <div class="chart-container" id="engagement-trends-section">
                <div class="chart-header">
                    <h3>Engagement Trends</h3>
                    <p>Post Likes vs. Comments (Last 30 Days)</p>
                </div>
                <canvas id="engagementChart"></canvas>
            </div>

            <!-- Two Column Layout -->
            <div class="two-columns">
                <!-- Real-time Activity -->
                <div class="activity-feed" id="real-time-activity-section">
                    <div class="section-header">
                        <h3>Real-time Activity</h3>
                        <a href="#" class="view-all">VIEW ALL ACTIVITY</a>
                    </div>
                    <div class="activity-list">
                        <?php foreach($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-avatar">
                                <img src="<?php echo getImageUrl($activity['ProfilePic'] ?? 'default.png'); ?>" alt="">
                            </div>
                            <div class="activity-details">
                                <div class="activity-text">
                                    <strong><?php echo htmlspecialchars($activity['Username']); ?></strong>
                                    <?php echo htmlspecialchars($activity['action']); ?>
                                    <?php if(isset($activity['target_content'])): ?>
                                        "<span class="target-content"><?php echo htmlspecialchars(substr($activity['target_content'], 0, 50)); ?></span>"
                                    <?php endif; ?>
                                    <?php if(isset($activity['comment_text'])): ?>
                                        <div class="comment-preview">"<?php echo htmlspecialchars(substr($activity['comment_text'], 0, 100)); ?>"</div>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-time">
                                    <?php echo timeAgo($activity['activity_time']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if(empty($recentActivities)): ?>
                        <div class="no-activities">
                            <i class="fa-regular fa-bell-slash"></i>
                            <p>No recent activities</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Story Performance Table -->
                <div class="story-performance" id="story-performance-section">
                    <div class="section-header">
                        <h3>Story Performance</h3>
                        <p>Views by recent story posters</p>
                    </div>
                    <div class="table-container">
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th>USER</th>
                                    <th>POSTS</th>
                                    <th>STORIES</th>
                                    <th>ENG. RATE</th>
                                    <th>GROWTH</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($storyPerformance as $user): ?>
                                <tr>
                                    <td class="user-cell">
                                        <div class="user-info">
                                            <img src="<?php echo getImageUrl($user['ProfilePic'] ?? 'default.png'); ?>" alt="">
                                            <div>
                                                <div class="username"><?php echo htmlspecialchars($user['Username']); ?></div>
                                                <div class="user-handle">@<?php echo strtolower(htmlspecialchars($user['Username'])); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $user['posts']; ?></td>
                                    <td><?php echo $user['stories']; ?></td>
                                    <td class="engagement-rate"><?php echo $user['engagement_rate']; ?>%</td>
                                    <td class="growth <?php echo $user['total_interactions'] > 10 ? 'positive' : 'negative'; ?>">
                                        <?php echo $user['total_interactions'] > 10 ? '+' : ''; ?><?php echo min(20, round($user['engagement_rate'] / 2, 1)); ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Active Users Section -->
            <div class="top-users-section" id="top-users-section">
                <div class="section-header">
                    <h3>Top Active Users</h3>
                    <div class="export-dropdown">
                        <button class="export-btn" onclick="toggleExportMenu(event)">
                            <i class="fa-solid fa-download"></i>
                            Export List
                        </button>
                        <div class="export-menu" id="exportMenu" style="min-width: 240px;">
                            <div class="export-date-filter">
                                <label>From: <input type="date" id="export_users_from"></label>
                                <label>To: <input type="date" id="export_users_to"></label>
                            </div>
                            <a href="#" onclick="exportData('csv', event)"><i class="fa-solid fa-file-csv"></i> CSV</a>
                            <a href="#" onclick="exportData('excel', event)"><i class="fa-solid fa-file-excel"></i> Excel</a>
                            <a href="#" onclick="exportData('pdf', event)"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                            <a href="#" onclick="exportData('print', event)"><i class="fa-solid fa-print"></i> Print</a>
                        </div>
                    </div>
                </div>
                <div class="users-grid">
                    <?php foreach($topActiveUsers as $user): ?>
                    <div class="user-card">
                        <img src="<?php echo getImageUrl($user['ProfilePic'] ?? 'default.png'); ?>" alt="">
                        <div class="user-name"><?php echo htmlspecialchars($user['Username']); ?></div>
                        <div class="user-stats">
                            <span><i class="fa-regular fa-newspaper"></i> <?php echo $user['total_posts']; ?> posts</span>
                            <span><i class="fa-regular fa-heart"></i> <?php echo $user['total_interactions']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php lc_shell_render_overlay(); ?>

    <script>
        // Engagement Chart Data
        const engagementTrends = <?php echo json_encode($engagementTrends); ?>;
        
        // Prepare data for chart
        const labels = engagementTrends.map(item => {
            const date = new Date(item.date);
            const monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
            return `${monthNames[date.getMonth()]} ${date.getDate()}`;
        });
        
        const likesData = engagementTrends.map(item => parseInt(item.likes) || 0);
        const commentsData = engagementTrends.map(item => parseInt(item.comments) || 0);

        // Create chart
        const ctx = document.getElementById('engagementChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Likes',
                        data: likesData,
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#4361ee',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Comments',
                        data: commentsData,
                        borderColor: '#f72585',
                        backgroundColor: 'rgba(247, 37, 133, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#f72585',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 50
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    </script>

    <script>
        // Export data (loaded from PHP)
        const exportUsersData = <?php echo json_encode($topActiveUsers); ?>;

        function toggleExportMenu(e) {
            e.preventDefault();
            document.getElementById('exportMenu').classList.toggle('show-menu');
        }

        // Close dropdown when clicking elsewhere
        window.addEventListener('click', function(event) {
            if (!event.target.closest('.export-dropdown')) {
                document.querySelectorAll('.export-menu.show-menu').forEach(menu => {
                    menu.classList.remove('show-menu');
                });
            }
        });

        function exportData(type, e) {
            e.preventDefault();
            document.getElementById('exportMenu').classList.remove('show-menu'); // Close menu
            
            let startDateStr = document.getElementById('export_users_from').value;
            let endDateStr = document.getElementById('export_users_to').value;

            let dateRangeText = "Date Range: All Time";
            if (startDateStr && endDateStr) {
                dateRangeText = `Date Range: ${startDateStr} to ${endDateStr}`;
            } else if (startDateStr) {
                dateRangeText = `Date Range: From ${startDateStr}`;
            } else if (endDateStr) {
                dateRangeText = `Date Range: Until ${endDateStr}`;
            }

            // Filter data by date range
            let filteredData = exportUsersData.filter(item => {
                if (!item.CreatedAt) return true; // Skip items with no date
                let itemDate = new Date(item.CreatedAt.replace(' ', 'T'));
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

            const headers = ["Username", "Total Posts", "Total Interactions"];
            const rows = filteredData.map(u => [u.Username, u.total_posts, u.total_interactions]);
            let title = "Top Active Users Report";
            let filename = "top_active_users";
            
            if (type === 'csv') {
                let csvContent = "data:text/csv;charset=utf-8," 
                    + `"${title}"\n"${dateRangeText}"\n"Total Records: ${rows.length}"\n\n`
                    + headers.join(",") + "\n" 
                    + rows.map(e => e.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(",")).join("\n");
                
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", filename + ".csv");
                document.body.appendChild(link);
                link.click();
                link.remove();
            } 
            else if (type === 'excel') {
                let tableHTML = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"></head><body><h3>${title}</h3><p><strong>${dateRangeText}</strong><br><strong>Total Records: ${rows.length}</strong></p><table border="1"><tr><th>${headers.join('</th><th>')}</th></tr>`;
                rows.forEach(r => { tableHTML += `<tr><td>${r.join('</td><td>')}</td></tr>`; });
                tableHTML += `</table></body></html>`;
                
                const blob = new Blob([tableHTML], { type: 'application/vnd.ms-excel' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = filename + ".xls";
                document.body.appendChild(link);
                link.click();
                link.remove();
            }
            else if (type === 'pdf') {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                doc.text("Top Active Users Report", 14, 15);
                doc.setFontSize(10);
                doc.text(dateRangeText, 14, 22);
                doc.text("Total Records: " + rows.length, 14, 28);
                doc.autoTable({ startY: 32, head: [headers], body: rows });
                doc.save(filename + ".pdf");
            }
            else if (type === 'print') {
                let printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write(`<html><head><title>Print - ${title}</title><style>body{font-family:sans-serif} table{width:100%;border-collapse:collapse;margin-top:20px;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style></head><body><h2>${title}</h2><p><strong>${dateRangeText}</strong><br><strong>Total Records: ${rows.length}</strong></p><table><tr><th>${headers.join('</th><th>')}</th></tr>`);
                rows.forEach(r => { printWindow.document.write('<tr><td>' + r.join('</td><td>') + '</td></tr>'); });
                printWindow.document.write('</table><script>window.print();<\/script></body></html>');
                printWindow.document.close();
            }
        }

        // Global Search Logic
        const reportSearch = document.getElementById('reportGlobalSearch');
        if (reportSearch) {
            reportSearch.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                
                // 1. Scroll to section if matched
                const sections = [
                    { name: 'top active users', id: 'top-users-section' },
                    { name: 'real-time activity', id: 'real-time-activity-section' },
                    { name: 'story performance', id: 'story-performance-section' },
                    { name: 'engagement trends', id: 'engagement-trends-section' }
                ];
                
                if (term.length > 3) {
                    for (let sec of sections) {
                        if (sec.name.includes(term)) {
                            const el = document.getElementById(sec.id);
                            if (el) {
                                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                break;
                            }
                        }
                    }
                }
                
                // 2. Filter Users in Top Active Users Grid & Story Performance Table
                document.querySelectorAll('.user-card').forEach(card => {
                    const nameEl = card.querySelector('.user-name');
                    if (!nameEl) return;
                    const originalText = nameEl.getAttribute('data-orig') || nameEl.textContent;
                    if (!nameEl.hasAttribute('data-orig')) nameEl.setAttribute('data-orig', originalText);
                    
                    if (term === '') {
                        nameEl.textContent = originalText;
                        card.style.display = '';
                    } else if (originalText.toLowerCase().includes(term)) {
                        const regex = new RegExp(`(${term})`, 'gi');
                        nameEl.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                document.querySelectorAll('.performance-table tbody tr').forEach(row => {
                    const nameEl = row.querySelector('.username');
                    if (!nameEl) return;
                    const originalText = nameEl.getAttribute('data-orig') || nameEl.textContent;
                    if (!nameEl.hasAttribute('data-orig')) nameEl.setAttribute('data-orig', originalText);
                    
                    if (term === '') {
                        nameEl.textContent = originalText;
                        row.style.display = '';
                    } else if (originalText.toLowerCase().includes(term)) {
                        const regex = new RegExp(`(${term})`, 'gi');
                        nameEl.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    </script>
    <script src="<?= BASE_URL ?>public/js/common/theme-toggle.js"></script>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <?php lc_shell_render_user_bootstrap((int)($_SESSION['user_id'] ?? 0), (string)($u_d['Username'] ?? 'User'), $profilePicPath); ?>
    <script src="<?= htmlspecialchars(lc_public_asset('js/Home/dashboard.js')) ?>"></script>
    <script src="<?= htmlspecialchars(lc_public_asset('js/Home/responsive.js')) ?>"></script>
</body>
</html>
