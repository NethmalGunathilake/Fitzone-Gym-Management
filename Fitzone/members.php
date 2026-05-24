<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: register_login.php");
    exit();
}

$current_user = [
    'first_name' => isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Admin',
    'last_name' => isset($_SESSION['last_name']) ? $_SESSION['last_name'] : 'User'
];

$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Management | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/admin.css">
    
</head>
<body>
    <?php include 'partial/admin_sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'partial/admin_topnav.php'; ?>
        
        <div class="content-area">
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h2>Member Management</h2>
                <button class="btn" id="addUserBtn"><i class='bx bx-plus'></i> Add Member</button>
            </div>
            
            <div class="data-table">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($users && $users->num_rows > 0): ?>
                            <?php while($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($user['user_type'])); ?></td>
                                <td><span class="status active">Active</span></td>
                                <td>
                                    <button class="action-btn edit-btn edit-user-btn" 
                                            data-id="<?php echo htmlspecialchars($user['id']); ?>"
                                            data-fname="<?php echo htmlspecialchars($user['first_name']); ?>"
                                            data-lname="<?php echo htmlspecialchars($user['last_name']); ?>"
                                            data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                            data-type="<?php echo htmlspecialchars($user['user_type']); ?>">
                                        <i class='bx bxs-edit'></i> Edit
                                    </button>
                                    <button class="action-btn delete-btn delete-user-btn" data-id="<?php echo htmlspecialchars($user['id']); ?>">
                                        <i class='bx bxs-trash'></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include 'modals/add_user_modal.php'; ?>
    <?php include 'modals/edit_user_modal.php'; ?>
    <?php include 'modals/delete_modal.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="members.js"></script>
</body>
</html>