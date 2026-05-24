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

// For this example, let's assume there's a membership plan in the user table
// You may need to adjust this based on your actual database structure
$current_plan = isset($user['membership_plan']) ? $user['membership_plan'] : 'basic';

// Define plans and their features
$plans = [
    'basic' => [
        'name' => 'Basic Plan',
        'price' => '1500 LKR/month',
        'features' => [
            'Access to gym equipment',
            'Locker room & shower access',
            '24/7 gym access',
            
        ]
    ],
    'standard' => [
        'name' => 'Standard Plan',
        'price' => '2000 LKR/month',
        'features' => [
            'Everything in Basic Plan',
            'Access to all fitness classes',
            'One personal training session per month',
            'Nutritional guidance',
            
        ]
    ],
    'premium' => [
        'name' => 'Premium Plan',
        'price' => '2500 LKR/month',
        'features' => [
            'Everything in Standard Plan',
            'Unlimited personal training sessions',
            'Customized workout & diet plans',
            'Sauna & recovery lounge access',
            
        ]
    ]
];

// Process plan upgrade if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upgrade_plan'])) {
    $new_plan = $_POST['plan'];
    
    // Update the user's plan in the database
    $update_query = "UPDATE users SET membership_plan = '$new_plan' WHERE id = $user_id";
    
    if ($conn->query($update_query)) {
        $success_message = "Your membership has been upgraded to " . $plans[$new_plan]['name'] . "!";
        $current_plan = $new_plan;
    } else {
        $error_message = "Error upgrading membership: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Details | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/user.css">
    <style>
        .membership-container {
            margin-top: 20px;
        }
        
        .current-plan {
            background-color: #f0f8ff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .current-plan h3 {
            margin-top: 0;
            color: #333;
        }
        
        .plan-badge {
            display: inline-block;
            padding: 5px 15px;
            background-color: #4CAF50;
            color: white;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .current-plan-details {
            margin-top: 15px;
        }
        
        .plan-cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .plan-card {
            flex: 1;
            min-width: 250px;
            border-radius: 8px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .plan-card.current {
            border: 2px solid #4CAF50;
            position: relative;
        }
        
        .plan-card.current::after {
            content: "Current Plan";
            position: absolute;
            top: -12px;
            right: 10px;
            background-color: #4CAF50;
            color: white;
            padding: 2px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        
        .plan-card h3 {
            margin-top: 0;
            font-size: 1.5rem;
            color: #333;
        }
        
        .plan-price {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #555;
        }
        
        .plan-features {
            list-style-type: none;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .plan-features li {
            padding: 5px 0;
            display: flex;
            align-items: center;
        }
        
        .plan-features li::before {
            content: "✓";
            color: #4CAF50;
            margin-right: 8px;
            font-weight: bold;
        }
        
        .upgrade-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        
        .upgrade-btn:hover {
            background-color: #3e8e41;
        }
        
        .upgrade-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
                    <h1>Membership Details</h1>
                </div>
                <div class="header-right">
                    <!-- Header right content if needed -->
                </div>
            </header>
            
            <div class="content">
                <div class="membership-container">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Current Plan Summary -->
                    <div class="current-plan">
                        <h3>Your Current Membership</h3>
                        <div class="plan-badge"><?php echo $plans[$current_plan]['name']; ?></div>
                        <p class="plan-price"><?php echo $plans[$current_plan]['price']; ?></p>
                        <div class="current-plan-details">
                            <ul class="plan-features">
                                <?php foreach ($plans[$current_plan]['features'] as $feature): ?>
                                    <li><?php echo htmlspecialchars($feature); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Available Plans -->
                    <h2>Available Membership Plans</h2>
                    <p>Compare plans and upgrade to enhance your fitness journey.</p>
                    
                    <div class="plan-cards">
                        <?php foreach ($plans as $plan_id => $plan): ?>
                            <div class="plan-card <?php echo $current_plan == $plan_id ? 'current' : ''; ?>">
                                <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                                <p class="plan-price"><?php echo htmlspecialchars($plan['price']); ?></p>
                                <ul class="plan-features">
                                    <?php foreach ($plan['features'] as $feature): ?>
                                        <li><?php echo htmlspecialchars($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <?php if ($current_plan != $plan_id): ?>
                                    <form method="post">
                                        <input type="hidden" name="plan" value="<?php echo $plan_id; ?>">
                                        <button type="submit" name="upgrade_plan" class="upgrade-btn">
                                            Upgrade to <?php echo htmlspecialchars($plan['name']); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="upgrade-btn" disabled>Current Plan</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/user.js"></script>
</body>
</html>