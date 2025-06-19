<?php
require 'config/db.php'; // Your DB connection

$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE title LIKE :search OR content LIKE :search");
    $stmt->execute(['search' => '%' . $search . '%']);
} else {
    $stmt = $pdo->query("SELECT * FROM posts");
}

$posts = $stmt->fetchAll();
?>

<form method="get" action="">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search posts">
    <button type="submit">Search</button>
</form>

<?php foreach ($posts as $post): ?>
    <div class="post">
        <h3><?= htmlspecialchars($post['title']) ?></h3>
        <p><?= htmlspecialchars($post['content']) ?></p>
    </div>
<?php endforeach; ?>
