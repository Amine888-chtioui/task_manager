<?php
require_once 'config.php';
    $role = $_SESSION['role'];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $list_name = $_POST['list_name'];
    $list_description = $_POST['list_description'];

    // جعل القوائم التي يضيفها الـ Admin عامة (user_id = NULL)
    $user_id_insert = ($role == "admin") ? NULL : $user_id;

    // إعداد وتنفيذ الاستعلام بإستخدام prepared statements
    $stmt = $conn->prepare("INSERT INTO tbl_lists (user_id, list_name, list_description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id_insert, $list_name, $list_description);

    if ($stmt->execute()) {
        $_SESSION['insert'] = "L'insertion réussie";
    } else {
        $_SESSION['error-insert'] = "Erreur d'insertion";
    }

    $stmt->close();
    header("Location: manage-list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="add_list.css">
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
    <a href="manage-list.php">Manage Lists</a>
    <h3>Add List Page</h3>
    <form action="" method="post">
        <table>
            <tr>
                <td>name</td>
                <td><input type="text" name="list_name" placeholder="Enter the name" required></td>
            </tr>
            <tr>
                <td>description</td>
                <td><textarea name="list_description" placeholder="Enter the description" required></textarea></td>
            </tr>
            <tr>
                <td><input type="submit" name="submit" value="Send"></td>
            </tr>
        </table>
    </form>
</body>
</html>