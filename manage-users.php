<?php
require_once('config.php');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get admin name from database
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['name'] ?? 'Admin';

// Handle user deletion
if(isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Prevent admin from deleting their own account
    if($delete_id == $user_id) {
        $_SESSION['user_error'] = "You cannot delete your own admin account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if($stmt->execute()) {
            // Also delete user's tasks and lists
            $conn->query("DELETE FROM tbl_tasks WHERE user_id = $delete_id");
            $conn->query("DELETE FROM tbl_lists WHERE user_id = $delete_id");
            
            $_SESSION['user_success'] = "User deleted successfully";
        } else {
            $_SESSION['user_error'] = "Error deleting user";
        }
    }
    
    header("Location: manage-users.php");
    exit();
}

// User role filter
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

// Get total user counts
$stats = [
    'total' => 0,
    'admin' => 0,
    'user' => 0
];

// Get total users
$query = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($query);
$stats['total'] = $result->fetch_assoc()['total'];

// Get admin count
$query = "SELECT COUNT(*) as admin_count FROM users WHERE role='admin'";
$result = $conn->query($query);
$stats['admin'] = $result->fetch_assoc()['admin_count'];

// Get regular user count
$query = "SELECT COUNT(*) as user_count FROM users WHERE role='user'";
$result = $conn->query($query);
$stats['user'] = $result->fetch_assoc()['user_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Task Manager</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
          --primary: #8C36D4;
          --primary-dark: #7929C4;
          --primary-light: #F0E5FA;
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
          font-size: 1.75rem;
          font-weight: 600;
          margin-bottom: 1.5rem;
          color: var(--dark);
          display: flex;
          align-items: center;
          gap: 0.5rem;
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

        /* === DASHBOARD STATS === */
        .stats-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
          gap: 1.5rem;
          margin-bottom: 2rem;
        }

        .stat-card {
          background-color: white;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          padding: 1.5rem;
          display: flex;
          flex-direction: column;
        }

        .stat-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 1rem;
        }

        .stat-title {
          color: var(--gray-600);
          font-size: 0.95rem;
          font-weight: 500;
        }

        .stat-icon {
          width: 40px;
          height: 40px;
          border-radius: var(--radius-sm);
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.25rem;
        }

        .total-users .stat-icon {
          background-color: var(--primary-light);
          color: var(--primary);
        }

        .admin-users .stat-icon {
          background-color: rgba(255, 59, 48, 0.1);
          color: var(--danger);
        }

        .regular-users .stat-icon {
          background-color: rgba(52, 199, 89, 0.1);
          color: var(--success);
        }

        .stat-value {
          font-size: 2rem;
          font-weight: 600;
          color: var(--dark);
          margin-bottom: 0.25rem;
        }

        .stat-desc {
          font-size: 0.9rem;
          color: var(--gray-600);
        }

        /* === USERS SECTION === */
        .users-container {
          background-color: white;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          overflow: hidden;
          margin-bottom: 2rem;
        }

        .users-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 1.25rem 1.5rem;
          border-bottom: 1px solid var(--gray-200);
        }

        .users-title {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          font-size: 1.1rem;
          font-weight: 600;
          color: var(--dark);
        }

        .users-title i {
          color: var(--primary);
        }

        .users-actions {
          display: flex;
          gap: 1rem;
          align-items: center;
        }

        .filter-form {
          display: flex;
          align-items: center;
          gap: 0.75rem;
        }

        .filter-form label {
          font-size: 0.9rem;
          color: var(--gray-700);
          white-space: nowrap;
        }

        .filter-dropdown {
          background-color: white;
          border: 1px solid var(--gray-300);
          border-radius: var(--radius);
          padding: 0.5rem 0.75rem;
          font-size: 0.9rem;
          color: var(--gray-800);
          outline: none;
          min-width: 120px;
        }

        .filter-dropdown:focus {
          border-color: var(--primary);
        }

        .btn {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          padding: 0.5rem 1rem;
          border-radius: var(--radius);
          font-weight: 500;
          font-size: 0.95rem;
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

        .btn-danger {
          background-color: var(--danger);
          color: white;
        }

        .btn-success {
          background-color: var(--success);
          color: white;
        }

        .btn-sm {
          padding: 0.35rem 0.75rem;
          font-size: 0.85rem;
        }

        /* === USERS TABLE === */
        .user-table {
          width: 100%;
          border-collapse: collapse;
        }

        .user-table th {
          text-align: left;
          padding: 1rem 1.5rem;
          font-weight: 600;
          color: var(--gray-700);
          background-color: var(--gray-100);
          border-bottom: 1px solid var(--gray-200);
        }

        .user-table td {
          padding: 1rem 1.5rem;
          border-bottom: 1px solid var(--gray-200);
        }

        .user-table tr:last-child td {
          border-bottom: none;
        }

        .user-name {
          font-weight: 500;
          color: var(--gray-800);
          display: flex;
          align-items: center;
          gap: 0.75rem;
        }

        .user-avatar-small {
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background-color: var(--primary-light);
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: bold;
          text-transform: uppercase;
          color: var(--primary);
          font-size: 0.85rem;
        }

        .user-email {
          color: var(--gray-600);
        }

        .role-badge {
          display: inline-block;
          padding: 0.2rem 0.5rem;
          border-radius: 999px;
          font-size: 0.75rem;
          font-weight: 500;
          text-transform: uppercase;
        }

        .role-admin {
          background-color: rgba(255, 59, 48, 0.1);
          color: var(--danger);
        }

        .role-user {
          background-color: rgba(52, 199, 89, 0.1);
          color: var(--success);
        }

        .action-buttons {
          display: flex;
          gap: 0.5rem;
        }

        /* Empty state */
        .empty-state {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          padding: 4rem 2rem;
          text-align: center;
        }

        .empty-icon {
          font-size: 3rem;
          color: var(--gray-300);
          margin-bottom: 1rem;
        }

        .empty-text {
          color: var(--gray-600);
          margin-bottom: 1.5rem;
          font-size: 1.1rem;
        }

        /* Responsive adaptations */
        @media (max-width: 992px) {
          .sidebar {
            display: none;
          }
        }

        @media (max-width: 768px) {
          .stats-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
          }
          
          .users-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
          }
          
          .users-actions {
            width: 100%;
            flex-wrap: wrap;
          }
          
          .filter-form {
            flex-wrap: wrap;
            width: 100%;
          }
          
          .filter-dropdown {
            flex-grow: 1;
          }
          
          .btn-primary {
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
          
          .stats-grid {
            grid-template-columns: 1fr;
          }
          
          .action-buttons {
            flex-direction: column;
            width: 100%;
          }
          
          .user-table th:nth-child(3),
          .user-table td:nth-child(3) {
            display: none;
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
                <div class="user-role">Admin</div>
            </div>
        </div>
    </header>

    <!-- App Container -->
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="admin_page.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
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
                    <li class="nav-item">
                        <a href="manage-users.php" class="active">
                            <i class="fas fa-users"></i>
                            Manage Users
                        </a>
                    </li>
                    
                    <div class="nav-section-title">SETTINGS</div>
                    <li class="nav-item">
                        <a href="profile.php">
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
                <i class="fas fa-users"></i>
                Manage Users
            </h1>
            
            <?php
            if(isset($_SESSION['user_success'])){
                echo '<div class="alert alert-success">' . $_SESSION['user_success'] . '</div>';
                unset($_SESSION['user_success']);
            }
            
            if(isset($_SESSION['user_error'])){
                echo '<div class="alert alert-danger">' . $_SESSION['user_error'] . '</div>';
                unset($_SESSION['user_error']);
            }
            ?>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card total-users">
                    <div class="stat-header">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                    <div class="stat-desc">All system users</div>
                </div>
                
                <div class="stat-card admin-users">
                    <div class="stat-header">
                        <div class="stat-title">Administrators</div>
                        <div class="stat-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['admin']; ?></div>
                    <div class="stat-desc">Admin accounts</div>
                </div>
                
                <div class="stat-card regular-users">
                    <div class="stat-header">
                        <div class="stat-title">Regular Users</div>
                        <div class="stat-icon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $stats['user']; ?></div>
                    <div class="stat-desc">Standard accounts</div>
                </div>
            </div>
            
            <!-- Users Section -->
            <div class="users-container">
                <div class="users-header">
                    <div class="users-title">
                        <i class="fas fa-user-group"></i>
                        All Users
                    </div>
                    
                    <div class="users-actions">
                        <form method="GET" action="" class="filter-form">
                            <label for="role">Filter by:</label>
                            <select name="role" id="role" class="filter-dropdown" onchange="this.form.submit()">
                                <option value="" <?php echo $role_filter == '' ? 'selected' : ''; ?>>All Roles</option>
                                <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Administrators</option>
                                <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Regular Users</option>
                            </select>
                        </form>
                        
                        <a href="add-user.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Add New User
                        </a>
                    </div>
                </div>
                
                <?php
                // Modify query based on role filter
                if (!empty($role_filter)) {
                    $query = "SELECT * FROM users WHERE role = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $role_filter);
                } else {
                    $query = "SELECT * FROM users";
                    $stmt = $conn->prepare($query);
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows > 0) {
                ?>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tasks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while($row = $result->fetch_assoc()) {
                            $id = $row['id'];
                            $name = $row['name'];
                            $email = $row['email'];
                            $role = $row['role'];
                            
                            // Get user's task count
                            $task_query = "SELECT COUNT(*) as task_count FROM tbl_tasks WHERE user_id = $id";
                            $task_result = $conn->query($task_query);
                            $task_count = $task_result->fetch_assoc()['task_count'];
                            
                            // Determine if current user is self
                            $is_current_user = ($id == $user_id);
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td>
                                <div class="user-name">
                                    <div class="user-avatar-small"><?php echo substr($name, 0, 1); ?></div>
                                    <?php echo $name; ?>
                                    <?php if($is_current_user): ?><small>(You)</small><?php endif; ?>
                                </div>
                            </td>
                            <td class="user-email"><?php echo $email; ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $role; ?>">
                                    <?php echo ucfirst($role); ?>
                                </span>
                            </td>
                            <td><?php echo $task_count; ?> tasks</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit-user.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if(!$is_current_user): ?>
                                    <a href="manage-users.php?delete_id=<?php echo $id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? All their tasks and lists will also be deleted.');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
                } else {
                ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash empty-icon"></i>
                    <p class="empty-text">No users found</p>
                    <a href="add-user.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Add New User
                    </a>
                </div>
                <?php
                }
                ?>
            </div>
        </main>
    </div>
</body>
</html>