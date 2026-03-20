<?php
//    session_start();

//     require_once '../../../config/db_config.php';

//     require_once '../../Models/Profile/ProfileModel.php';
//     // សុវត្ថិភាព
//     if (!isset($_SESSION['user_id'])) {
//         header("Location: ../Login/login_gmail.php");
//         exit();
//     }

//     $model = new ProfileModel($conn);
//     $user_id = $_SESSION['user_id'];
//     $u_d = $model->getUserData($user_id);
//     $stats = $model->getProfileStats($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/Report/userReport.css">
    <link rel="stylesheet" href="../../../public/css/home/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>
        
        <nav class="menu">
            <a href="../Home/dashboard.php" class="item"><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="item"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AllFriends.php" class="item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="item"><i class="fa-solid fa-message"></i> Messages</a>
            <a href="userReport.php" class="item active"><i class="fa-solid fa-chart-simple"></i> Report</a> 
        </nav>

        <button class="btn-create">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
        
    </div>
    
    <div class="container">

        <!-- Top Cards -->
        <div class="cards">

            <div class="card">
                <h4>Total Users</h4>
                <h2>1.24M</h2>
                <span class="green">+5.2%</span>
            </div>

            <div class="card">
                <h4>Net Growth</h4>
                <h2>15.4K</h2>
                <span class="green">+12.8%</span>
            </div>

            <div class="card">
                <h4>Churn Rate</h4>
                <h2>2.1%</h2>
                <span class="orange">-0.4%</span>
            </div>

            <div class="card">
                <h4>Acquisition Cost</h4>
                <h2>$12.50</h2>
                <span class="green">-2.1%</span>
            </div>

        </div>


        <!-- Chart -->
        <div class="chart-box">

            <div class="chart-header">
                <h3>Cumulative User Growth</h3>
                <div>
                    <button>6 Months</button>
                    <button>1 Year</button>
                </div>
            </div>

            <canvas id="growthChart"></canvas>

        </div>
        <script src="../../../public/Report/reportScript.js"></script>
    </div>
</body>
</html>