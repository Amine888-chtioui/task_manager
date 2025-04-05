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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Document</title>
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
    <a href="add-task.php">add-task</a>
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
         $result = $conn->query("SELECT * FROM tbl_tasks");
         
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
                    }else{
                        ?>
                          <tr>
                            <td>//NO DATA FOUND</td>
                          </tr>
                        <?php
                    }
            }
         

        ?>
    </table>
</div>
</body>
</html>