<?php 
require_once('config.php');
if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $check_email = $conn->query("SELECT * FROM users WHERE email = '$email'");
        if($check_email->num_rows > 0){
            $_SESSION['register_error']="Email is already registred!";
            header("Location: register.php");
            exit();
        }else{
            $result = $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')");
            if($result){
                header("Location: login.php");
            exit();
            }
        }

}

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){

            $session_token = bin2hex(random_bytes(32));
            $user_id = $user['id'];
            $role=$user['role'];

            $sec = $conn->query("INSERT INTO sessions (user_id, session_token, created_at) VALUES ('$user_id', '$session_token', NOW())");

            $_SESSION['session_token'] = $session_token;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role; 
            
            if($role == 'admin'){
                header("Location: admin_page.php");
                exit();
            } elseif($role == 'user') {
                header("Location: user_page.php");
                exit();
            } else {
                echo "Rôle inconnu: " . $role;
                exit();
            }
            exit();
        }
    }
    $_SESSION['login_error'] = 'incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: login.php");
    exit();
}
?>