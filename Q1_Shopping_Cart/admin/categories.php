<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../config/db.php';
admin_csrf_check();
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    try {
        if ($action === 'save') {
            if ($name === '') throw new RuntimeException('Enter a category name.');
            if ($id) {
                $s = $conn->prepare('UPDATE categories SET name=? WHERE id=?');
                $s->bind_param('si', $name, $id);
            } else {
                $s = $conn->prepare('INSERT INTO categories(name) VALUES(?)');
                $s->bind_param('s', $name);
            }
            $s->execute();
            $s->close();
            $message = 'Category saved successfully.';
        } elseif ($action === 'delete' && $id) {
            $s = $conn->prepare('DELETE FROM categories WHERE id=?');
            $s->bind_param('i', $id);
            $s->execute();
            $s->close();
            $message = 'Category deleted. Its products are also deleted.';
        }
    } catch (Throwable $e) {
        $error = $e instanceof mysqli_sql_exception ? 'Could not save category. The name may already exist.' : $e->getMessage();
    }
}
$edit = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $s = $conn->prepare('SELECT * FROM categories WHERE id=?');
    $s->bind_param('i', $id);
    $s->execute();
    $edit = $s->get_result()->fetch_assoc();
    $s->close();
}
$categories = $conn->query('SELECT c.id,c.name,COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id,c.name ORDER BY c.name')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <main class="wrap">
        <nav><a href="dashboard.php">Dashboard</a> · <a href="products.php">Products</a> · <a href="../index.php">User site</a></nav>
        <h1>Manage Categories (CRUD)</h1><?php if ($message): ?><p class="success"><?= admin_h($message) ?></p><?php endif; ?><?php if ($error): ?><p class="error"><?= admin_h($error) ?></p><?php endif; ?>
        <section class="panel">
            <h2><?= $edit ? 'Edit' : 'Add' ?> category</h2>
            <form method="post"><input type="hidden" name="csrf" value="<?= admin_h($_SESSION['admin_csrf']) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>"><label>Category name<input name="name" maxlength="100" required value="<?= admin_h($edit['name'] ?? '') ?>"></label><button>Save category</button><?php if ($edit): ?> <a href="categories.php">Cancel</a><?php endif; ?></form>
        </section>
        <section class="panel">
            <h2>Category list</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($categories as $c): ?><tr>
                            <td><?= (int)$c['id'] ?></td>
                            <td><?= admin_h($c['name']) ?></td>
                            <td><?= (int)$c['product_count'] ?></td>
                            <td><a href="?edit=<?= (int)$c['id'] ?>">Edit</a>
                                <form class="inline" method="post" onsubmit="return confirm('Delete category and its products?')"><input type="hidden" name="csrf" value="<?= admin_h($_SESSION['admin_csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="danger">Delete</button></form>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </section>
    </main>
</body>

</html>