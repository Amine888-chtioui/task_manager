<?php
    require_once 'config.php';
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
        exit();
    }
    if(isset($_GET['task_id'])){
        $task_id = $_GET['task_id'];
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];

        if($role == 'admin'){
            $result = $conn->query("DELETE FROM tbl_tasks WHERE task_id=$task_id");
        }else{
            $result = $conn->query("DELETE FROM tbl_tasks WHERE task_id=$task_id AND user_id=$user_id");
        }
        if($result && $role =='admin'){
            $_SESSION['delete_task']="delete de task sucsess";
            header("Location: admin_page.php");
        }elseif($result && $role =='user'){
            $_SESSION['delete_task']="delete de task sucsess";
            header("Location: user_page.php");
        }else{
            $_SESSION['delete_error']="delete error";
        }
    }

?>