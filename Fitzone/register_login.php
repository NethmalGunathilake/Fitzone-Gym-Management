<?php
session_start();
include "db.php";


if (isset($_POST['signUp'])) {
    $firstName = $conn->real_escape_string($_POST['fName']);
    $lastName = $conn->real_escape_string($_POST['lName']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $userType = $conn->real_escape_string($_POST['user_type']);
    
    
    $checkEmail = $conn->query("SELECT * FROM users WHERE email = '$email'");
    
    if ($checkEmail->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='register_login.php';</script>";
        exit();
    }
    
    $sql = "INSERT INTO users (first_name, last_name, email, password, user_type) 
            VALUES ('$firstName', '$lastName', '$email', '$password', '$userType')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration successful!'); window.location.href='register_login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location.href='register_login.php';</script>";
    }
}


if (isset($_POST['signIn'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
          
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['redirect_to'] = ''; 
            
           
            if ($user['user_type'] == 'admin') {
                $_SESSION['redirect_to'] = 'admin_dashboard.php';
            } else if ($user['user_type'] == 'staff') {
                $_SESSION['redirect_to'] = 'staff_dashboard.php';
            } else {
                $_SESSION['redirect_to'] = 'user.php';
            }
            
           
            echo '
            <div style="text-align:center; margin-top:50px;">
                <h2>Login successful! Redirecting to your dashboard...</h2>
                <p>If you are not redirected automatically, please <a href="'.$_SESSION['redirect_to'].'">click here</a>.</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = "'.$_SESSION['redirect_to'].'";
                }, 1500);
            </script>
            ';
            exit();
        } else {
            echo "<script>alert('Invalid email or password!'); window.location.href='register_login.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password!'); window.location.href='register_login.php';</script>";
    }
}


if (isset($_SESSION['redirect_to']) && !empty($_SESSION['redirect_to'])) {
    echo '
    <div style="text-align:center; margin-top:50px;">
        <h2>Please wait, redirecting to your dashboard...</h2>
        <p>If you are not redirected automatically, please <a href="'.$_SESSION['redirect_to'].'">click here</a>.</p>
    </div>
    <script>
        window.location.href = "'.$_SESSION['redirect_to'].'";
    </script>
    ';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=s, initial-scale=1.0">
    <title>Register login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/homebtn.css">


</head>
<body>

    <div class="container" id="signup" style="display:none;">
        <h1 class="form-title">Register</h1>
        <form method="post" action="register_login.php">
            
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fName" id="fName" placeholder="First Name" required>
                <label for="fname">First Name</label> 
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i> 
                <input type="text" name="lName" id="lName" placeholder="Last Name" required>
                <label for="lname">Last Name</label>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label for="password">Password</label>
             </div>

            <div class="form-group">
                <select name="user_type" id="" class="form-control">
                    <option value="user">User</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
             <input type="submit" class="btn" value="Sign Up" name="signUp">
        </form>

        <div class="links">
            <p>Already Have Account?</p>
            <button id="signInButton">Sign In</button>
        </div>
    </div>
    

       
    <div class="container" id="signIn">

       

        <h1 class="form-title">Sign In</h1>
        <form method="post" action="register_login.php">
           
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="login_email" placeholder="Email" required>
                <label for="login_email">Email</label>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="login_password" placeholder="Password" required>
                <label for="login_password">Password</label>
            </div>

             <p class="recover">
                <a href="#">Recover Password</a>
             </p>
             <input type="submit" class="btn" value="Sign In" name="signIn">
        </form>

        <div class="links">
            <p>Don't Have Account?</p>
            <button id="signUpButton">Sign Up</button>
        </div>
    </div>

    
    <script src="js/script2.js"></script>
</body>
</html>