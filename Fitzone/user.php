<?php
session_start();
require "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: register_login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <div class="user-dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile">
                <img src="images/profile.png" alt="Profile" class="profile-img"> 
                <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                <p>Member</p>
            </div>
            
            <nav class="nav-menu">
    <ul>
        <li><a href="user.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'class="active"' : ''; ?>><i class='bx bxs-dashboard'></i> Dashboard</a></li>
        <li><a href="user_profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user_profile.php' ? 'class="active"' : ''; ?>><i class='bx bxs-user'></i> My Profile</a></li>
        <li><a href="user_classes.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user_classes.php' ? 'class="active"' : ''; ?>><i class='bx bxs-calendar'></i> Class Schedule</a></li>
        <li><a href="user_membership.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user_membership.php' ? 'class="active"' : ''; ?>><i class='bx bxs-card'></i> Membership Details</a></li>
        <li><a href="user_nutrition.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user_nutrition.php' ? 'class="active"' : ''; ?>><i class='bx bxs-bowl-hot'></i> Nutrition Guide</a></li>
        <li><a href="logout.php"><i class='bx bxs-log-out'></i> Logout</a></li>
    </ul>
</nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <i class='bx bx-menu' id="menu-toggle"></i>
                    <h1>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                </div>
                <div class="header-right">
                   
                    
                </div>
            </header>
            
            <div class="content">
                <!-- Dashboard Overview -->
                <div class="dashboard-cards">
                    <div class="card">
                        <i class='bx bxs-calendar-check'></i>
                        <h3>Upcoming Classes</h3>
                        <p>2 classes this week</p>
                    </div>
                    
                    <div class="card">
                        <i class='bx bxs-trophy'></i>
                        <h3>Workout Streak</h3>
                        <p>5 days in a row</p>
                    </div>
                    
                    <div class="card">
    <i class='bx bxs-bowl-hot'></i>
    <h3>Nutrition Guides</h3>
    <p><?php 
        $nutrition_count = $conn->query("SELECT COUNT(*) as count FROM nutrition_guides")->fetch_assoc()['count'];
        echo $nutrition_count . ' available guides';
    ?></p>
    <a href="user_nutrition.php" class="card-link">View All</a>
</div>
                </div>
                
                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    <div class="activity-item">
                        <i class='bx bxs-check-circle'></i>
                        <p>Attended Yoga Class on Monday</p>
                        <span>2 days ago</span>
                    </div>
                    <div class="activity-item">
                        <i class='bx bxs-bookmark'></i>
                        <p>Saved "Healthy Breakfast Ideas" article</p>
                        <span>4 days ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/user.js"></script>
</body>
</html>