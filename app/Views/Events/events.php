<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LocalConnect V2 - Creative Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/css/events/events.css">
    <link rel="stylesheet" href="../../../public/css/Home/dashboard.css">
</head>
<body>

    <div class="sidebar">
        <div class="logo-section">
            <i class="fa-solid fa-cube"></i>
            <span>LocalConnect</span>
        </div>
        
        <nav class="nav-menu">
            <a href="../Home/dashboard.php" class="nav-item "><i class="fa-solid fa-home"></i> Home</a>
            <a href="../Explore/explore.php" class="nav-item"><i class="fa-solid fa-compass"></i> Explore</a>
            <a href="../Groups/groups.php" class="nav-item"><i class="fa-solid fa-people-group"></i> Groups</a>
            <a href="../Events/events.php" class="nav-item active"><i class="fa-solid fa-calendar-days"></i> Events</a>
            <a href="../Friends/AllFriends.php" class="nav-item"><i class="fa-solid fa-user-friends"></i> Friends</a>
            <a href="../Messages/message.php" class="nav-item"><i class="fa-solid fa-message"></i> Messages</a>
            <a href="../Report/userReport.php" class="nav-item"><i class="fa-solid fa-chart-simple"></i> Report</a>       
        </nav>

        <button class="btn-create">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Create New Post</span>
        </button>
    </div>

    <div class="main-content">
        <header>
            <div class="search-bar">
                <i class="fa-solid fa-search text-muted"></i>
                <input type="text" placeholder="Search for anything...">
            </div>
            
            <div class="header-actions">
                <div class="icon-btn"><i class="fa-solid fa-moon"></i></div>
                <div class="icon-btn" style="position: relative;">
                    <i class="fa-solid fa-bell"></i>
                    <span style="position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                </div>
                <div class="user-profile">
                    <span style="font-weight: 700;">Dara S.</span>
                    <a href="../Profile/profile.php">
                    <img src="https://i.pravatar.cc/150?u=me" alt="Profile"></a>
                </div>
            </div>
        </header>
                
                <div class="events-container">
            <div class="events-header">
                <h1>Events</h1>
                <button class="btn-primary"><i class="fa-solid fa-plus"></i> Create Event</button>
            </div>

            <div class="events-tabs">
                <div class="tab active">Upcoming Events</div>
                <div class="tab">My Events (3)</div>
            </div>

            <div class="events-list">
                <div class="event-card">
                    <div class="event-image">
                        <img src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80" alt="Yoga">
                    </div>
                    <div class="event-details">
                        <div class="event-info-top">
                            <span class="tag health">Health & Wellness</span>
                            <span class="event-date-text">Tomorrow</span>
                        </div>
                        <h3>Community Yoga in the Park</h3>
                        <p class="event-desc">Free outdoor yoga session. All levels welcome! Bring your own mat.</p>
                        
                        <div class="event-meta">
                            <span><i class="fa-regular fa-clock"></i> 1:44 PM</span>
                            <span><i class="fa-solid fa-location-dot"></i> Golden Gate Park, San Francisco</span>
                            <span><i class="fa-solid fa-users"></i> 1 going</span>
                        </div>

                        <div class="event-actions">
                            <button class="btn-status going"><i class="fa-solid fa-check"></i> Going</button>
                            <button class="btn-status interested">Interested</button>
                        </div>
                    </div>
                </div>

                <div class="event-card">
                    <div class="event-image">
                        <img src="https://images.unsplash.com/photo-1488459711615-228f39596750?auto=format&fit=crop&w=800&q=80" alt="Market">
                    </div>
                    <div class="event-details">
                        <div class="event-info-top">
                            <span class="tag market">Market</span>
                            <span class="event-date-text">Mar 5, 2026</span>
                        </div>
                        <h3>Weekend Farmers Market</h3>
                        <p class="event-desc">Fresh local produce, artisan goods, and live music. Every Saturday morning!</p>
                        
                        <div class="event-meta">
                            <span><i class="fa-regular fa-clock"></i> 1:44 PM</span>
                            <span><i class="fa-solid fa-location-dot"></i> Ferry Building, San Francisco</span>
                            <span><i class="fa-solid fa-users"></i> 3 going</span>
                        </div>

                        <div class="event-actions">
                            <button class="btn-status going"><i class="fa-solid fa-check"></i> Going</button>
                            <button class="btn-status interested">Interested</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        </div>
    </div>
</body>
</html>