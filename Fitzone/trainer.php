<?php
// Include your database connection
include "db.php";

// Create trainers table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    specialization VARCHAR(100)
)";

if ($conn->query($create_table_sql) === TRUE) {
    echo "Trainers table created successfully or already exists.<br>";
} else {
    echo "Error creating trainers table: " . $conn->error . "<br>";
    exit;
}

// Sample trainer data
$trainers = [
    
    ['Sophia', 'Kim', 'sophia.k@fitzone.com', 'Barre'],
    ['Daniel', 'Brown', 'daniel.b@fitzone.com', 'Weight Loss'],
    ['Maya', 'Nguyen', 'maya.n@fitzone.com', 'Functional Training'],
    ['Thomas', 'Jackson', 'thomas.j@fitzone.com', 'Senior Fitness'],
    ['Leila', 'Gupta', 'leila.g@fitzone.com', 'Pre/Post Natal'],
    ['Carlos', 'Hernandez', 'carlos.h@fitzone.com', 'Sports Conditioning']
];

// Prepare the statement
$stmt = $conn->prepare("INSERT INTO trainers (first_name, last_name, email, specialization) VALUES (?, ?, ?, ?)");

// Check if prepared statement was created successfully
if ($stmt) {
    // Bind parameters and execute for each trainer
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $specialization);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($trainers as $trainer) {
        $first_name = $trainer[0];
        $last_name = $trainer[1];
        $email = $trainer[2];
        $specialization = $trainer[3];
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
            echo "Error inserting trainer: " . $first_name . " " . $last_name . " - " . $stmt->error . "<br>";
        }
    }
    
    echo "Successfully added " . $success_count . " trainers.<br>";
    if ($error_count > 0) {
        echo "Failed to add " . $error_count . " trainers.<br>";
    }
    
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
echo "Done!";
?>