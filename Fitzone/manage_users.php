<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: register_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($action === 'add') {
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_type = $conn->real_escape_string($_POST['user_type']);

        // Check if email exists
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $_SESSION['error'] = "Email already exists!";
        } else {
            $sql = "INSERT INTO users (first_name, last_name, email, password, user_type) 
                    VALUES ('$first_name', '$last_name', '$email', '$password', '$user_type')";
            if ($conn->query($sql)) {
                $_SESSION['message'] = "User added successfully!";
            } else {
                $_SESSION['error'] = "Error adding user: " . $conn->error;
            }
        }
    } elseif ($action === 'edit') {
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $user_type = $conn->real_escape_string($_POST['user_type']);

        // Check if email exists (excluding current user)
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $user_id");
        if ($check_email->num_rows > 0) {
            $_SESSION['error'] = "Email already exists!";
        } else {
            $sql = "UPDATE users SET 
                    first_name = '$first_name',
                    last_name = '$last_name',
                    email = '$email',
                    user_type = '$user_type'
                    WHERE id = $user_id";
            if ($conn->query($sql)) {
                $_SESSION['message'] = "User updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating user: " . $conn->error;
            }
        }
    } elseif ($action === 'delete') {
        // Prevent self-deletion
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account!";
        } else {
            $sql = "DELETE FROM users WHERE id = $user_id";
            if ($conn->query($sql)) {
                $_SESSION['message'] = "User deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting user: " . $conn->error;
            }
        }
    }

    header("Location: members.php");
    exit();
} else {
    header("Location: members.php");
    exit();
}
?>