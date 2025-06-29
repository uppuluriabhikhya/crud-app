<?php
session_start();
require 'config/db.php';

// Only admin should access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newRole = $_POST['role'];

    if (!in_array($newRole, ['viewer', 'editor', 'admin'])) {
        $errors[] = "Invalid role selected.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $id]);
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User Role</title>
</head>
<body>
    <h1>Edit Role for <?= htmlspecialchars($user['username']) ?></h1>

    <?php if ($errors): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <label>Select Role:</label>
        <select name="role">
            <option value="viewer" <?= $user['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
            <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <br><br>
        <button type="submit">Update Role</button>
        <a href="dashboard.php">Cancel</a>
    </form>
</body>
</html>
