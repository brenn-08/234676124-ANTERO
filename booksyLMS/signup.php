<?php
require 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $password])) {
        header('Location: login.php?success=1');
        exit;
    } else {
        $error = "Signup failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booksy LMS - Signup</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .signup-form {
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
            .signup-form {
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
    <div class="signup-form">
        <h2>Sign up for Booksy LMS</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <a href="login.php">Already have an account? Login</a>
    </div>
</body>
</html>