<?php
require_once('config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get user details from database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['name'];
$email = $user['email'];

// Handle form submission
if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password_change = isset($_POST['change_password']) && $_POST['change_password'] == '1';
    
    // Check if email already exists and doesn't belong to this user
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $error = "Email is already registered. Please use a different email.";
    } else {
        // Verify current password
        if(!password_verify($_POST['current_password'], $user['password'])) {
            $error = "Current password is incorrect.";
        } else {
            if($password_change && !empty($_POST['new_password'])) {
                // Update with new password
                $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $password, $user_id);
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $user_id);
            }
            
            if($stmt->execute()) {
                $success = "Profile updated successfully.";
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $username = $user['name'];
                $email = $user['email'];
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        }
    }
}

// Get user stats
$stats = [
    'total' => 0,
    'pending' => 0,
    'completed' => 0
];

// Get total tasks
$query = "SELECT COUNT(*) as total FROM tbl_tasks WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total'] = $result->fetch_assoc()['total'];

// Get pending tasks
$query = "SELECT COUNT(*) as pending FROM tbl_tasks WHERE user_id = ? AND status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['pending'] = $result->fetch_assoc()['pending'];

// Get completed tasks
$query = "SELECT COUNT(*) as completed FROM tbl_tasks WHERE user_id = ? AND status = 'complited'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['completed'] = $result->fetch_assoc()['completed'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | Task Manager</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
          --primary: <?php echo $role === 'admin' ? '#8C36D4' : '#5468FF'; ?>;
          --primary-dark: <?php echo $role === 'admin' ? '#7929C4' : '#4054EB'; ?>;
          --primary-light: <?php echo $role === 'admin' ? '#F0E5FA' : '#E6E9FF'; ?>;
          --success: #34C759;
          --danger: #FF3B30;
          --warning: #FFCC00;
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
          --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
          --shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
          --shadow-lg: 0 5px 15px rgba(0, 0, 0, 0.05);
          --radius-sm: 0.25rem;
          --radius: 0.5rem;
          --radius-lg: 0.75rem;
        }

        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        body {
          background-color: #F5F7FA;
          color: var(--dark);
          min-height: 100vh;
          display: flex;
          flex-direction: column;
        }

        /* === HEADER STYLES === */
        .header {
          background-color: var(--primary);
          color: white;
          padding: 1rem 2rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
          height: 70px;
          position: sticky;
          top: 0;
          z-index: 100;
        }

        .brand {
          display: flex;
          align-items: center;
          gap: 0.75rem;
        }

        .brand h1 {
          font-size: 1.5rem;
          letter-spacing: 0.5px;
          font-weight: 600;
          margin: 0;
          white-space: nowrap;
        }

        .brand-icon {
          font-size: 1.75rem;
        }

        .user-profile {
          display: flex;
          align-items: center;
          gap: 1rem;
        }

        .user-avatar {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background-color: rgba(255, 255, 255, 0.3);
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: bold;
          text-transform: uppercase;
        }

        .user-info {
          text-align: right;
        }

        .user-name {
          font-weight: 600;
          margin-bottom: 0.1rem;
        }

        .user-role {
          font-size: 0.8rem;
          opacity: 0.9;
        }

        /* === MAIN CONTENT LAYOUT === */
        .app-container {
          display: flex;
          flex-grow: 1;
        }

        /* === SIDEBAR NAVIGATION === */
        .sidebar {
          background-color: white;
          width: 250px;
          padding: 2rem 0;
          height: calc(100vh - 70px);
          position: sticky;
          top: 70px;
          border-right: 1px solid var(--gray-200);
        }

        .nav-menu {
          list-style: none;
        }

        .nav-item {
          margin-bottom: 0.5rem;
        }

        .nav-item a {
          display: flex;
          align-items: center;
          padding: 0.75rem 1.5rem;
          color: var(--gray-700);
          text-decoration: none;
          font-weight: 500;
          transition: var(--transition);
          gap: 0.75rem;
        }

        .nav-item a:hover {
          background-color: var(--gray-100);
          color: var(--primary);
        }

        .nav-item a.active {
          border-left: 3px solid var(--primary);
          background-color: var(--primary-light);
          color: var(--primary);
        }

        .nav-item a.active i {
          color: var(--primary);
        }

        .nav-item i {
          font-size: 1.25rem;
          color: var(--gray-600);
          transition: var(--transition);
          width: 20px;
          text-align: center;
        }

        .nav-section-title {
          font-size: 0.75rem;
          text-transform: uppercase;
          letter-spacing: 1px;
          color: var(--gray-500);
          margin: 1.5rem 1.5rem 0.75rem;
          font-weight: 600;
        }

        /* === MAIN CONTENT AREA === */
        .main-content {
          flex-grow: 1;
          padding: 2rem;
          max-width: 1200px;
          margin: 0 auto;
        }

        .page-title {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          font-size: 1.75rem;
          font-weight: 600;
          margin-bottom: 1.5rem;
          color: var(--dark);
        }

        .page-title i {
          color: var(--primary);
        }

        /* Alert message styling */
        .alert {
          padding: 1rem;
          border-radius: var(--radius);
          margin-bottom: 1.5rem;
          font-weight: 500;
        }

        .alert-success {
          background-color: rgba(52, 199, 89, 0.1);
          color: var(--success);
          border: 1px solid rgba(52, 199, 89, 0.2);
        }

        .alert-danger {
          background-color: rgba(255, 59, 48, 0.1);
          color: var(--danger);
          border: 1px solid rgba(255, 59, 48, 0.2);
        }

        /* === PROFILE SECTION === */
        .profile-container {
          display: grid;
          grid-template-columns: 1fr 2fr;
          gap: 1.5rem;
        }

        /* === USER CARD === */
        .user-card {
          background-color: white;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          overflow: hidden;
        }

        .user-card-header {
          background-color: var(--primary-light);
          padding: 2rem 1.5rem;
          text-align: center;
          border-bottom: 1px solid var(--gray-200);
        }

        .user-card-avatar {
          width: 100px;
          height: 100px;
          border-radius: 50%;
          background-color: var(--primary);
          margin: 0 auto 1rem;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 2.5rem;
          font-weight: bold;
          color: white;
          text-transform: uppercase;
        }

        .user-card-name {
          font-size: 1.25rem;
          font-weight: 600;
          color: var(--dark);
          margin-bottom: 0.25rem;
        }

        .user-card-role {
          display: inline-block;
          padding: 0.25rem 0.75rem;
          border-radius: 999px;
          font-size: 0.8rem;
          font-weight: 500;
          text-transform: uppercase;
          background-color: var(--primary);
          color: white;
        }

        .user-card-body {
          padding: 1.5rem;
        }

        .user-stats {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 1rem;
          text-align: center;
        }

        .user-stat-item {
          padding: 0.75rem;
        }

        .user-stat-value {
          font-size: 1.5rem;
          font-weight: 600;
          color: var(--primary);
          margin-bottom: 0.25rem;
        }

        .user-stat-label {
          font-size: 0.85rem;
          color: var(--gray-600);
        }

        /* === FORM CONTAINER === */
        .form-container {
          background-color: white;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          overflow: hidden;
        }

        .form-header {
          background-color: var(--primary-light);
          padding: 1.5rem;
          border-bottom: 1px solid var(--gray-200);
        }

        .form-title {
          color: var(--primary);
          font-size: 1.2rem;
          font-weight: 600;
          margin: 0;
          display: flex;
          align-items: center;
          gap: 0.5rem;
        }

        .form-content {
          padding: 2rem;
        }

        .form-group {
          margin-bottom: 1.5rem;
        }

        .form-label {
          display: block;
          margin-bottom: 0.5rem;
          font-weight: 500;
          color: var(--gray-800);
        }

        .form-control {
          width: 100%;
          padding: 0.75rem 1rem;
          font-size: 1rem;
          background-color: white;
          border: 1px solid var(--gray-300);
          border-radius: var(--radius);
          transition: var(--transition);
        }

        .form-control:focus {
          outline: none;
          border-color: var(--primary);
          box-shadow: 0 0 0 3px var(--primary-light);
        }

        .form-control::placeholder {
          color: var(--gray-500);
        }

        .form-check {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          margin-bottom: 1rem;
        }

        .form-check-input {
          width: 18px;
          height: 18px;
          cursor: pointer;
        }

        .form-check-label {
          color: var(--gray-700);
          cursor: pointer;
        }

        .password-fields {
          margin-top: 1rem;
          padding-top: 1rem;
          border-top: 1px dashed var(--gray-300);
          display: none;
        }

        .password-fields.show {
          display: block;
        }

        .form-footer {
          padding: 1.5rem 2rem;
          border-top: 1px solid var(--gray-200);
          display: flex;
          justify-content: flex-end;
          gap: 1rem;
        }

        /* === BUTTONS === */
        .btn {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          padding: 0.75rem 1.5rem;
          border-radius: var(--radius);
          font-weight: 500;
          font-size: 1rem;
          text-decoration: none;
          cursor: pointer;
          transition: var(--transition);
          gap: 0.5rem;
          border: none;
        }

        .btn-primary {
          background-color: var(--primary);
          color: white;
        }

        .btn-primary:hover {
          background-color: var(--primary-dark);
        }

        .btn-outline {
          background-color: transparent;
          border: 1px solid var(--gray-300);
          color: var(--gray-700);
        }

        .btn-outline:hover {
          border-color: var(--primary);
          color: var(--primary);
        }

        /* Security info */
        .security-info {
          margin-top: 1.5rem;
          padding-top: 1.5rem;
          border-top: 1px solid var(--gray-200);
        }

        .security-info-title {
          font-size: 1rem;
          font-weight: 600;
          color: var(--dark);
          margin-bottom: 0.75rem;
        }

        .security-info p {
          color: var(--gray-600);
          font-size: 0.9rem;
          margin-bottom: 0.5rem;
        }

        /* Responsive adaptations */
        @media (max-width: 992px) {
          .sidebar {
            display: none;
          }
        }

        @media (max-width: 768px) {
          .profile-container {
            grid-template-columns: 1fr;
          }
          
          .form-footer {
            flex-direction: column;
          }
          
          .btn {
            width: 100%;
          }
        }

        @media (max-width: 576px) {
          .header {
            padding: 1rem;
          }
          
          .brand h1 {
            font-size: 1.25rem;
          }
          
          .main-content {
            padding: 1.5rem 1rem;
          }
          
          .form-content {
            padding: 1.5rem;
          }
          
          .user-stats {
            grid-template-columns: 1fr;
          }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="brand">
            <i class="fas fa-bars brand-icon"></i>
            <h1>Task Manager</h1>
        </div>
        <div class="user-profile">
            <div class="user-avatar"><?php echo substr($username, 0, 1); ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo $username; ?></div>
                <div class="user-role"><?php echo ucfirst($role); ?></div>
            </div>
        </div>
    </header>

    <!-- App Container -->
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav>
                <ul class="nav-menu">
                    <?php if($role == 'admin'): ?>
                    <li class="nav-item">
                        <a href="admin_page.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a href="user_page.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a href="done.php">
                            <i class="fas fa-check-circle"></i>
                            Completed Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-list.php">
                            <i class="fas fa-folder"></i>
                            Manage Lists
                        </a>
                    </li>
                    
                    <?php if($role == 'admin'): ?>
                    <li class="nav-item">
                        <a href="manage-users.php">
                            <i class="fas fa-users"></i>
                            Manage Users
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <div class="nav-section-title">SETTINGS</div>
                    <li class="nav-item">
                        <a href="profile.php" class="active">
                            <i class="fas fa-user-gear"></i>
                            Profile Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <h1 class="page-title">
                <i class="fas fa-user-gear"></i>
                Profile Settings
            </h1>
            
            <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="profile-container">
                <!-- User Info Card -->
                <div class="user-card">
                    <div class="user-card-header">
                        <div class="user-card-avatar"><?php echo substr($username, 0, 1); ?></div>
                        <h2 class="user-card-name"><?php echo $username; ?></h2>
                        <span class="user-card-role"><?php echo ucfirst($role); ?></span>
                    </div>
                    
                    <div class="user-card-body">
                        <div class="user-stats">
                            <div class="user-stat-item">
                                <div class="user-stat-value"><?php echo $stats['total']; ?></div>
                                <div class="user-stat-label">Total Tasks</div>
                            </div>
                            
                            <div class="user-stat-item">
                                <div class="user-stat-value"><?php echo $stats['pending']; ?></div>
                                <div class="user-stat-label">Pending</div>
                            </div>
                            
                            <div class="user-stat-item">
                                <div class="user-stat-value"><?php echo $stats['completed']; ?></div>
                                <div class="user-stat-label">Completed</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Form -->
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">
                            <i class="fas fa-pen-to-square"></i>
                            Edit Profile Information
                        </h2>
                    </div>
                    
                    <form action="" method="post">
                        <div class="form-content">
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            
                            <div class="security-info">
                                <h3 class="security-info-title">Security Information</h3>
                                
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter your current password" required>
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" id="change_password" name="change_password" value="1" class="form-check-input">
                                    <label for="change_password" class="form-check-label">I want to change my password</label>
                                </div>
                                
                                <div class="password-fields" id="password_fields">
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password">
                                    </div>
                                </div>
                                
                                <p>For security reasons, you need to enter your current password to save any changes to your profile.</p>
                            </div>
                        </div>
                        
                        <div class="form-footer">
                            <a href="<?php echo $role === 'admin' ? 'admin_page.php' : 'user_page.php'; ?>" class="btn btn-outline">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle password fields
        document.addEventListener('DOMContentLoaded', function() {
            const changePasswordCheckbox = document.getElementById('change_password');
            const passwordFields = document.getElementById('password_fields');
            
            changePasswordCheckbox.addEventListener('change', function() {
                if(this.checked) {
                    passwordFields.classList.add('show');
                } else {
                    passwordFields.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>