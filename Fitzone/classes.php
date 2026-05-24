<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: register_login.php");
    exit();
}

// Get current user data from session
$current_user = [
    'first_name' => isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Admin',
    'last_name' => isset($_SESSION['last_name']) ? $_SESSION['last_name'] : 'User'
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new class
    if (isset($_POST['add_class'])) {
        $class_name = $_POST['class_name'];
        $description = $_POST['description'];
        $schedule = $_POST['schedule'];
        $duration = $_POST['duration'];
        $trainer_id = $_POST['trainer_id'];
        $max_capacity = $_POST['max_capacity'];
        
        $sql = "INSERT INTO classes (class_name, description, schedule, duration, trainer_id, max_capacity) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiii", $class_name, $description, $schedule, $duration, $trainer_id, $max_capacity);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Class added successfully!";
        } else {
            $_SESSION['error'] = "Error adding class: " . $stmt->error;
        }
        
        $stmt->close();
        header("Location: classes.php");
        exit();
    }
    
    // Update class
    if (isset($_POST['update_class'])) {
        $class_id = $_POST['class_id'];
        $class_name = $_POST['class_name'];
        $description = $_POST['description'];
        $schedule = $_POST['schedule'];
        $duration = $_POST['duration'];
        $trainer_id = $_POST['trainer_id'];
        $max_capacity = $_POST['max_capacity'];
        
        $sql = "UPDATE classes SET 
                class_name = ?, 
                description = ?, 
                schedule = ?, 
                duration = ?, 
                trainer_id = ?, 
                max_capacity = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiiii", $class_name, $description, $schedule, $duration, $trainer_id, $max_capacity, $class_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Class updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating class: " . $stmt->error;
        }
        
        $stmt->close();
        header("Location: classes.php");
        exit();
    }
    
    // Delete class
    if (isset($_POST['delete_class'])) {
        $class_id = $_POST['class_id'];
        
        $sql = "DELETE FROM classes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $class_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Class deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting class: " . $stmt->error;
        }
        
        $stmt->close();
        header("Location: classes.php");
        exit();
    }
}

// Get all classes with trainer names
$classes = $conn->query("
    SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as trainer_name 
    FROM classes c
    LEFT JOIN trainers t ON c.trainer_id = t.id
    ORDER BY c.schedule ASC
");
// Get all trainers for the dropdown
$trainers = $conn->query("
    SELECT id, first_name, last_name, specialization 
    FROM trainers
    ORDER BY first_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes Management | FitZone</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Additional styles for the classes page */
        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .class-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            position: relative;
        }
        
        .class-card h3 {
            margin-top: 0;
            color: #333;
        }
        
        .class-card p {
            color: #666;
            margin: 5px 0;
        }
        
        .class-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 0.9em;
            color: #777;
        }
        
        .class-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .class-actions button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .edit-btn {
            background: #4CAF50;
            color: white;
        }
        
        .delete-btn {
            background: #f44336;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 600px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-primary {
            background: #4e73df;
            color: white;
        }
        
        .btn-primary:hover {
            background: #375dd0;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #777;
        }
        
        .empty-state i {
            font-size: 3em;
            margin-bottom: 10px;
            color: #ddd;
        }
        
        .filter-bar {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 200px;
        }
        
        .add-class-btn {
            background: #4e73df;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .add-class-btn:hover {
            background: #375dd0;
        }

        .class-date {
            color: #e74a3b;
            font-weight: bold;
        }

        .class-capacity {
            background: #f8f9fc;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }

        .confirm-delete-modal p {
            text-align: center;
            margin: 20px 0;
            font-size: 1.1em;
        }

        .confirm-delete-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .confirm-delete-actions button {
            padding: 8px 20px;
        }

        .cancel-btn {
            background: #858796;
            color: white;
        }

        .delete-confirm-btn {
            background: #e74a3b;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="menu-toggle">
        <i class='bx bx-menu'></i>
    </div>
    
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="/api/placeholder/40/40" alt="Logo">
                <h3>FitZone Admin</h3>
            </div>
            
            <div class="sidebar-menu">
                <h3>Main</h3>
                <ul>
                    <li><a href="admin_dashboard.php"><i class='bx bxs-dashboard'></i> <span>Dashboard</span></a></li>
                    <li><a href="members.php"><i class='bx bxs-user'></i> <span>Members</span></a></li>
                    <li><a href="classes.php" class="active"><i class='bx bxs-calendar'></i> <span>Classes</span></a></li>
                </ul>
                
                <h3>Account</h3>
                <ul>
                    <li><a href="logout.php"><i class='bx bxs-log-out'></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Nav -->
            <div class="top-nav">
                <div class="user-profile">
                    <div class="notification-icon">
                        <i class='bx bxs-bell'></i>
                        <span class="badge">3</span>
                    </div>
                    
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></h4>
                        <p>Admin</p>
                    </div>
                    
                    <img src="/api/placeholder/40/40" alt="User">
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area">
                <!-- Display Messages -->
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
                    <h2>Classes Management</h2>
                </div>
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <input type="text" id="searchClasses" class="search-input" placeholder="Search classes...">
                    <button id="openAddClassModal" class="add-class-btn">
                        <i class='bx bx-plus'></i> Add New Class
                    </button>
                </div>
                
                <!-- Classes Grid -->
                <div class="class-grid" id="classesGrid">
                    <?php if ($classes->num_rows > 0): ?>
                        <?php while($class = $classes->fetch_assoc()): ?>
                            <div class="class-card">
                                <h3><?php echo htmlspecialchars($class['class_name']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($class['description'], 0, 100)) . (strlen($class['description']) > 100 ? '...' : ''); ?></p>
                                <p class="class-date">
                                    <i class='bx bx-calendar'></i>
                                    <?php echo date('M j, Y, g:i A', strtotime($class['schedule'])); ?>
                                </p>
                                <p><i class='bx bx-time'></i> <?php echo $class['duration']; ?> minutes</p>
                                <p><i class='bx bx-user'></i> Trainer: <?php echo !empty($class['trainer_name']) ? htmlspecialchars($class['trainer_name']) : 'Not Assigned'; ?></p>
                                <div class="class-meta">
                                    <span class="class-capacity">Capacity: <?php echo $class['max_capacity']; ?></span>
                                </div>
                                <div class="class-actions">
                                    <button class="edit-btn" onclick="openEditModal(<?php echo $class['id']; ?>, '<?php echo addslashes($class['class_name']); ?>', '<?php echo addslashes($class['description']); ?>', '<?php echo date('Y-m-d\TH:i', strtotime($class['schedule'])); ?>', <?php echo $class['duration']; ?>, <?php echo $class['trainer_id'] ?: 'null'; ?>, <?php echo $class['max_capacity']; ?>)">
                                        Edit
                                    </button>
                                    <button class="delete-btn" onclick="openDeleteModal(<?php echo $class['id']; ?>, '<?php echo addslashes($class['class_name']); ?>')">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class='bx bx-calendar-x'></i>
                            <h3>No Classes Yet</h3>
                            <p>Start by adding your first fitness class</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Class Modal -->
    <div id="addClassModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2>Add New Class</h2>
            <form action="classes.php" method="POST">
                <div class="form-group">
                    <label for="class_name">Class Name</label>
                    <input type="text" id="class_name" name="class_name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="schedule">Schedule</label>
                    <input type="datetime-local" id="schedule" name="schedule" required>
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration (minutes)</label>
                    <input type="number" id="duration" name="duration" min="5" max="300" required>
                </div>
                
                <div class="form-group">
                <label for="trainer_id">Trainer</label>
                <select id="trainer_id" name="trainer_id">
                    <option value="">-- Select Trainer --</option>
                    <?php 
                    // Reset the result pointer to the beginning
                    $trainers->data_seek(0);
                    while($trainer = $trainers->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $trainer['id']; ?>">
                            <?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?> 
                            <?php if(!empty($trainer['specialization'])): ?>
                                - <?php echo htmlspecialchars($trainer['specialization']); ?>
                            <?php endif; ?>
                        </option>
                    <?php endwhile; ?>
                    <?php $trainers->data_seek(0); // Reset again for the edit modal ?>
                </select>
            </div>
            
                
                <div class="form-group">
                    <label for="max_capacity">Maximum Capacity</label>
                    <input type="number" id="max_capacity" name="max_capacity" min="1" max="100" required>
                </div>
                
                <button type="submit" name="add_class" class="btn btn-primary">Add Class</button>
            </form>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div id="editClassModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Class</h2>
            <form action="classes.php" method="POST">
                <input type="hidden" id="edit_class_id" name="class_id">
                
                <div class="form-group">
                    <label for="edit_class_name">Class Name</label>
                    <input type="text" id="edit_class_name" name="class_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_schedule">Schedule</label>
                    <input type="datetime-local" id="edit_schedule" name="schedule" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_duration">Duration (minutes)</label>
                    <input type="number" id="edit_duration" name="duration" min="5" max="300" required>
                </div>
                
                <div class="form-group">
            <label for="edit_trainer_id">Trainer</label>
            <select id="edit_trainer_id" name="trainer_id">
                <option value="">-- Select Trainer --</option>
                <?php while($trainer = $trainers->fetch_assoc()): ?>
                    <option value="<?php echo $trainer['id']; ?>">
                        <?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?>
                        <?php if(!empty($trainer['specialization'])): ?>
                            - <?php echo htmlspecialchars($trainer['specialization']); ?>
                        <?php endif; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
                
                <div class="form-group">
                    <label for="edit_max_capacity">Maximum Capacity</label>
                    <input type="number" id="edit_max_capacity" name="max_capacity" min="1" max="100" required>
                </div>
                
                <button type="submit" name="update_class" class="btn btn-primary">Update Class</button>
            </form>
        </div>
    </div>

    <!-- Delete Class Modal -->
    <div id="deleteClassModal" class="modal">
        <div class="modal-content confirm-delete-modal">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>Delete Class</h2>
            <p>Are you sure you want to delete <span id="deleteClassName"></span>?</p>
            <div class="confirm-delete-actions">
                <button class="btn cancel-btn" onclick="closeDeleteModal()">Cancel</button>
                <form action="classes.php" method="POST">
                    <input type="hidden" id="delete_class_id" name="class_id">
                    <button type="submit" name="delete_class" class="btn delete-confirm-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
        // Class Management JavaScript
        const addClassModal = document.getElementById("addClassModal");
        const editClassModal = document.getElementById("editClassModal");
        const deleteClassModal = document.getElementById("deleteClassModal");
        const searchInput = document.getElementById("searchClasses");
        
        // Open Add Class Modal
        document.getElementById("openAddClassModal").onclick = function() {
            addClassModal.style.display = "block";
        }
        
        // Close Add Class Modal
        function closeAddModal() {
            addClassModal.style.display = "none";
        }
        
        // Open Edit Class Modal
        function openEditModal(id, name, description, schedule, duration, trainer_id, capacity) {
            document.getElementById("edit_class_id").value = id;
            document.getElementById("edit_class_name").value = name;
            document.getElementById("edit_description").value = description;
            document.getElementById("edit_schedule").value = schedule;
            document.getElementById("edit_duration").value = duration;
            
            const trainerSelect = document.getElementById("edit_trainer_id");
            if (trainer_id) {
                trainerSelect.value = trainer_id;
            } else {
                trainerSelect.value = "";
            }
            
            document.getElementById("edit_max_capacity").value = capacity;
            
            editClassModal.style.display = "block";
        }
        
        // Close Edit Class Modal
        function closeEditModal() {
            editClassModal.style.display = "none";
        }
        
        // Open Delete Class Modal
        function openDeleteModal(id, name) {
            document.getElementById("delete_class_id").value = id;
            document.getElementById("deleteClassName").textContent = name;
            deleteClassModal.style.display = "block";
        }
        
        // Close Delete Class Modal
        function closeDeleteModal() {
            deleteClassModal.style.display = "none";
        }
        
        // Search Classes
        searchInput.addEventListener("input", function() {
            const searchTerm = this.value.toLowerCase();
            const classCards = document.querySelectorAll(".class-card");
            
            classCards.forEach(card => {
                const className = card.querySelector("h3").textContent.toLowerCase();
                const description = card.querySelectorAll("p")[0].textContent.toLowerCase();
                const trainer = card.querySelectorAll("p")[3].textContent.toLowerCase();
                
                if (className.includes(searchTerm) || description.includes(searchTerm) || trainer.includes(searchTerm)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == addClassModal) {
                addClassModal.style.display = "none";
            }
            if (event.target == editClassModal) {
                editClassModal.style.display = "none";
            }
            if (event.target == deleteClassModal) {
                deleteClassModal.style.display = "none";
            }
        }
    </script>
</body>
</html>