<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: register_login.php");
    exit();
}

// Check if class ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: staff_classes.php");
    exit();
}

$class_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get class details
$class_query = "SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as trainer_name 
                FROM classes c
                LEFT JOIN users u ON c.trainer_id = u.id
                WHERE c.id = ?";
$stmt = $conn->prepare($class_query);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class_result = $stmt->get_result();
$class = $class_result->fetch_assoc();


$stmt->close();

// If class doesn't exist, redirect back
if (!$class) {
    header("Location: staff_classes.php");
    exit();
}

// Get registered users for this class
$registered_users_query = "SELECT u.id, u.first_name, u.last_name, u.email, cr.registered_at
                          FROM users u
                          JOIN class_registrations cr ON u.id = cr.user_id
                          WHERE cr.class_id = ?
                          ORDER BY cr.registered_at DESC";
$stmt = $conn->prepare($registered_users_query);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$registered_users_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Details | FitZone Staff</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="css/staff.css">
    <style>
        .class-details-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-top: 1.5rem;
        }
        
        .class-info-card, .registered-users-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .class-info-card:hover, .registered-users-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .class-info-header, .card-header {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .class-info-header h3, .card-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .class-info-body {
            padding: 1.5rem;
        }
        
        .info-item {
            display: flex;
            margin-bottom: 1.2rem;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 120px;
            padding-right: 1rem;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .description {
            line-height: 1.6;
            color: #444;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background-color: #f8f9fa;
            padding: 0.8rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #eee;
        }
        
        .data-table td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .data-table tr:hover td {
            background-color: #f9f9f9;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #777;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        
        .empty-state p {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .progress-container {
            margin-top: 0.5rem;
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .capacity-info {
            display: flex;
            justify-content: space-between;
            margin-top: 0.3rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        @media (min-width: 992px) {
            .class-details-container {
                grid-template-columns: 1fr 1.5fr;
            }
        }

        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #2c3e50;
            --sidebar-active: #3498db;
            --main-color: #3498db;
            --card-bg: #ffffff;
            --text-dark: #2c3e50;
            --text-light:rgb(27, 29, 29);
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
            color: rgba(218, 212, 212, 0.5);
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
        <?php include 'partial/staff_sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partial/staff_topnav.php'; ?>
            
            <div class="content-area">
                <div class="page-header">
                    <a href="staff_classes.php" class="back-btn"><i class='bx bx-arrow-back'></i></a>
                    <h2><?php echo htmlspecialchars($class['class_name']); ?></h2>
                    <div class="class-status">
                        <span class="status-badge <?php echo ($registered_users_result->num_rows >= $class['max_capacity']) ? 'full' : 'available'; ?>">
                            <?php echo ($registered_users_result->num_rows >= $class['max_capacity']) ? 'Full' : 'Available'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="class-details-container animate__animated animate__fadeIn">
                    <div class="class-info-card">
                        <div class="class-info-header">
                            <h3><i class='bx bx-info-circle'></i> Class Details</h3>
                        </div>
                        
                        <div class="class-info-body">
                            <div class="info-item">
                                <span class="info-label"><i class='bx bx-calendar'></i> Date & Time:</span>
                                <span class="info-value"><?php echo date('F j, Y, g:i a', strtotime($class['schedule'])); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label"><i class='bx bx-time'></i> Duration:</span>
                                <span class="info-value"><?php echo $class['duration']; ?> minutes</span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label"><i class='bx bx-map'></i> Location:</span>
                                <span class="info-value"><?php echo htmlspecialchars($class['location'] ?? 'Main Gym'); ?></span>
                            </div>
                            
                            
                            
                            <div class="info-item">
                                <span class="info-label"><i class='bx bx-group'></i> Capacity:</span>
                                <div class="info-value">
                                    <?php 
                                    $percentage = ($registered_users_result->num_rows / $class['max_capacity']) * 100;
                                    ?>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <div class="capacity-info">
                                        <span><?php echo $registered_users_result->num_rows; ?> registered</span>
                                        <span><?php echo $class['max_capacity']; ?> spots total</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label"><i class='bx bx-notepad'></i> Description:</span>
                                <div class="info-value description">
                                    <?php echo nl2br(htmlspecialchars($class['description'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="registered-users-card">
                        <div class="card-header">
                            <h3><i class='bx bx-list-ul'></i> Registered Members</h3>
                        </div>
                        
                        <div class="card-body">
                            <?php if ($registered_users_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Member</th>
                                                <th>Contact</th>
                                                <th>Registration Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $registered_users_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="user-info">
                                                            <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo date('M j, Y, g:i a', strtotime($user['registered_at'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class='bx bx-user-x'></i>
                                    <p>No members have registered for this class yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/staff.js"></script>
</body>
</html>