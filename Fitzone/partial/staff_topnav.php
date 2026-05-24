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