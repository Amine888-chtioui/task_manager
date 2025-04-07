<?php
require_once('config.php');

// التحقق من الجلسة
if (!isset($_SESSION['session_token'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
    exit();
}

// التحقق من دور المستخدم
if ($_SESSION['role'] !== 'user') {
    echo "ليس لديك صلاحية للوصول إلى هذه الصفحة.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Priority filter handling
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="user.css">
    <title>User Dashboard</title>
</head>
<body>
    <h1>TASK MANAGER</h1>
    <h2 style="text-align: center; color: green;">User</h2>

    <div class="menu">
        <a href="user_page.php">Home</a>
        <a href="#">To Do</a>
        <a href="done.php">Done</a>
        <a href="manage-list.php">manage list</a>
        <a href="logout.php">logout</a>
        <?php
            if(isset($_SESSION['insert_tasks'])){
                echo $_SESSION['insert_tasks'];
                unset($_SESSION['insert_tasks']);
            }
        ?>
    </div>

<div class="all-tasks">
    <div class="task-header">
        <a href="add-task.php" class="add-task-btn">add-task</a>
        
        <!-- Priority Filter Form -->
        <div class="filter-container">
            <form method="GET" action="" class="filter-form">
                <label for="priority">Filter by priority:</label>
                <select name="priority" id="priority" onchange="this.form.submit()">
                    <option value="" <?php echo $priority_filter == '' ? 'selected' : ''; ?>>All Priorities</option>
                    <option value="High" <?php echo $priority_filter == 'High' ? 'selected' : ''; ?>>High</option>
                    <option value="Medium" <?php echo $priority_filter == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="Low" <?php echo $priority_filter == 'Low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </form>
        </div>
    </div>
    <style>.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filter-container {
    background-color: white;
    padding: 10px 15px;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-form label {
    font-weight: bold;
    color: #333;
    margin-right: 5px;
}

.filter-form select {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background-color: #f8f8f8;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.filter-form select:hover {
    border-color: #007bff;
}

.filter-form select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

/* For responsive design */
@media screen and (max-width: 768px) {
    .task-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .filter-container {
        width: 100%;
    }
    
    .filter-form {
        width: 100%;
    }
    
    .filter-form select {
        flex-grow: 1;
    }
}</style>

    <table>
        <tr>
            <th>S.N</th>
            <th>Task Name</th>
            <th>Task description</th>
            <th>Priority</th>
            <th>Deadline</th>
            <th>Actions</th>
            <th>Status</th>
        </tr>

        <?php
        // Modify query based on priority filter
        if (!empty($priority_filter)) {
            $query = "SELECT * FROM tbl_tasks WHERE (user_id = '$user_id' OR user_id IS NULL) AND status='pending' AND priority='$priority_filter'";
        } else {
            $query = "SELECT * FROM tbl_tasks WHERE (user_id = '$user_id' OR user_id IS NULL) AND status='pending'";
        }
        
        $result = $conn->query($query);
         
        if($result == true){
            $count_result = mysqli_num_rows($result);
            if($count_result > 0){
                $i = 1;
                while($row = mysqli_fetch_assoc($result)){
                    $ab = $row['task_id'];
                    $a = $row['task_name'];
                    $b = $row['task_description'];
                    $c = $row['priority'];
                    $d = $row['deadline'];
                    ?>
                    <tr>
                        <td><?php echo $i++ ?></td>
                        <td><?php echo $a ?></td>
                        <td><?php echo $b ?></td>
                        <td><?php echo $c ?></td>
                        <td><?php echo $d ?></td>
                        <td>
                            <a href="update-task.php?task_id=<?php echo $ab ?>">Update</a>
                            <a href="delete-task.php?task_id=<?php echo $ab ?>">Delete</a>
                        </td>
                        <td>
                            <a href="complited.php?task_id=<?php echo $ab ?>"><button>Completed</button></a>
                        </td>
                    </tr>  
                <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" class="no-data">NO DATA FOUND</td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
</div>
</body>
</html>