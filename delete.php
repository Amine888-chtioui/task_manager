<?php 
require_once('config.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


    if(isset($_GET['list_id'])){
        $list_id = $_GET['list_id'];
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];

    if($role =='admin'){
     $result = $conn->query("DELETE FROM tbl_lists WHERE list_id=$list_id");

    }else{
     $result = $conn->query("DELETE FROM tbl_lists WHERE list_id=$list_id AND user_id=$user_id");

    }
    if($result == true){
        $_SESSION['delete'] = "delete sucess";
        header("Location: manage-list.php");
    }else{
        $_SESSION['error-delete'] = "delete error";
        header("Location: manage-list.php");

    }

    }







?>