<?php
session_start();

require_once '../../../config/db_config.php';
require_once '../../Helpers/functions.php';
require_once '../../Models/Report/ReportModel.php';
require_once '../../Models/Profile/ProfileModel.php';


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

// Fetch user stats
$totalUsers = $reportModel->getTotalUsers();
$netGrowth = $reportModel->getNewUsersThisMonth();
$userGrowthData = $reportModel->getMonthlyUserGrowth(12);

// Fetch social activity stats
$totalPosts = $reportModel->getTotalPosts();
$postsGrowth = $reportModel->getPostsGrowth();
$totalStoryViews = $reportModel->getTotalStoryViews();
$avgEngagementRate = $reportModel->getAvgEngagementRate();
$activeStories = $reportModel->getActiveStories();

// Fetch engagement trends
$engagementTrends = $reportModel->getEngagementTrends();

// Fetch story performance
$storyPerformance = $reportModel->getStoryPerformance();

// Fetch real-time activities
$recentActivities = $reportModel->getRecentActivities(10);

// Fetch top active users
$topActiveUsers = $reportModel->getTopActiveUsers(5);

// Fetch actual counts for Likes, Comments, Shares
try {
    $stmtLikes = $conn->query("SELECT COUNT(*) FROM post_likes");
    $totalLikes = $stmtLikes->fetchColumn();

    $stmtComments = $conn->query("SELECT COUNT(*) FROM post_comments");
    $totalComments = $stmtComments->fetchColumn();

    $stmtShares = $conn->query("SELECT COUNT(*) FROM post_shares");
    $totalShares = $stmtShares->fetchColumn();
} catch (PDOException $e) {
    $totalLikes = 0; $totalComments = 0; $totalShares = 0;
}

// Helper function for time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
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
    <title>Social Activity & Engagement Report | LocalConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/Report/userReport.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/home/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>
        
        <nav class="menu">
            <a href="<?= BASE_URL ?>app/Views/Home/dashboard.php" class="item"><i class="fa-solid fa-home"></i> Home</a>
            <a href="<?= BASE_URL ?>app/Views/Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="<?= BASE_URL ?>app/Views/Groups/groups.php" class="item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="<?= BASE_URL ?>app/Views/Events/events.php" class="item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="<?= BASE_URL ?>app/Views/Friends/AllFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="<?= BASE_URL ?>app/Views/Messages/message.php" class="item"><i class="fa-solid fa-message"></i> Messages</a>
            <a href="<?= BASE_URL ?>app/Views/Report/userReport.php" class="item active"><i class="fa-solid fa-chart-simple"></i> Report</a> 
        </nav>

        <button class="btn-create" id="open-create-post" type="button">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
    </div>
    
    <div class="main-content">
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
            <div class="chart-container">
                <div class="chart-header">
                    <h3>Engagement Trends</h3>
                    <p>Post Likes vs. Comments (Last 30 Days)</p>
                </div>
                <canvas id="engagementChart"></canvas>
            </div>

            <!-- Two Column Layout -->
            <div class="two-columns">
                <!-- Real-time Activity -->
                <div class="activity-feed">
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
                <div class="story-performance">
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
            <div class="top-users-section">
                <div class="section-header">
                    <h3>Top Active Users</h3>
                    <button class="export-btn">
                        <i class="fa-regular fa-file-excel"></i>
                        Export List
                    </button>
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

    <script>
        // Engagement Chart Data
        const engagementTrends = <?php echo json_encode($engagementTrends); ?>;
        
        // Prepare data for chart
        const labels = engagementTrends.map(item => {
            const date = new Date(item.date);
            return `AUG ${date.getDate()}`;
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
    <script src="<?= BASE_URL ?>public/js/common/theme-toggle.js"></script>
    <?php require_once __DIR__ . '/../Component/create_post_modal.php'; ?>
    <script>
        const CURRENT_USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
        const CURRENT_USER_NAME = <?php echo json_encode((string)($u_d['Username'] ?? 'User')); ?>;
        const CURRENT_USER_AVATAR = <?php echo json_encode($profilePicPath); ?>;
    </script>
    <script src="../../../public/js/Home/dashboard.js"></script>
</body>
</html>
