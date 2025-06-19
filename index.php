<?php
require 'config/db.php';

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$offset = ($page - 1) * $limit;

// Count total posts for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE :search OR content LIKE :search");
$countStmt->execute(['search' => "%$search%"]);
$totalPosts = $countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $limit);

// Get posts with limit + search
$stmt = $pdo->prepare("SELECT * FROM posts WHERE title LIKE :search OR content LIKE :search ORDER BY id DESC LIMIT :offset, :limit");
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

    <h2 class="mb-4">All Posts</h2>

    <!-- Search -->
    <form method="GET" class="d-flex mb-4">
        <input type="text" name="search" class="form-control me-2" value="<?= htmlspecialchars($search) ?>" placeholder="Search posts...">
        <button class="btn btn-primary">Search</button>
    </form>

    <!-- Posts -->
    <?php foreach ($posts as $post): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($post['content']) ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

</body>
</html>
