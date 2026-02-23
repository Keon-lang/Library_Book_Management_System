<?php
session_start();
include "db.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Librarian Login</title>
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8, #dfe9f3);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-box {
            background: #fff;
            padding: 35px 45px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            text-align: center;
            width: 350px;
        }
        h2 {
            color: #007BFF;
            margin-bottom: 25px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            background: #007BFF;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php 
    if(isset($_POST['username'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']); // MD5 is okay for test use

        $query = mysqli_query($conn, "SELECT * FROM librarians WHERE username='$username' AND password='$password'");

        if(mysqli_num_rows($query) > 0){
            $data = mysqli_fetch_array($query);

            // ✅ use the correct session name for your index.php
            $_SESSION['librarians'] = $data['username'];  

            echo '<script>alert("Welcome, '.$data['username'].'!"); 
            window.location.href="index.php";</script>';
        } else {
            echo '<script>alert("Invalid username or password!");</script>';
        }
    }
    ?>
    <div class="login-box">
        <h2>Librarian Login</h2>
        <form method="POST">
            <label for="username">Username</label>
            <input type="text" name="username" placeholder="Username" required><br>
            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
            <p>Default: admin / 12345</p>
        </form>
    </div>
</body>
</html>

