<?php 
require_once('config.php');
$role = $_SESSION['role'];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user name from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lists | Task Manager</title>
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
          font-size: 1.75rem;
          font-weight: 600;
          margin-bottom: 1.5rem;
          color: var(--dark);
        }

        /* === LISTS SECTION === */
        .lists-container {
          background-color: white;
          border-radius: var(--radius);
          box-shadow: var(--shadow);
          overflow: hidden;
        }

        .lists-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 1.25rem 1.5rem;
          border-bottom: 1px solid var(--gray-200);
        }

        .lists-title {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          font-size: 1.1rem;
          font-weight: 600;
          color: var(--dark);
        }

        .lists-title i {
          color: var(--primary);
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

        .btn-sm {
          padding: 0.35rem 0.75rem;
          font-size: 0.85rem;
        }

        /* === LIST TABLE === */
        .list-table {
          width: 100%;
          border-collapse: collapse;
        }

        .list-table th {
          text-align: left;
          padding: 1rem 1.5rem;
          font-weight: 600;
          color: var(--gray-700);
          background-color: var(--gray-100);
          border-bottom: 1px solid var(--gray-200);
        }

        .list-table td {
          padding: 1rem 1.5rem;
          border-bottom: 1px solid var(--gray-200);
        }

        .list-table tr:last-child td {
          border-bottom: none;
        }

        .list-name {
          font-weight: 500;
          color: var(--gray-800);
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

        /* Responsive adaptations */
        @media (max-width: 992px) {
          .sidebar {
            display: none;
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
          
          .lists-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
          }
          
          .btn {
            width: 100%;
          }
          
          .action-buttons {
            flex-direction: column;
            width: 100%;
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
                        <a href="#">
                            <i class="fas fa-list-check"></i>
                            My Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="done.php">
                            <i class="fas fa-check-circle"></i>
                            Completed Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-list.php" class="active">
                            <i class="fas fa-folder"></i>
                            Manage Lists
                        </a>
                    </li>
                    
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
            <h1 class="page-title">Manage Lists</h1>

            <?php
            // Display notifications
            if(isset($_SESSION['insert'])) {
                echo '<div class="alert alert-success">' . $_SESSION['insert'] . '</div>';
                unset($_SESSION['insert']);
            }
            
            if(isset($_SESSION['delete'])) {
                echo '<div class="alert alert-success">' . $_SESSION['delete'] . '</div>';
                unset($_SESSION['delete']);
            }
            
            if(isset($_SESSION['update'])) {
                echo '<div class="alert alert-success">' . $_SESSION['update'] . '</div>';
                unset($_SESSION['update']);
            }
            
            if(isset($_SESSION['error-insert'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error-insert'] . '</div>';
                unset($_SESSION['error-insert']);
            }
            
            if(isset($_SESSION['error-delete'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error-delete'] . '</div>';
                unset($_SESSION['error-delete']);
            }
            ?>
            
            <!-- Lists Section -->
            <div class="lists-container">
                <div class="lists-header">
                    <div class="lists-title">
                        <i class="fas fa-folder"></i>
                        All Lists
                    </div>
                    
                    <a href="add-list.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New List
                    </a>
                </div>
                
                <?php
                // Get lists based on user role
                if($role == 'user') {
                    $query = "SELECT * FROM tbl_lists WHERE user_id = ? OR user_id IS NULL";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                } else { // admin
                    $query = "SELECT * FROM tbl_lists";
                    $stmt = $conn->prepare($query);
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows > 0) {
                ?>
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>List Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while($row = $result->fetch_assoc()) {
                            $list_id = $row['list_id'];
                            $list_name = $row['list_name'];
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td class="list-name"><?php echo $list_name; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="update.php?list_id=<?php echo $list_id; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete.php?list_id=<?php echo $list_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this list?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
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
                    <i class="fas fa-folder-open empty-icon"></i>
                    <p class="empty-text">No lists found</p>
                    <a href="add-list.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Create Your First List
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