<?php
// Start the session at the beginning
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Task Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles CSS... */
        :root {
          --primary: #5468FF;
          --primary-dark: #4054EB;
          --primary-light: #E6E9FF;
          --primary-gradient: linear-gradient(135deg, #5468FF 0%, #4054EB 100%);
          --danger: #FF3B30;
          --light: #F2F2F7;
          --dark: #1C1C1E;
          --gray-100: #F2F2F7;
          --gray-200: #E5E5EA;
          --gray-300: #D1D1D6;
          --gray-400: #C7C7CC;
          --gray-500: #AEAEB2;
          --gray-600: #8E8E93;
          --gray-700: #636366;
          --gray-800: #48484A;
          --gray-900: #3A3A3C;
          --transition: all 0.2s ease;
          --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
          --shadow-lg: 0 12px 24px rgba(0, 0, 0, 0.12);
          --radius: 0.5rem;
          --radius-lg: 0.75rem;
        }

        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: 'Poppins', sans-serif;
        }

        body {
          background-color: #F5F7FA;
          color: var(--dark);
          min-height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 2rem 1rem;
        }

        /* Error message style */
        .error-message {
            background-color: #FFEBEE;
            color: #D32F2F;
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        /* Container */
        .auth-container {
          display: flex;
          max-width: 1000px;
          width: 100%;
          box-shadow: var(--shadow-lg);
          border-radius: var(--radius-lg);
          overflow: hidden;
          background-color: white;
          height: 650px;
        }

        /* Left side */
        .auth-banner {
          background: var(--primary-gradient);
          flex: 1;
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          color: white;
          padding: 2rem;
          text-align: center;
        }

        .auth-banner-logo {
          font-size: 3rem;
          margin-bottom: 2rem;
        }

        .auth-banner-title {
          font-size: 1.75rem;
          font-weight: 600;
          margin-bottom: 1rem;
        }

        .auth-banner-subtitle {
          font-size: 1rem;
          opacity: 0.9;
          max-width: 80%;
          line-height: 1.6;
        }

        .auth-banner-features {
          margin-top: 2rem;
          list-style: none;
          width: 100%;
          max-width: 300px;
        }

        .auth-banner-feature {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          margin-bottom: 1rem;
          text-align: left;
        }

        .auth-banner-feature i {
          font-size: 1.25rem;
          color: rgba(255, 255, 255, 0.9);
        }

        /* Right side - form */
        .auth-form {
          flex: 1;
          padding: 3rem;
          display: flex;
          flex-direction: column;
          justify-content: center;
        }

        .auth-form-header {
          margin-bottom: 2rem;
        }

        .auth-form-title {
          font-size: 1.75rem;
          font-weight: 600;
          color: var(--dark);
          margin-bottom: 0.5rem;
        }

        .auth-form-subtitle {
          color: var(--gray-600);
          font-size: 0.95rem;
        }

        .form-group {
          margin-bottom: 1.25rem;
        }

        .form-label {
          display: block;
          margin-bottom: 0.5rem;
          font-weight: 500;
          color: var(--gray-800);
          font-size: 0.9rem;
        }

        .form-control {
          width: 100%;
          padding: 0.85rem 1rem;
          font-size: 1rem;
          background-color: var(--gray-100);
          border: 1px solid var(--gray-200);
          border-radius: var(--radius);
          transition: var(--transition);
        }

        .form-control:focus {
          outline: none;
          border-color: var(--primary);
          background-color: white;
          box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-control::placeholder {
          color: var(--gray-500);
        }

        .input-group {
          position: relative;
        }

        .input-group .form-control {
          padding-left: 3rem;
        }

        .input-group-icon {
          position: absolute;
          left: 1rem;
          top: 50%;
          transform: translateY(-50%);
          color: var(--gray-500);
        }

        .btn {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          padding: 0.85rem 1.5rem;
          border-radius: var(--radius);
          font-weight: 500;
          font-size: 1rem;
          text-decoration: none;
          cursor: pointer;
          transition: var(--transition);
          gap: 0.5rem;
          border: none;
          width: 100%;
        }

        .btn-primary {
          background-color: var(--primary);
          color: white;
        }

        .btn-primary:hover {
          background-color: var(--primary-dark);
        }

        .auth-form-footer {
          margin-top: 1.5rem;
          display: flex;
          justify-content: center;
          font-size: 0.95rem;
          color: var(--gray-600);
        }

        .auth-form-footer a {
          color: var(--primary);
          font-weight: 500;
          margin-left: 0.3rem;
          text-decoration: none;
        }

        .auth-form-footer a:hover {
          text-decoration: underline;
        }

        /* Responsive styles */
        @media (max-width: 992px) {
          .auth-container {
            flex-direction: column;
            height: auto;
            max-width: 500px;
          }
          
          .auth-banner, .auth-form {
            flex: none;
            width: 100%;
          }
          
          .auth-banner {
            padding: 2rem 1rem;
          }
          
          .auth-form {
            padding: 2rem 1.5rem;
          }
        }

        @media (max-width: 576px) {
          .auth-form {
            padding: 1.5rem 1rem;
          }
          
          .auth-banner {
            padding: 1.5rem 1rem;
          }
          
          .auth-banner-features {
            display: none;
          }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left side - Banner/Illustration -->
        <div class="auth-banner">
            <div class="auth-banner-logo">
                <i class="fas fa-tasks"></i>
            </div>
            <h1 class="auth-banner-title">Task Manager</h1>
            <p class="auth-banner-subtitle">Join our community and get access to the best task management system</p>
            
            <ul class="auth-banner-features">
                <li class="auth-banner-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Organize tasks efficiently</span>
                </li>
                <li class="auth-banner-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Set priorities and deadlines</span>
                </li>
                <li class="auth-banner-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Track completed tasks</span>
                </li>
                <li class="auth-banner-feature">
                    <i class="fas fa-check-circle"></i>
                    <span>Collaborate with team members</span>
                </li>
            </ul>
        </div>
        
        <!-- Right side - Register form -->
        <div class="auth-form">
            <div class="auth-form-header">
                <h2 class="auth-form-title">Create Account</h2>
                <p class="auth-form-subtitle">Register with your email to get started</p>
            </div>
            
            <?php 
            // Display error message if it exists
            if(isset($_SESSION['register_error'])) {
                echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i>' . $_SESSION['register_error'] . '</div>';
                unset($_SESSION['register_error']);
            }
            ?>
            
            <form action="login_register.php" method="post">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <i class="fas fa-user input-group-icon"></i>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-group-icon"></i>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-group-icon"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required>
                    </div>
                </div>
                
                <!-- Hidden input for user role -->
                <input type="hidden" name="role" value="user">
                
                <button type="submit" name="register" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
                
                <div class="auth-form-footer">
                    <span>Already have an account?</span>
                    <a href="login.php">Login Here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>