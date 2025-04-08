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

// Check for task_id in URL
if(!isset($_GET['task_id'])){
    header("Location: " . ($role == 'admin' ? 'admin_page.php' : 'user_page.php'));
    exit();
}

$task_id = $_GET['task_id'];

// Get task details
if($role == 'admin') {
    $stmt = $conn->prepare("SELECT * FROM tbl_tasks WHERE task_id = ?");
    $stmt->bind_param("i", $task_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM tbl_tasks WHERE task_id = ? AND (user_id = ? OR user_id IS NULL)");
    $stmt->bind_param("ii", $task_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    header("Location: " . ($role == 'admin' ? 'admin_page.php' : 'user_page.php'));
    exit();
}

$task = $result->fetch_assoc();
$task_name = $task['task_name'];
$task_description = $task['task_description'];
$list_id = $task['list_id'];
$priority = $task['priority'];
$deadline = $task['deadline'];

// Handle form submission
if(isset($_POST['submit'])){
    $task_name = $_POST['task_name'];
    $task_description = $_POST['task_description'];
    $list_id = !empty($_POST['list_id']) ? $_POST['list_id'] : NULL;
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];

    if($role == 'admin') {
        $stmt = $conn->prepare("UPDATE tbl_tasks SET task_name = ?, task_description = ?, list_id = ?, priority = ?, deadline = ? WHERE task_id = ?");
        $stmt->bind_param("ssissi", $task_name, $task_description, $list_id, $priority, $deadline, $task_id);
    } else {
        $stmt = $conn->prepare("UPDATE tbl_tasks SET task_name = ?, task_description = ?, list_id = ?, priority = ?, deadline = ? WHERE task_id = ? AND user_id = ?");
        $stmt->bind_param("ssissii", $task_name, $task_description, $list_id, $priority, $deadline, $task_id, $user_id);
    }

    if($stmt->execute()) {
        $_SESSION['update_task'] = "Task updated successfully";
        header("Location: " . ($role == 'admin' ? 'admin_page.php' : 'user_page.php'));
        exit();
    } else {
        $update_error = "Error updating task";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Task | Task Manager</title>
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

        .alert-danger {
          background-color: rgba(255, 59, 48, 0.1);
          color: var(--danger);
          border: 1px solid rgba(255, 59, 48, 0.2);
        }

        /* === FORM CONTAINER === */
        .form-container {
          background-color: white;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          overflow: hidden;
          max-width: 800px;
          margin: 0 auto;
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

        .form-select {
          width: 100%;
          padding: 0.75rem 1rem;
          font-size: 1rem;
          background-color: white;
          border: 1px solid var(--gray-300);
          border-radius: var(--radius);
          appearance: none;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 1rem center;
          background-size: 16px 12px;
          transition: var(--transition);
        }

        .form-select:focus {
          outline: none;
          border-color: var(--primary);
          box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea.form-control {
          min-height: 120px;
          resize: vertical;
        }

        .form-group-row {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 1.5rem;
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

        /* Task metadata */
        .task-meta {
          display: flex;
          flex-wrap: wrap;
          gap: 1rem;
          margin-bottom: 1.5rem;
          font-size: 0.9rem;
        }

        .task-meta-item {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          color: var(--gray-600);
        }

        .task-meta-item i {
          color: var(--primary);
        }

        /* Responsive adaptations */
        @media (max-width: 992px) {
          .sidebar {
            display: none;
          }
        }

        @media (max-width: 768px) {
          .form-group-row {
            grid-template-columns: 1fr;
            gap: 1rem;
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
                        <a href="#">
                            <i class="fas fa-users"></i>
                            Manage Users
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <div class="nav-section-title">SETTINGS</div>
                    <li class="nav-item">
                        <a href="#">
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
                <i class="fas fa-edit"></i>
                Update Task
            </h1>
            
            <?php if(isset($update_error)): ?>
            <div class="alert alert-danger">
                <?php echo $update_error; ?>
            </div>
            <?php endif; ?>
            
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title">
                        <i class="fas fa-clipboard-list"></i>
                        Edit Task: <?php echo htmlspecialchars($task_name); ?>
                    </h2>
                </div>
                
                <form action="" method="post">
                    <div class="form-content">
                        <div class="form-group">
                            <label for="task_name" class="form-label">Task Name</label>
                            <input type="text" id="task_name" name="task_name" class="form-control" value="<?php echo htmlspecialchars($task_name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="task_description" class="form-label">Task Description</label>
                            <textarea id="task_description" name="task_description" class="form-control" required><?php echo htmlspecialchars($task_description); ?></textarea>
                        </div>
                        
                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="list_id" class="form-label">Select List</label>
                                <select id="list_id" name="list_id" class="form-select">
                                    <option value="">None</option>
                                    <?php 
                                    // Get available lists
                                    if($role == 'admin') {
                                        $listQuery = "SELECT * FROM tbl_lists";
                                        $stmt = $conn->prepare($listQuery);
                                    } else {
                                        $listQuery = "SELECT * FROM tbl_lists WHERE user_id = ? OR user_id IS NULL";
                                        $stmt = $conn->prepare($listQuery);
                                        $stmt->bind_param("i", $user_id);
                                    }
                                    
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    while($row = $result->fetch_assoc()) {
                                        $selected = ($row['list_id'] == $list_id) ? 'selected' : '';
                                        echo '<option value="' . $row['list_id'] . '" ' . $selected . '>' . $row['list_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="priority" class="form-label">Priority</label>
                                <select id="priority" name="priority" class="form-select" required>
                                    <option value="High" <?php echo ($priority == 'High') ? 'selected' : ''; ?>>High</option>
                                    <option value="Medium" <?php echo ($priority == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="Low" <?php echo ($priority == 'Low') ? 'selected' : ''; ?>>Low</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" id="deadline" name="deadline" class="form-control" value="<?php echo $deadline; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <a href="<?php echo $role === 'admin' ? 'admin_page.php' : 'user_page.php'; ?>" class="btn btn-outline">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Task
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>