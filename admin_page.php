<?php
require_once('config.php');
if(($_SESSION['role'] != 'admin')){
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
    exit();
}

// Priority filter handling
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>TASK MANAGER</h1>
    <h2 style="text-align: center; color: red;">Admin</h2>

    <div class="menu">
        <a href="admin_page.php">Home</a>
        <a href="#">To Do</a>
        <a href="done.php">Done</a>
        <a href="manage-list.php">manage list</a>
        <a href="logout.php">logout</a>
        <?php
          require_once 'config.php';
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
    
    <table>
        <tr>
            <th>S.N</th>
            <th>Task Name</th>
            <th>Task description</th>
            <th>Priority</th>
            <th>Deadline</th>
            <th>Actions</th>
        </tr>

        <?php
        require_once 'config.php';
        
        // Modify query based on priority filter
        if (!empty($priority_filter)) {
            $query = "SELECT * FROM tbl_tasks WHERE priority='$priority_filter' AND status='pending'";
        } else {
            $query = "SELECT * FROM tbl_tasks WHERE status='pending'";
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
                    </tr>  
                <?php
                }
            } else {
                ?>
                  <tr>
                    <td colspan="6" class="no-data">NO DATA FOUND</td>
                  </tr>
                <?php
            }
        }
        ?>
    </table>
</div>
</body>
</html>