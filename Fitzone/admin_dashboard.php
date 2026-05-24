<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: register_login.php");
    exit();
}

// Get current user data from session
$current_user = [
    'first_name' => isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Admin',
    'last_name' => isset($_SESSION['last_name']) ? $_SESSION['last_name'] : 'User'
];

// Get all users
$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="menu-toggle">
        <i class='bx bx-menu'></i>
    </div>
    
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/logo.jpg" alt="Logo">
                <h3>FitZone Admin</h3>
            </div>
            
            <div class="sidebar-menu">
                <h3>Main</h3>
                <ul>
                    <li><a href="admin_dashboard.php" class="active"><i class='bx bxs-dashboard'></i> <span>Dashboard</span></a></li>
                    <li><a href="members.php"><i class='bx bxs-user'></i> <span>Members</span></a></li>
                    
                    <li><a href="classes.php"><i class='bx bxs-calendar'></i> <span>Classes</span></a></li>
                  
                </ul>
                
                <h3>Account</h3>
                <ul>
                   
                    <li><a href="logout.php"><i class='bx bxs-log-out'></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Nav -->
            <div class="top-nav">
                <div class="user-profile">
                    <div class="notification-icon">
                        <i class='bx bxs-bell'></i>
                        <span class="badge">3</span>
                    </div>
                    
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></h4>
                        <p>Admin</p>
                    </div>
                    
                    <img src="images/profile.png" alt="User">
                </div>
            </div>
            
            
            <!-- Content Area -->
<div class="content-area">
    <!-- Display Messages -->
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
    
    <div class="page-header">
        <h2>Dashboard Overview</h2>
    </div>
    
    <!-- Stats Cards - Updated Version -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="card-header">
            <div>
                <h3>Total Members</h3>
                <p><i class='bx bx-up-arrow-alt'></i> 12% from last month</p>
            </div>
            <div class="card-icon members">
                <i class='bx bxs-user'></i>
            </div>
        </div>
        <h2>315</h2>
        <div class="progress-bar">
            <div class="progress" style="width: 75%"></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="card-header">
            <div>
                <h3>Active Classes</h3>
                <p><i class='bx bx-up-arrow-alt'></i> 2 new this week</p>
            </div>
            <div class="card-icon classes">
                <i class='bx bxs-calendar'></i>
            </div>
        </div>
        <h2>24</h2>
        <div class="progress-bar">
            <div class="progress" style="width: 60%"></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="card-header">
            <div>
                <h3>Today's Attendance</h3>
                <p>85% of daily capacity</p>
            </div>
            <div class="card-icon attendance">
                <i class='bx bxs-check-circle'></i>
            </div>
        </div>
        <h2>187</h2>
        <div class="progress-bar">
            <div class="progress" style="width: 85%"></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="card-header">
            <div>
                <h3>Monthly Revenue</h3>
                <p><i class='bx bx-up-arrow-alt'></i> 8% from last month</p>
            </div>
            <div class="card-icon revenue">
                <i class='bx bxs-dollar-circle'></i>
            </div>
        </div>
        <h2>LKR 34,560</h2>
        <div class="progress-bar">
            <div class="progress" style="width: 65%"></div>
        </div>
    </div>
</div>


    
    <!-- Additional dashboard content can go here -->
</div>

            <div class="content-area">
                <!-- Display Messages -->
                <?php if(isset($_SESSION['message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                            echo $_SESSION['message']; 
                            unset($_SESSION['message']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php 
                            echo $_SESSION['error']; 
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                
                
                <!-- Stats Cards -->
                <div class="stats-cards">
                    <!-- Cards content remains the same -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>