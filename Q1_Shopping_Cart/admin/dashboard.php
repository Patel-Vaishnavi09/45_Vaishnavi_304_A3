<?php require_once __DIR__ . '/auth.php'; ?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <main class="wrap">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?= admin_h($_SESSION['admin_username'] ?? 'Admin') ?>.</p>
        <div class="cards"><a class="card" href="categories.php">Manage Categories</a><a class="card" href="products.php">Manage Products</a><a class="card" href="../index.php">Open User Site</a></div>
        <form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?= admin_h($_SESSION['admin_csrf']) ?>"><button>Logout</button></form>
    </main>
</body>

</html>