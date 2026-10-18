<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (empty($_SESSION['q1_csrf'])) $_SESSION['q1_csrf'] = bin2hex(random_bytes(32));
function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
$cats = $conn->query('SELECT id,name FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header><a class="brand" href="index.php">My Shopping Cart</a>
        <nav><a href="index.php">Home</a><?php if (!empty($_SESSION['user_id'])): ?><span>Hello, <?= h($_SESSION['user_name']) ?></span><a href="user/logout.php">Logout</a><?php else: ?><a href="user/register.php">Register</a><a href="user/login.php">Login</a><?php endif; ?><a href="admin/login.php">Admin</a></nav>
    </header>
    <main class="wrap">
        <section class="hero">
            <h1>Shop by category</h1>
            <p>Select a category to load its products with AJAX pagination.</p>
        </section>
        <div class="layout">
            <aside class="panel">
                <h2>Categories</h2><button class="category-link active" data-category="0">All products</button><?php foreach ($cats as $c): ?><button class="category-link" data-category="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></button><?php endforeach; ?>
            </aside>
            <section>
                <h2>Products</h2>
                <div id="status" aria-live="polite"></div>
                <div id="products" class="grid"></div>
                <div id="pages" class="pages"></div>
            </section>
        </div>
    </main>
    <script src="assets/script.js"></script>
</body>

</html>