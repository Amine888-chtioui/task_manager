<?php
require_once 'config.php';
$user_id = $_SESSION['user_id'];
    $role = $_SESSION['role']; // Assuming the role is stored in the session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
    exit();
}

if(isset($_POST['submit'])){
    $sn = $_POST['sn'];
    $tn = $_POST['tn'];
    $pr = $_POST['pr'];
    $dl = $_POST['dl'];

    $user_id_insert = ($role == 'admin') ? 'NULL' : "'$user_id'";

    $result = $conn->query("INSERT INTO tbl_tasks (task_name, task_description, priority, deadline, user_id) VALUES ('$sn', '$tn', '$pr', '$dl', $user_id_insert)");
    if($result == true && $role =='admin'){
        $_SESSION['insert_tasks'] = "Task inserted successfully";
        header("Location: admin_page.php");
    }
    else{
        $_SESSION['insert_tasks_error'] = "Error inserting task";
        header("Location: user_page.php");
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="add_task.css">
    <title>Document</title>
</head>
<body>
    <h1>TASK MANAGER</h1>
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
    <div class="menu">
        <form action="" method="post">
            <table>
                <tr>
                    <td>name</td>
                    <td><input type="text" name="sn" placeholder="enter your name" required="required"></td>
                </tr>    
                    <td>Task description</td>
                    <td><input type="text" name="tn" placeholder="enter your description" required="required"></td>
                <tr>
                <tr>
                    <td>Select_list</td>
                    <td>
                        <select name="ld">
                            <?php 
                            
                            require_once 'config.php';

                            $result = $conn->query("SELECT * FROM tbl_lists");
                            if($result == true){
                                $count_result = mysqli_num_rows($result);
                                    if($count_result > 0){
                                        while($row = mysqli_fetch_assoc($result)){
                                            $list_id = $row['list_id'];
                                            $list_name = $row['list_name'];
                                            ?>
                                                <option value="<?php echo $list_id ?>"><?php echo $list_name ?></option>
                                            <?php
                                        }
                                    }
                            }

                            ?>
                        </select>
                    </td>
                </tr>
                    <td>Priority</td>
                    <td>
                        <select name="pr">
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </td>
                </tr>     
                    <td>Deadline</td>
                    <td><input type="date" name="dl" placeholder="enter your name" required="required"></td>
                <tr><td><input type="submit" name="submit" value="Send"></td></tr>
            </table>
        </form>
    </div>
</body>
</html>