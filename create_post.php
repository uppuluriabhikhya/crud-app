<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id']; // get current logged-in user ID

    if (!$title) {
        $errors[] = "Title is required";
    }

    if (!$content) {
        $errors[] = "Content is required";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$title, $content, $user_id]);
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e9f0f4;
            padding: 30px;
            margin: 0;
        }

        .container {
            max-width: 700px;
            background: #ffffff;
            margin: auto;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        a.back-button {
            display: inline-block;
            margin-bottom: 20px;
            background: #6c757d;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
        }

        a.back-button:hover {
            background: #5a6268;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            background: #f9f9f9;
        }

        textarea {
            resize: vertical;
        }

        button {
            margin-top: 20px;
            padding: 12px 20px;
            background: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        ul.error-list {
            color: red;
            list-style: disc;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create New Post</h1>
        <a href="dashboard.php" class="back-button">← Back to Dashboard</a>

        <?php if ($errors): ?>
            <ul class="error-list">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="create_post.php">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>">

            <label for="content">Content</label>
            <textarea name="content" id="content" rows="6"><?= isset($content) ? htmlspecialchars($content) : '' ?></textarea>

            <button type="submit">Create Post</button>
        </form>
    </div>
</body>
</html>
