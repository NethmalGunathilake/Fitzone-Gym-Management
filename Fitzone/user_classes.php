<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: register_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Handle Register or Cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = intval($_POST['class_id']);

    if (isset($_POST['cancel'])) {
        // Cancel Registration
        $cancel = $conn->prepare("DELETE FROM class_registrations WHERE user_id = ? AND class_id = ?");
        $cancel->bind_param("ii", $user_id, $class_id);
        if ($cancel->execute()) {
            $success = "Successfully canceled registration.";
        } else {
            $error = "Failed to cancel registration.";
        }
    } else {
        // Register
        $check = $conn->prepare("SELECT id FROM class_registrations WHERE user_id = ? AND class_id = ?");
        $check->bind_param("ii", $user_id, $class_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "You are already registered for this class.";
        } else {
            $count_query = $conn->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM class_registrations WHERE class_id = ?) AS enrolled,
                    c.max_capacity
                FROM classes c
                WHERE c.id = ?
            ");
            $count_query->bind_param("ii", $class_id, $class_id);
            $count_query->execute();
            $count_result = $count_query->get_result()->fetch_assoc();

            if ($count_result['enrolled'] >= $count_result['max_capacity']) {
                $error = "This class is already full.";
            } else {
                $register = $conn->prepare("INSERT INTO class_registrations (user_id, class_id) VALUES (?, ?)");
                $register->bind_param("ii", $user_id, $class_id);
                if ($register->execute()) {
                    $success = "Successfully registered for the class!";
                } else {
                    $error = "Something went wrong. Try again.";
                }
            }
        }
    }
}

// Fetch upcoming classes
$classes_result = $conn->query("
    SELECT 
        c.*,
        (SELECT COUNT(*) FROM class_registrations cr WHERE cr.class_id = c.id) AS current_enrollment,
        EXISTS(
            SELECT 1 FROM class_registrations cr WHERE cr.class_id = c.id AND cr.user_id = $user_id
        ) AS is_registered
    FROM classes c
    WHERE c.schedule >= NOW()
    ORDER BY c.schedule ASC
");
$classes = [];
while ($row = $classes_result->fetch_assoc()) {
    $classes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Schedule | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <div class="user-dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile">
                <?php
                $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                ?>
                <img src="images/profile.png" alt="Profile" class="profile-img"> 
                <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                <p>Member</p>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="user.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
                    <li><a href="user_profile.php"><i class='bx bxs-user'></i> My Profile</a></li>
                    <li><a href="user_classes.php" class="active"><i class='bx bxs-calendar'></i> Class Schedule</a></li>
                    <li><a href="user_membership.php"><i class='bx bxs-news'></i> Membership Details</a></li>
                    <li><a href="user_nutrition.php"><i class='bx bxs-bowl-hot'></i> Nutrition Guide</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out'></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <i class='bx bx-menu'></i>
                    <h1>Class Schedule</h1>
                </div>
            </header>

            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <h2>📆 Calendar View (Upcoming Classes)</h2>
                <div class="calendar-view">
                    <?php
                    $current_date = '';
                    foreach ($classes as $class) {
                        $class_date = date('Y-m-d', strtotime($class['schedule']));
                        if ($class_date !== $current_date) {
                            if ($current_date !== '') echo "</div>"; // close column
                            echo "<div class='calendar-column'><h4>" . date('D, M j', strtotime($class_date)) . "</h4>";
                            $current_date = $class_date;
                        }

                        echo "<div class='calendar-entry'>";
                        echo "<strong>" . htmlspecialchars($class['class_name']) . "</strong><br>";
                        echo date('g:i A', strtotime($class['schedule']));
                        echo "</div>";
                    }
                    if ($current_date !== '') echo "</div>"; // close final column
                    ?>
                </div>

                <hr style="margin: 30px 0;">

                <h2>📋 All Upcoming Classes</h2>
                <?php if (count($classes) > 0): ?>
                    <?php foreach ($classes as $class): ?>
                        <div class="class-card">
                            <h3><?php echo htmlspecialchars($class['class_name']); ?></h3>
                            <p><?php echo date('D, M j, Y - g:i A', strtotime($class['schedule'])); ?></p>
                            <p><?php echo $class['current_enrollment']; ?>/<?php echo $class['max_capacity']; ?> enrolled</p>
                            <form method="POST" style="margin-top:10px;">
                                <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                <?php if ($class['is_registered']): ?>
                                    <button type="submit" name="cancel" class="btn btn-danger">Cancel Registration</button>
                                <?php elseif ($class['current_enrollment'] >= $class['max_capacity']): ?>
                                    <button type="button" class="btn disabled" disabled>Class Full</button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-primary">Register</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming classes found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
