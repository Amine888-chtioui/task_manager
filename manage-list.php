<?php include('config.php');
$role = $_SESSION['role'];
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
    <link rel="stylesheet" href="manager.css">
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
    <h3>Manger Lists Page</h3>
    <?php

        if(isset($_SESSION['insert'])){
            echo $_SESSION['insert'];
            unset($_SESSION['insert']);
        }
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        if(isset($_SESSION['error-insert'])){
            echo $_SESSION['error-insert'];
            unset($_SESSION['error-insert']);
        }
        if(isset($_SESSION['update'])){
            echo $_SESSION['update'];
            unset($_SESSION['update']);
        }
    
    ?>

    <div class="all-lists">
        <a href="add-list.php">add list</a>
        <table>
            <tr>
                <th>S.N</th>
                <th>List Name</th>
                <th>Actions</th>
            </tr>
            <?php
            require_once('config.php');
            if($role == 'user'){
                $query = "SELECT * FROM tbl_lists WHERE user_id = '$user_id' OR user_id IS NULL";
             } elseif($role == 'admin'){
                $query = "SELECT * FROM tbl_lists";
             }
             
            
            $result = $conn->query($query);

            if($result == true){
                $mod = mysqli_num_rows($result);
                if($mod > 0){
                    $i = 1;
                    while($row = mysqli_fetch_assoc($result)){
                        $list_id = $row['list_id'];
                        $list_name = $row['list_name'];
                    ?>
                    <tr>
                        <td><?php echo $i++ ?></td>
                        <td><?php echo $list_name ?></td>
                        <td>
                            <a href="update.php?list_id=<?php echo $list_id ?>">update</a>
                            <a href="delete.php?list_id=<?php echo $list_id ?>">delete</a>
                        </td>
                    </tr>
                    <?php
                    }
                }
            } else {
                ?>
                <p>//aucun data for now</p>
                <?php
            }
            ?>
        </table>
    </div>
</body>
</html>