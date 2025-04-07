<?php 
// Start the session
session_start();

// Include database configuration
require_once('config.php');

// Handle registration form submission
if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0){
        // Email already exists
        $_SESSION['register_error'] = "Email is already registered!";
        header("Location: register.php");
        exit();
    } else {
        // Insert new user
        $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
        
        if(mysqli_query($conn, $query)){
            // Registration successful
            header("Location: login.php");
            exit();
        } else {
            // Registration failed
            $_SESSION['register_error'] = "Registration failed. Please try again.";
            header("Location: register.php");
            exit();
        }
    }
}

// Handle login form submission
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        if(password_verify($password, $user['password'])){
            // Password is correct, create session
            $session_token = bin2hex(random_bytes(32));
            $user_id = $user['id'];
            $role = $user['role'];

            // Store session in database
            $query = "INSERT INTO sessions (user_id, session_token, created_at) VALUES ('$user_id', '$session_token', NOW())";
            mysqli_query($conn, $query);

            // Set session variables
            $_SESSION['session_token'] = $session_token;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role; 
            
            // Redirect based on role
            if($role == 'admin'){
                header("Location: admin_page.php");
                exit();
            } elseif($role == 'user') {
                header("Location: user_page.php");
                exit();
            } else {
                echo "Unknown role: " . $role;
                exit();
            }
        } else {
            // Password is incorrect
            $_SESSION['login_error'] = 'Incorrect email or password';
            header("Location: login.php");
            exit();
        }
    } else {
        // Email not found
        $_SESSION['login_error'] = 'Incorrect email or password';
        header("Location: login.php");
        exit();
    }
}
?>