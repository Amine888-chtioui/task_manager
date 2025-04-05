<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
    exit();
}
if(isset($_GET['task_id'])){
    $task_id = $_GET['task_id'];
    $role = $_SESSION['role'];
    $result = $conn->query("SELECT * FROM tbl_tasks WHERE task_id=$task_id");
    $count_result = mysqli_num_rows($result);
        if($count_result > 0){
            while($row = mysqli_fetch_assoc($result)){
                $task_name = $row['task_name'];
                $task_description = $row['task_description'];
                $list_id = $row['list_id'];
                $task_pr = $row['priority'];
                $task_dl = $row['deadline'];
            }
        }
    if(isset($_POST['submit'])){
        $task_name = $_POST['tn'];
        $task_description = $_POST['td'];
        $list_id = $_POST['ld'];
        $task_pr = $_POST['pr'];
        $deadline = $_POST['dl'];
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];

        if($role =='admin'){
            $stmt = $conn->prepare("UPDATE tbl_tasks SET task_name=?, task_description=?, list_id=?, priority=?, deadline=? WHERE task_id=?");
            $stmt->bind_param("ssissi", $task_name, $task_description, $list_id, $task_pr, $deadline, $task_id);
            $stmt->execute();
            $stmt->close();
        
        }else{
            $stmt = $conn->prepare("UPDATE tbl_tasks SET task_name=?, task_description=?, list_id=?, priority=?, deadline=? WHERE task_id=? AND user_id=?");
            $stmt->bind_param("ssissi", $task_name, $task_description, $list_id, $task_pr, $deadline, $task_id, $user_id);
            $stmt->execute();
            $stmt->close();

        }

        if($role =='admin'){
            header("Location: admin_page.php");

        }elseif($role == 'user'){
            header("Location: user_page.php");

        }else{
            header("Location: error.php");
        }
    }
}



?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h1>update list</h1>
<form method="POST" action="">
    <table>
        <tr>
            <td>Task name</td>
            <td><input type="text" name="tn" value="<?php echo $task_name; ?>" placeholder="enter your description" required="required"></td>
        </tr>
        <tr>
            <td>Task Description</td>
            <td><input type="text" name="td" value="<?php echo $task_description; ?>" required="required"></td>
        </tr>       
        <tr>
            <td>Select_list</td>
            <td>
                <select name="ld">
                    <?php 
                    require_once 'config.php';
                    $resultt = $conn->query("SELECT list_id, list_name FROM tbl_lists");
                    while($row = $resultt->fetch_assoc()) {
                        ?>
                            <option value="<?php $row['list_id']?>"><?php echo $row['list_name'] ?></option>
                        <?php 

                     
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Priority</td>
            <td>
                <select name="pr">
                    <option value="High" <?php echo ($task_pr == "High") ? 'selected="selected"' : ''; ?>>High</option>
                    <option value="Medium" <?php echo ($task_pr == "Medium") ? 'selected="selected"' : ''; ?>>Medium</option>
                    <option value="Low" <?php echo ($task_pr == "Low") ? 'selected="selected"' : ''; ?>>Low</option>
                </select>
            </td>
        </tr>     
        <tr>
            <td>Deadline</td>
            <td><input type="date" name="dl" value="<?php echo $task_dl; ?>" required="required"></td>
        </tr>
        <tr>
            <td><input type="submit" name="submit" value="Send"></td>
        </tr>
    </table>
</form>
</body>
</html>