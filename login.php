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
                <h1>Login</h1>
                <input type="email" name="email" placeholder="email" required="required">
                <input type="password" name="password" placeholder="password" required="required">
                <button type="submit" name="login">login</button>
                <p>Don't have ana account? <a href="register.php">Register</a></p>
            </form>
        </div>
    </div>
</body>
</html>