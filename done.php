<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
    exit();
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="done.css">
    <title>Document</title>
</head>
<body>
<?php 
        if($role =='admin'){
            ?>
    <a href="admin_page.php">Home</a>
            <?php
        }elseif($role =='user'){
            ?>
    <a href="user_page.php">Home</a>
            <?php
        }
    
    ?>
    <div class="container">
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
        if($role =='admin'){
         $result = $conn->query("SELECT * FROM tbl_tasks WHERE status='complited'");

        }else{
            $result = $conn->query("SELECT * FROM tbl_tasks WHERE (user_id = '$user_id' OR user_id IS NULL )AND status='complited'");
        }         
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
                              
                                    <a href="delete-task.php?task_id=<?php echo $ab ?>">Delete</a>
                                </td>
                                <td>
                                  <a href="pending.php?task_id=<?php echo $ab ?>"><button>Anuller</button></a>
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