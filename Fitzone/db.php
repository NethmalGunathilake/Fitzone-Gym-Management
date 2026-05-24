<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fitzone";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Create admin account if not exists
$adminEmail = "admin@gmail.com";
$checkAdmin = $conn->query("SELECT * FROM users WHERE email = '$adminEmail'");

if ($checkAdmin->num_rows == 0) {
    $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (first_name, last_name, email, password, user_type) 
            VALUES ('Admin', 'FitZone', '$adminEmail', '$adminPassword', 'admin')";
    
    if ($conn->query($sql) !== TRUE) {
        error_log("Error creating admin account: " . $conn->error);
    }
}



?>