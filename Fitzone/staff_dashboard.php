<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: register_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

$nutrition_guides = $conn->query("
    SELECT * FROM nutrition_guides 
    ORDER BY created_at DESC 
    LIMIT 3
");

$upcoming_classes = $conn->query("
    SELECT c.*, COUNT(cr.user_id) AS current_enrollment
    FROM classes c
    LEFT JOIN class_registrations cr ON c.id = cr.class_id
    WHERE c.schedule >= NOW()
    GROUP BY c.id
    ORDER BY c.schedule ASC 
    LIMIT 5
");

// Get additional stats
$total_classes = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
$total_members = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'member'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="css/staff.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #2c3e50;
            --sidebar-active: #3498db;
            --main-color: #3498db;
            --card-bg: #ffffff;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
        }
        
        /* Enhanced Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu h3 {
            padding: 0 20px 10px;
            font-size: 0.9rem;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            letter-spacing: 1px;
            margin-bottom: 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 25px;
        }
        
        .sidebar-menu li a.active {
            background: var(--sidebar-active);
            color: white;
        }
        
        .sidebar-menu li a i {
            font-size: 1.2rem;
            margin-right: 10px;
            width: 24px;
            text-align: center;
        }
        
        .sidebar-menu li a span {
            font-size: 0.95rem;
        }
        
        /* Main Content Adjustments */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s;
        }
        
        /* Enhanced Top Navigation */
        .top-nav {
            background: white;
            padding: 15px 25px;
            display: flex;
            justify-content: flex-end;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 90;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-info {
            text-align: right;
            margin-right: 15px;
        }
        
        .user-info h4 {
            margin: 0;
            font-size: 1rem;
            color: var(--text-dark);
        }
        
        .user-info p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--main-color);
        }
        
        /* Enhanced Dashboard Content */
        .content-area {
            padding: 25px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            color: var(--text-dark);
            font-size: 1.8rem;
            margin: 0;
            position: relative;
            display: inline-block;
        }
        
        .page-header h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 3px;
            background: var(--main-color);
        }
        
        /* Enhanced Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--main-color);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(52, 152, 219, 0.1);
            color: var(--main-color);
        }
        
        .stat-card h2 {
            margin: 0;
            font-size: 2.2rem;
            color: var(--text-dark);
            font-weight: 700;
        }
        
        /* Dashboard Sections */
        .dashboard-section {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .section-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-header h3 i {
            color: var(--main-color);
        }
        
        .view-all {
            color: var(--main-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        /* Class List */
        .class-list {
            display: grid;
            gap: 15px;
        }
        
        .class-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: rgba(0,0,0,0.02);
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .class-item:hover {
            background: rgba(52, 152, 219, 0.05);
            transform: translateX(5px);
        }
        
        .class-info h4 {
            margin: 0 0 5px;
            font-size: 1rem;
            color: var(--text-dark);
        }
        
        .class-info p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-light);
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background: var(--main-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        /* Guides List */
        .guides-list {
            display: grid;
            gap: 15px;
        }
        
        .guide-item {
            padding: 15px;
            background: rgba(0,0,0,0.02);
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .guide-item:hover {
            background: rgba(52, 152, 219, 0.05);
            transform: translateX(5px);
        }
        
        .guide-item h4 {
            margin: 0 0 10px;
            font-size: 1rem;
            color: var(--text-dark);
        }
        
        .guide-desc {
            margin: 0 0 10px;
            font-size: 0.9rem;
            color: var(--text-light);
            line-height: 1.5;
        }
        
        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .empty-state p {
            margin: 0;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="staff-dashboard">
        <!-- Enhanced Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="images/logo.jpg" alt="Logo">
                <h3>FitZone Staff</h3>
            </div>
            
            <div class="sidebar-menu">
                <h3>Main</h3>
                <ul>
                    <li><a href="staff_dashboard.php" class="active"><i class='bx bxs-dashboard'></i> <span>Dashboard</span></a></li>
                    <li><a href="staff_classes.php"><i class='bx bxs-calendar'></i> <span>Classes</span></a></li>
                    <li><a href="staff_nutrition.php"><i class='bx bxs-bowl-hot'></i> <span>Nutrition Guides</span></a></li>
                </ul>
                
                <h3>Account</h3>
                <ul>
                    <li><a href="staff_profile.php"><i class='bx bxs-user'></i> <span>Profile</span></a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out'></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Nav -->
            <div class="top-nav">
                <div class="user-profile">
                    <div class="user-info">
                        <h4><?php echo isset($_SESSION['first_name'], $_SESSION['last_name']) ? 
                            htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 
                            "Staff User"; ?></h4>
                        <p>Staff</p>
                    </div>
                    <img src="images/profile.png" alt="User">
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area animate__animated animate__fadeIn">
                <div class="page-header">
                    <h2>Staff Dashboard</h2>
                </div>
                
                <!-- Enhanced Stats Cards -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="card-header">
                            <div>
                                <h3>Upcoming Classes</h3>
                            </div>
                            <div class="card-icon classes">
                                <i class='bx bxs-calendar'></i>
                            </div>
                        </div>
                        <h2><?php echo $upcoming_classes->num_rows; ?></h2>
                    </div>
                    
                    <div class="stat-card">
                        <div class="card-header">
                            <div>
                                <h3>Nutrition Guides</h3>
                            </div>
                            <div class="card-icon nutrition">
                                <i class='bx bxs-bowl-hot'></i>
                            </div>
                        </div>
                        <h2><?php echo $nutrition_guides->num_rows; ?></h2>
                    </div>
                    
                    
                </div>
                
                <!-- Upcoming Classes Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3><i class='bx bxs-calendar'></i> Upcoming Classes</h3>
                        <a href="staff_classes.php" class="view-all">View All</a>
                    </div>
                    
                    <div class="class-list">
                        <?php if($upcoming_classes->num_rows > 0): ?>
                            <?php while($class = $upcoming_classes->fetch_assoc()): ?>
                            <div class="class-item">
                                <div class="class-info">
                                    <h4><?php echo htmlspecialchars($class['class_name']); ?></h4>
                                    <p><?php echo date('D, M j, g:i a', strtotime($class['schedule'])); ?></p>
                                    <p><?php echo $class['current_enrollment']; ?>/<?php echo $class['max_capacity']; ?> enrolled</p>
                                </div>
                                <a href="staff_class_detail.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-primary">Details</a>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class='bx bx-calendar'></i>
                                <p>No upcoming classes scheduled</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Nutrition Guides Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3><i class='bx bxs-bowl-hot'></i> Recent Nutrition Guides</h3>
                        <a href="staff_nutrition.php" class="view-all">View All</a>
                    </div>
                    
                    <div class="guides-list">
                        <?php if($nutrition_guides->num_rows > 0): ?>
                            <?php while($guide = $nutrition_guides->fetch_assoc()): ?>
                            <div class="guide-item">
                                <h4><?php echo htmlspecialchars($guide['title']); ?></h4>
                                <p class="guide-desc"><?php echo htmlspecialchars($guide['description']); ?></p>
                                <a href="staff_nutrition_detail.php?id=<?php echo $guide['id']; ?>" class="btn btn-sm btn-primary">View</a>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class='bx bx-bowl-hot'></i>
                                <p>No nutrition guides created yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/staff.js"></script>
</body>
</html>