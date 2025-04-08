<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get user name from database
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['name'] ?? ($role === 'admin' ? 'Admin' : 'User');

// Count completed tasks
if($role == 'admin') {
    $query = "SELECT COUNT(*) as total FROM tbl_tasks WHERE status='complited'";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT COUNT(*) as total FROM tbl_tasks WHERE (user_id = ? OR user_id IS NULL) AND status='complited'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$completed_count = $result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Tasks | Task Manager</title>
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
          color: var(--success);
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

        /* === COMPLETED TASKS SECTION === */
        .tasks-container {
          background-color: white;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          overflow: hidden;
          margin-bottom: 2rem;
        }

        .tasks-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 1.25rem 1.5rem;
          border-bottom: 1px solid var(--gray-200);
        }

        .tasks-title {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          font-size: 1.1rem;
          font-weight: 600;
          color: var(--dark);
        }

        .tasks-title i {
          color: var(--success);
        }

        .tasks-count {
          background-color: var(--success);
          color: white;
          font-size: 0.85rem;
          padding: 0.15rem 0.5rem;
          border-radius: 999px;
          font-weight: 500;
        }

        /* === TASKS TABLE === */
        .task-table {
          width: 100%;
          border-collapse: collapse;
        }

        .task-table th {
          text-align: left;
          padding: 1rem 1.5rem;
          font-weight: 600;
          color: var(--gray-700);
          background-color: var(--gray-100);
          border-bottom: 1px solid var(--gray-200);
        }

        .task-table td {
          padding: 1rem 1.5rem;
          border-bottom: 1px solid var(--gray-200);
        }

        .task-table tr:last-child td {
          border-bottom: none;
        }

        .task-name {
          font-weight: 500;
          color: var(--gray-800);
        }

        .task-description {
          color: var(--gray-600);
          max-width: 250px;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }

        .priority-badge {
          display: inline-block;
          padding: 0.2rem 0.5rem;
          border-radius: 999px;
          font-size: 0.75rem;
          font-weight: 500;
          text-transform: uppercase;
        }

        .priority-high {
          background-color: rgba(255, 59, 48, 0.1);
          color: var(--danger);
        }

        .priority-medium {
          background-color: rgba(255, 204, 0, 0.1);
          color: var(--warning);
        }

        .priority-low {
          background-color: rgba(52, 199, 89, 0.1);
          color: var(--success);
        }

        .deadline {
          white-space: nowrap;
          color: var(--gray-700);
          font-size: 0.9rem;
        }

        .deadline i {
          margin-right: 0.25rem;
          color: var(--gray-500);
        }

        .completed-date {
          color: var(--success);
          font-size: 0.9rem;
          display: flex;
          align-items: center;
          gap: 0.25rem;
        }

        .completed-date i {
          color: var(--success);
        }

        .action-buttons {
          display: flex;
          gap: 0.5rem;
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

        .btn-warning {
          background-color: var(--warning);
          color: var(--dark);
        }

        .btn-success {
          background-color: var(--success);
          color: white;
        }

        .btn-sm {
          padding: 0.35rem 0.75rem;
          font-size: 0.85rem;
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
          .tasks-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
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
          
          .action-buttons {
            flex-direction: column;
            width: 100%;
          }
          
          .task-table th:nth-child(3),
          .task-table td:nth-child(3) {
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
                        <a href="done.php" class="active">
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
                <i class="fas fa-check-circle"></i>
                Completed Tasks
            </h1>
            
            <!-- Completed Tasks Section -->
            <div class="tasks-container">
                <div class="tasks-header">
                    <div class="tasks-title">
                        <i class="fas fa-clipboard-check"></i>
                        Completed Tasks
                        <span class="tasks-count"><?php echo $completed_count; ?></span>
                    </div>
                </div>
                
                <?php
                // Get completed tasks based on role
                if($role == 'admin') {
                    $query = "SELECT * FROM tbl_tasks WHERE status='complited'";
                    $stmt = $conn->prepare($query);
                } else {
                    $query = "SELECT * FROM tbl_tasks WHERE (user_id = ? OR user_id IS NULL) AND status='complited'";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows > 0) {
                ?>
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Task Name</th>
                            <th>Description</th>
                            <th>Priority</th>
                            <th>Deadline</th>
                            <th>Actions</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while($row = $result->fetch_assoc()) {
                            $task_id = $row['task_id'];
                            $task_name = $row['task_name'];
                            $task_description = $row['task_description'];
                            $priority = $row['priority'];
                            $deadline = $row['deadline'];
                            
                            // Determine priority class
                            $priority_class = '';
                            switch($priority) {
                                case 'High': 
                                    $priority_class = 'priority-high'; 
                                    break;
                                case 'Medium': 
                                    $priority_class = 'priority-medium'; 
                                    break;
                                case 'Low': 
                                    $priority_class = 'priority-low'; 
                                    break;
                            }
                            
                            // Format date
                            $formatted_date = date('M d, Y', strtotime($deadline));
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td class="task-name"><?php echo $task_name; ?></td>
                            <td class="task-description"><?php echo $task_description; ?></td>
                            <td>
                                <span class="priority-badge <?php echo $priority_class; ?>">
                                    <?php echo $priority; ?>
                                </span>
                            </td>
                            <td class="deadline">
                                <i class="far fa-calendar-alt"></i> <?php echo $formatted_date; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="delete-task.php?task_id=<?php echo $task_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this task?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <a href="pending.php?task_id=<?php echo $task_id; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-undo"></i> Move to Pending
                                </a>
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
                    <i class="fas fa-clipboard-check empty-icon"></i>
                    <p class="empty-text">No completed tasks found</p>
                    <a href="<?php echo $role === 'admin' ? 'admin_page.php' : 'user_page.php'; ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Return to Dashboard
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