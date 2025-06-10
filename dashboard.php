<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Delete post
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$delete_id]);
    header('Location: dashboard.php');
    exit;
}

// Fetch posts
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Blog Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #ffffff;
            padding: 20px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        a.button {
            display: inline-block;
            padding: 10px 15px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        a.button:hover {
            background: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #f9f9f9;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }

        th {
            background: #007bff;
            color: white;
        }

        td a {
            color: #007bff;
            text-decoration: none;
            margin-right: 10px;
        }

        td a:hover {
            text-decoration: underline;
        }

        .no-posts {
            text-align: center;
            color: #666;
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard - Blog Posts</h1>
        <a href="create_post.php" class="button">Create New Post</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Title</th><th>Content</th><th>Created At</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($posts): ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['id']) ?></td>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= nl2br(htmlspecialchars($post['content'])) ?></td>
                            <td><?= htmlspecialchars($post['created_at']) ?></td>
                            <td>
                                <a href="edit_post.php?id=<?= $post['id'] ?>">Edit</a>
                                <a href="dashboard.php?delete_id=<?= $post['id'] ?>" onclick="return confirm('Are you sure to delete this post?');" style="color: red;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-posts">No posts found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
