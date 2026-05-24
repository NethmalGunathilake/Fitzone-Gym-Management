<?php
session_start();
require "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: register_login.php");
    exit();
}

// Get user information from database instead of session
$user_id = $_SESSION['user_id'];
$user_query = $conn->query("SELECT first_name, last_name FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();

// Check if guide ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: user_nutrition.php");
    exit();
}

$guide_id = $_GET['id'];

// Get guide details
$stmt = $conn->prepare("
    SELECT ng.*, u.first_name as author_first, u.last_name as author_last 
    FROM nutrition_guides ng
    LEFT JOIN users u ON ng.created_by = u.id
    WHERE ng.id = ?
");
$stmt->bind_param("i", $guide_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: user_nutrition.php");
    exit();
}

$guide = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($guide['title']); ?> | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <div class="user-dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile">
                <img src="images/profile.png" alt="Profile" class="profile-img"> 
                <h3><?php 
                    // Use user data from database query instead of session
                    echo htmlspecialchars(
                        ($user['first_name'] ?? 'User') . ' ' . 
                        ($user['last_name'] ?? '')
                    ); 
                ?></h3>
                <p>Member</p>
            </div>
            
            <nav class="nav-menu">
                <ul>
                    <li><a href="user.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
                    <li><a href="user_profile.php"><i class='bx bxs-user'></i> My Profile</a></li>
                    <li><a href="user_classes.php"><i class='bx bxs-calendar'></i> Class Schedule</a></li>
                    <li><a href="blogs.php"><i class='bx bxs-news'></i> Fitness Blogs</a></li>
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
                    <a href="user_nutrition.php" class="back-btn"><i class='bx bx-arrow-back'></i></a>
                    <h1><?php echo htmlspecialchars($guide['title']); ?></h1>
                </div>
                <div class="header-right">
                    
                   
                </div>
            </header>
            
            <div class="content">
                <div class="nutrition-detail-container">
                    <div class="nutrition-header">
                        <h1><?php echo htmlspecialchars($guide['title']); ?></h1>
                        <div class="nutrition-meta">
                            <span><i class='bx bx-calendar'></i> <?php echo date('F j, Y', strtotime($guide['created_at'])); ?></span>
                            <span><i class='bx bx-user'></i> <?php 
                                echo htmlspecialchars(
                                    ($guide['author_first'] ?? 'Staff') . ' ' . 
                                    ($guide['author_last'] ?? '')
                                ); 
                            ?></span>
                        </div>
                    </div>
                    
                    <?php if(!empty($guide['description'])): ?>
                    <div class="nutrition-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($guide['description'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="nutrition-content">
                        <h3>Content</h3>
                        <div class="content-body">
                            <?php echo $guide['content']; ?>
                        </div>
                    </div>
                    
                    <div class="nutrition-actions">
                        <a href="user_nutrition.php" class="btn btn-secondary">
                            <i class='bx bx-arrow-back'></i> Back to Guides
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/user.js"></script>
</body>
</html>