<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: register_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$classes = $conn->query("
    SELECT c.*, COUNT(cr.user_id) as current_enrollment 
    FROM classes c
    LEFT JOIN class_registrations cr ON c.id = cr.class_id
    GROUP BY c.id
    ORDER BY c.schedule ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes | FitZone Staff</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="css/staff.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .add-class-btn {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .add-class-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }
        
        .classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .class-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .class-header {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 1.2rem;
            position: relative;
        }
        
        .class-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .class-time {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .class-body {
            padding: 1.2rem;
            flex: 1;
        }
        
        .class-body p {
            color: #555;
            line-height: 1.5;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        
        .class-meta {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            font-size: 0.9rem;
            color: #666;
        }
        
        .class-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .class-footer {
            padding: 0 1.2rem 1.2rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            background: #f9f9f9;
            border-radius: 12px;
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
        
        .capacity-bar {
            height: 6px;
            background: #f0f0f0;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .capacity-progress {
            height: 100%;
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            border-radius: 3px;
        }
        
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-badge.full {
            background: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }
        
        .status-badge.available {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }
        
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
        <?php include 'partial/staff_sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'partial/staff_topnav.php'; ?>
            
            <div class="content-area animate__animated animate__fadeIn">
                <div class="page-header">
                    <h2>Manage Classes</h2>
                    
                </div>
                
                <div class="classes-grid">
                    <?php while($class = $classes->fetch_assoc()): 
                        $enrollment_percentage = ($class['current_enrollment'] / $class['max_capacity']) * 100;
                        $is_full = $class['current_enrollment'] >= $class['max_capacity'];
                    ?>
                    <div class="class-card">
                        <div class="class-header">
                            <h3><?php echo htmlspecialchars($class['class_name']); ?></h3>
                            <span class="class-time">
                                <?php echo date('D, M j, g:i a', strtotime($class['schedule'])); ?>
                            </span>
                            <span class="status-badge <?php echo $is_full ? 'full' : 'available'; ?>">
                                <?php echo $is_full ? 'Full' : 'Available'; ?>
                            </span>
                        </div>
                        
                        <div class="class-body">
                            <p><?php echo htmlspecialchars($class['description']); ?></p>
                            
                            <div class="capacity-bar">
                                <div class="capacity-progress" style="width: <?php echo $enrollment_percentage; ?>%"></div>
                            </div>
                            
                            <div class="class-meta">
                                <span><i class='bx bx-time'></i> <?php echo $class['duration']; ?> mins</span>
                                <span><i class='bx bx-group'></i> <?php echo $class['current_enrollment']; ?>/<?php echo $class['max_capacity']; ?></span>
                            </div>
                        </div>
                        
                        <div class="class-footer">
                            <a href="staff_class_detail.php?id=<?php echo $class['id']; ?>" class="btn btn-primary">
                                <i class='bx bx-detail'></i> View Details
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    
                    <?php if($classes->num_rows == 0): ?>
                        <div class="empty-state">
                            <i class='bx bx-calendar'></i>
                            <p>No classes scheduled yet</p>
                            <a href="staff_create_class.php" class="add-class-btn" style="margin-top: 1rem; display: inline-flex;">
                                <i class='bx bx-plus'></i> Create Your First Class
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/staff.js"></script>
</body>
</html>