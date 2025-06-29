<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = (int)$_GET['id'];

// Fetch post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    echo "Post not found.";
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!$title) {
        $errors[] = "Title is required.";
    }

    if (!$content) {
        $errors[] = "Content is required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $id]);
        header("Location: dashboard.php");
        exit;
    }
} else {
    $title = $post['title'];
    $content = $post['content'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #444;
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
            font-weight: 600;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #f9f9f9;
            font-size: 16px;
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
            padding-left: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Post</h1>
    <a href="dashboard.php" class="back-button">← Back to Dashboard</a>

    <?php if ($errors): ?>
        <ul class="error-list">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="edit_post.php?id=<?= $id ?>">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>">

        <label for="content">Content</label>
        <textarea name="content" id="content" rows="6"><?= htmlspecialchars($content) ?></textarea>

        <button type="submit">Update Post</button>
    </form>
</div>
</body>
</html>
