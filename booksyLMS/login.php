<?php
session_start();
require 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            header($user['role'] == 'admin' ? 'Location: admin.php' : 'Location: user.php');
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booksy LMS - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-form {
            background: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            width: 380px; 
            display: flex;
            flex-direction: column;
        }

        h2 {
            margin-bottom: 25px;
            font-weight: 600;
            color: #1b1f3b;
            text-align: center;
            font-size: 24px;
        }

        input, button {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            display: block;
        }

        input {
            border: 1px solid #ccc;
            text-align: left;
        }

        button {
            background: #1b1f3b;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }

        button:hover {
            background: #30365f;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #1b1f3b;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
        }

        p.error {
            color: red;
            font-size: 14px;
            margin: 5px 0 10px;
            text-align: center;
        }

        @media (max-width: 420px) {
            .login-form {
                width: 90%;
                padding: 30px;
            }

            input, button {
                padding: 12px;
                font-size: 14px;
            }

            h2 {
                font-size: 20px;
            }

            a {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Login to Booksy LMS</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="signup.php">Don't have an account? Sign up</a>
    </div>
</body>
</html>