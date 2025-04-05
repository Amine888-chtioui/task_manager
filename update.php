<?php
            
require_once('config.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // توجيه المستخدم إلى صفحة تسجيل الدخول
    exit();
}

   if(isset($_GET['list_id'])){
    $list_id = $_GET['list_id'];
    $role =$_SESSION['role'];
    $user_id=$_SESSION['user_id'];
    $mon = $conn->query("SELECT * FROM tbl_lists WHERE list_id=$list_id");
    if($mon){
        $count_mon = mysqli_num_rows($mon);
        if($count_mon > 0){
           while($row = mysqli_fetch_assoc($mon)){
            $list_name = $row['list_name'];
            $list_description = $row['list_description'];
           }
        }
    }

    if(isset($_POST['submit'])){
        $list_name = $_POST['list_name'];
        $list_description = $_POST['list_description'];

        if($role =='admin'){
            $result = $conn->query("UPDATE tbl_lists SET list_name = '$list_name', list_description = '$list_description' WHERE list_id = $list_id");
        
        }else{
         $result = $conn->query("UPDATE tbl_lists SET list_name = '$list_name', list_description = '$list_description' WHERE list_id = $list_id AND user_id=$user_id");

        }

        
         if($result){
            $_SESSION['update']="la modification est reussit avec sucsess";
            header("Location: manage-list.php");
         }
    
    
        }

   }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="update.css">
    <title>Document</title>
</head>
<body>
    <h1>update list</h1>
    <a href="index.php">Home</a>
    <a href="manage-list.php">manage list</a>
    <form action="" method="post">
        <table>
            <tr>
                <td>name</td>
                <td><input type="text" name="list_name" placeholder="enter the name" required="required" value="<?php echo $list_name ?>"></td>
            </tr>
            <tr>
                <td>description</td>
                <td><textarea name="list_description" placeholder="Enter the description" required="required"><?php echo $list_description; ?></textarea></td>
            </tr>
            <tr>
                <td><input type="submit" name="submit" value="Send"></td>
            </tr>
        </table>
    </form>
</body>
</html>

