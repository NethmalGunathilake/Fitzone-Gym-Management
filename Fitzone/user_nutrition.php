<?php
session_start();
require "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: register_login.php");
    exit();
}

// Get user information first
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Get all nutrition guides
$nutrition_guides = $conn->query("
    SELECT ng.*, u.first_name, u.last_name 
    FROM nutrition_guides ng
    LEFT JOIN users u ON ng.created_by = u.id
    ORDER BY ng.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrition Guides | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
                    <li><a href="user.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
                    <li><a href="user_profile.php"><i class='bx bxs-user'></i> My Profile</a></li>
                    <li><a href="user_classes.php"><i class='bx bxs-calendar'></i> Class Schedule</a></li>
                    <li><a href="user_membership.php"><i class='bx bxs-news'></i> Membership Details</a></li>
                    <li><a href="user_nutrition.php" class="active"><i class='bx bxs-bowl-hot'></i> Nutrition Guide</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out'></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <i class='bx bx-menu' id="menu-toggle"></i>
                    <h1>Nutrition Guides</h1>
                </div>
                <div class="header-right">
                    
                </div>
            </header>
            
            <div class="content">
                <div class="nutrition-guides">
                    <?php if($nutrition_guides->num_rows > 0): ?>
                        <div class="guides-grid">
                            <?php while($guide = $nutrition_guides->fetch_assoc()): ?>
                                <div class="guide-card">
                                    <div class="guide-header">
                                        <h3><?php echo htmlspecialchars($guide['title']); ?></h3>
                                    </div>
                                    
                                    <div class="guide-body">
                                        <p><?php 
                                            $description = htmlspecialchars($guide['description']);
                                            echo strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
                                        ?></p>
                                    </div>
                                    
                                    <div class="guide-meta">
                                        <span><i class='bx bx-calendar'></i> <?php echo date('M j, Y', strtotime($guide['created_at'])); ?></span>
                                        <span><i class='bx bx-user'></i> <?php echo htmlspecialchars($guide['first_name'] . ' ' . $guide['last_name']); ?></span>
                                    </div>
                                    
                                    <div class="guide-footer">
                                        <a href="user_nutrition_detail.php?id=<?php echo $guide['id']; ?>" class="btn btn-primary">
                                            <i class='bx bx-detail'></i> View Guide
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class='bx bx-food-menu'></i>
                            <p>No nutrition guides available yet</p>
                            <p>Check back later for nutrition tips from our staff!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/user.js"></script>
</body>
</html>