<?php
session_start();
require 'config/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$role = $_SESSION['role'] ?? 'viewer';
$canEdit = in_array($role, ['admin', 'editor']);
$canDelete = $role === 'admin';

// Handle post deletion
if ($canDelete && isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: dashboard.php");
    exit;
}

// Pagination and Search
$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$offset = ($page - 1) * $limit;

// Count matching posts
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE :search OR content LIKE :search");
$countStmt->execute(['search' => "%$search%"]);
$totalPosts = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalPosts / $limit));

// Fetch paginated posts
$postStmt = $pdo->prepare("SELECT * FROM posts WHERE title LIKE :search OR content LIKE :search ORDER BY created_at DESC LIMIT :offset, :limit");
$postStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$postStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$postStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$postStmt->execute();
$posts = $postStmt->fetchAll();

// Fetch editor/viewer summary (only for admin)
$editorCount = $viewerCount = 0;
$editors = $viewers = [];
if ($role === 'admin') {
    $editorStmt = $pdo->query("SELECT fullname, email FROM users WHERE role = 'editor'");
    $editors = $editorStmt->fetchAll();
    $editorCount = count($editors);

    $viewerStmt = $pdo->query("SELECT fullname, email FROM users WHERE role = 'viewer'");
    $viewers = $viewerStmt->fetchAll();
    $viewerCount = count($viewers);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Blog</title>
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
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        a.button {
            padding: 10px 15px;
            background: #28a745;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        a.button:hover {
            background: #218838;
        }
        .logout {
            text-align: right;
            margin-bottom: 10px;
        }
        .logout a {
            color: red;
            text-decoration: none;
        }
        .logout a:hover {
            text-decoration: underline;
        }
        form.search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        input[type="text"] {
            flex: 1;
            padding: 8px;
        }
        button.search-btn {
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #f9f9f9;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
        }
        th {
            background: #007bff;
            color: white;
        }
        td a {
            margin-right: 10px;
            text-decoration: none;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            margin: 0 3px;
            border-radius: 4px;
            text-decoration: none;
        }
        .pagination a.active {
            background: #0056b3;
            pointer-events: none;
        }
        .summary {
            margin: 30px 0;
        }
        .summary h3 {
            color: #333;
        }
        ul.user-list {
            list-style: none;
            padding-left: 0;
        }
        ul.user-list li {
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logout">
        Logged in as <strong><?= htmlspecialchars($role) ?></strong> |
        <a href="auth/logout.php">Logout</a>
    </div>

    <h1>Dashboard</h1>

    <div class="top-bar">
        <?php if ($canEdit): ?>
            <a href="create_post.php" class="button">+ Create Post</a>
        <?php endif; ?>
        <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
            <button class="search-btn" type="submit">Search</button>
        </form>
    </div>

    <table>
        <thead>
        <tr>
            <th>ID</th><th>Title</th><th>Content</th><th>Created</th>
            <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= $post['id'] ?></td>
                    <td><?= htmlspecialchars($post['title']) ?></td>
                    <td><?= nl2br(htmlspecialchars($post['content'])) ?></td>
                    <td><?= $post['created_at'] ?></td>
                    <?php if ($canEdit): ?>
                        <td>
                            <a href="edit_post.php?id=<?= $post['id'] ?>">Edit</a>
                            <?php if ($canDelete): ?>
                                <a href="?delete_id=<?= $post['id'] ?>" style="color:red" onclick="return confirm('Delete this post?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="<?= $canEdit ? 5 : 4 ?>" style="text-align:center">No posts found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="<?= $i == $page ? 'active' : '' ?>" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>

    <?php if ($role === 'admin'): ?>
        <div class="summary">
            <h3>👥 User Summary</h3>
            <p><strong>Editors:</strong> <?= $editorCount ?></p>
            <ul class="user-list">
                <?php foreach ($editors as $e): ?>
                    <li><?= htmlspecialchars($e['fullname']) ?> (<?= htmlspecialchars($e['email']) ?>)</li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Viewers:</strong> <?= $viewerCount ?></p>
            <ul class="user-list">
                <?php foreach ($viewers as $v): ?>
                    <li><?= htmlspecialchars($v['fullname']) ?> (<?= htmlspecialchars($v['email']) ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
