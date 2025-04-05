<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <form action="login_register.php" method="post">
                <h1>Register</h1>
                <input type="text" name="name" placeholder="enter your name" required="required">
                <input type="email" name="email" placeholder="email" required="required">
                <input type="password" name="password" placeholder="password" required="required">
                <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
                </select>
                <button type="submit" name="register">register</button>
                <p>You have already account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>