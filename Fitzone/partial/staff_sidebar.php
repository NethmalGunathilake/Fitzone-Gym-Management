<div class="sidebar">
    <div class="sidebar-header">
    <img src="images/logo.jpg" alt="Logo">
        <h3>FitZone Staff</h3>
    </div>
    
    <div class="sidebar-menu">
        <h3>Main</h3>
        <ul>
            <li><a href="staff_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff_dashboard.php' ? 'active' : ''; ?>"><i class='bx bxs-dashboard'></i> <span>Dashboard</span></a></li>
            <li><a href="staff_classes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff_classes.php' ? 'active' : ''; ?>"><i class='bx bxs-calendar'></i> <span>Classes</span></a></li>
            <li><a href="staff_nutrition.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff_nutrition.php' ? 'active' : ''; ?>"><i class='bx bxs-bowl-hot'></i> <span>Nutrition Guides</span></a></li>
        </ul>
        
        <h3>Account</h3>
        <ul>
            <li><a href="staff_profile.php"><i class='bx bxs-user'></i> <span>Profile</span></a></li>
            <li><a href="logout.php"><i class='bx bxs-log-out'></i> <span>Logout</span></a></li>
        </ul>
    </div>
</div>