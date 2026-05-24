<div class="sidebar">
    <div class="profile">
        <img src="/api/placeholder/100/100" alt="Profile" class="profile-img"> 
        <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
        <p>Member</p>
    </div>
    
    <nav class="nav-menu">
        <ul>
            <li><a href="user.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'class="active"' : ''; ?>><i class='bx bxs-dashboard'></i> Dashboard</a></li>
            <li><a href="user_profile.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user_profile.php' ? 'class="active"' : ''; ?>><i class='bx bxs-user'></i> My Profile</a></li>
            <li><a href="user_classes.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user_classes.php' ? 'class="active"' : ''; ?>><i class='bx bxs-calendar'></i> Class Schedule</a></li>
            <li><a href="blogs.php" <?php echo basename($_SERVER['PHP_SELF']) == 'blogs.php' ? 'class="active"' : ''; ?>><i class='bx bxs-news'></i> Fitness Blogs</a></li>
            <li><a href="user_nutrition.php" <?php echo basename($_SERVER['PHP_SELF']) == 'user_nutrition.php' ? 'class="active"' : ''; ?>><i class='bx bxs-bowl-hot'></i> Nutrition Guide</a></li>
            <li><a href="logout.php"><i class='bx bxs-log-out'></i> Logout</a></li>
        </ul>
    </nav>
</div>