<?php
require '../config/db.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password should be at least 6 characters.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $username, $passwordHash]);

            $success = "Registered successfully! <a href='login.php'>Login</a>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Email or username already exists. Please choose another.";
            } else {
                $error = "Database error: " . $e->getMessage(); // Show actual error
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Register</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #74ebd5, #ACB6E5);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-box {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            background: #f9f9f9;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success, .error {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<form method="post" class="form-box" novalidate>
    <h2>Register</h2>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <input type="text" name="fullname" placeholder="Full Name" required value="<?= isset($fullname) ? htmlspecialchars($fullname) : '' ?>" />
    <input type="email" name="email" placeholder="Email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" />
    <input type="text" name="username" placeholder="Username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" />
    <input type="password" name="password" placeholder="Password" required />
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
    <button type="submit">Register</button>
</form>
</body>
</html>
