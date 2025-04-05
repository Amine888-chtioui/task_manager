<?php
require_once 'config.php';

if(isset($_GET['task_id'])){
    $task_id = $_GET['task_id'];

    $stmt = $conn->prepare("UPDATE tbl_tasks SET status='pending' WHERE task_id=?");
    $stmt->bind_param("i", $task_id);
    if($stmt->execute()){
        header("Location: user_page.php");
    }else{
        header("Location: error.php");
    }
    $stmt->close();
}

?>