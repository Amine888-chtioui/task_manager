<?php
require_once 'config.php';

if(isset($_GET['task_id'])){
    $task_id = $_GET['task_id'];

    $result = $conn->query("UPDATE tbl_tasks SET status='complited' WHERE task_id=$task_id");
    if($result){
        header("Location: done.php");
    }else{
        header("error.php");
    }
}


?>