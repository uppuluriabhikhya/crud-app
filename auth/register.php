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
    $role = $_POST['role'];

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($fullname) < 3 || strlen($username) < 3) {
        $error = "Full name and username must be at least 3 characters.";
    } elseif (!in_array($role, ['admin', 'editor', 'viewer'])) {
        $error = "Invalid role selected.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, username, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$fullname, $email, $username, $passwordHash, $role]);
            $success = "Registered successfully! <a href='login.php'>Login here</a>";
            $fullname = $email = $username = '';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Email or username already exists.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(120deg, #74ebd5, #acb6e5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .form-box input,
        .form-box select,
        .form-box button {
            width: 100%;
            margin: 10px 0;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .form-box input:focus,
        .form-box select:focus {
            outline: none;
            border-color: #007bff;
        }

        .form-box button {
            background-color: #007bff;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .form-box button:hover {
            background-color: #0056b3;
        }

        .success, .error {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
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
    <form method="post" class="form-box">
        <h2>Register</h2>

        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <input type="text" name="fullname" placeholder="Full Name" required value="<?= htmlspecialchars($fullname ?? '') ?>" />
        <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($email ?? '') ?>" />
        <input type="text" name="username" placeholder="Username" required value="<?= htmlspecialchars($username ?? '') ?>" />
        <input type="password" name="password" placeholder="Password" required />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />

        <select name="role" required>
            <option value="">Select Role</option>
            <option value="viewer" <?= (isset($role) && $role === 'viewer') ? 'selected' : '' ?>>Viewer</option>
            <option value="editor" <?= (isset($role) && $role === 'editor') ? 'selected' : '' ?>>Editor</option>
            <option value="admin" <?= (isset($role) && $role === 'admin') ? 'selected' : '' ?>>Admin</option>
        </select>

        <button type="submit">Register</button>
    </form>
</body>
</html>
