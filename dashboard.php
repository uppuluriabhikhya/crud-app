<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Handle post deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    // Optional: Delete related comments first if you have a `comments` table
    // $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
    // $stmt->execute([$delete_id]);

    // Now delete the post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$delete_id]);

    header("Location: dashboard.php");
    exit;
}

// Pagination and Search
$limit = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Count total matching posts
$countQuery = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE :search OR content LIKE :search");
$countQuery->execute(['search' => "%$search%"]);
$totalPosts = $countQuery->fetchColumn();
$totalPages = max(1, ceil($totalPosts / $limit));

// Fetch paginated posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE title LIKE :search OR content LIKE :search ORDER BY created_at DESC LIMIT :offset, :limit");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
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

        form.search-form {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        form.search-form input[type="text"] {
            flex: 1;
            padding: 8px;
            margin-right: 10px;
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

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            margin: 0 5px;
            padding: 6px 12px;
            text-decoration: none;
            background: #007bff;
            color: white;
            border-radius: 4px;
        }

        .pagination a.active {
            background: #0056b3;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard - Blog Posts</h1>

        <a href="create_post.php" class="button">Create New Post</a>

        <!-- Search Form -->
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="button" style="background: #007bff;">Search</button>
        </form>

        <!-- Posts Table -->
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

        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>
