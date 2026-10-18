<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../config/db.php';
admin_csrf_check();
$message = '';
$error = '';
function save_product_image()
{
    if (empty($_FILES['picture']) || $_FILES['picture']['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES['picture']['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Image upload failed.');
    if ($_FILES['picture']['size'] > 5 * 1024 * 1024) throw new RuntimeException('Image must be under 5 MB.');
    $info = getimagesize($_FILES['picture']['tmp_name']);
    if (!$info) throw new RuntimeException('Choose a valid image.');
    $mime = $info['mime'];
    $fn = ['image/jpeg' => 'imagecreatefromjpeg', 'image/png' => 'imagecreatefrompng', 'image/gif' => 'imagecreatefromgif', 'image/webp' => 'imagecreatefromwebp'];
    if (!isset($fn[$mime]) || !function_exists($fn[$mime])) throw new RuntimeException('Only JPG, PNG, GIF, or WebP is supported. Enable PHP GD.');
    $src = $fn[$mime]($_FILES['picture']['tmp_name']);
    $w = imagesx($src);
    $h = imagesy($src);
    $scale = min(600 / max(1, $w), 450 / max(1, $h), 1);
    $nw = max(1, (int)($w * $scale));
    $nh = max(1, (int)($h * $scale));
    $dst = imagecreatetruecolor($nw, $nh);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $name = bin2hex(random_bytes(12)) . '.jpg';
    $ok = imagejpeg($dst, __DIR__ . '/../uploads/' . $name, 85);
    imagedestroy($src);
    imagedestroy($dst);
    if (!$ok) throw new RuntimeException('Cannot write to uploads folder.');
    return $name;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    try {
        if ($action === 'save') {
            $cat = (int)($_POST['category_id'] ?? 0);
            $name = trim($_POST['pname'] ?? '');
            $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
            $desc = trim($_POST['description'] ?? '');
            if (!$cat || $name === '' || $price === false || $price < 0) throw new RuntimeException('Enter product name, category, and a valid price.');
            $new = save_product_image();
            if ($id) {
                if ($new) {
                    $s = $conn->prepare('SELECT picture FROM products WHERE id=?');
                    $s->bind_param('i', $id);
                    $s->execute();
                    $old = $s->get_result()->fetch_assoc()['picture'] ?? null;
                    $s->close();
                    $s = $conn->prepare('UPDATE products SET category_id=?,pname=?,price=?,description=?,picture=? WHERE id=?');
                    $s->bind_param('isdssi', $cat, $name, $price, $desc, $new, $id);
                    $s->execute();
                    $s->close();
                    if ($old && is_file(__DIR__ . '/../uploads/' . basename($old))) @unlink(__DIR__ . '/../uploads/' . basename($old));
                } else {
                    $s = $conn->prepare('UPDATE products SET category_id=?,pname=?,price=?,description=? WHERE id=?');
                    $s->bind_param('isdsi', $cat, $name, $price, $desc, $id);
                    $s->execute();
                    $s->close();
                }
                $message = 'Product updated.';
            } else {
                $s = $conn->prepare('INSERT INTO products(category_id,pname,price,description,picture) VALUES(?,?,?,?,?)');
                $s->bind_param('isdss', $cat, $name, $price, $desc, $new);
                $s->execute();
                $s->close();
                $message = 'Product added.';
            }
        } elseif ($action === 'delete' && $id) {
            $s = $conn->prepare('SELECT picture FROM products WHERE id=?');
            $s->bind_param('i', $id);
            $s->execute();
            $old = $s->get_result()->fetch_assoc()['picture'] ?? null;
            $s->close();
            $s = $conn->prepare('DELETE FROM products WHERE id=?');
            $s->bind_param('i', $id);
            $s->execute();
            $s->close();
            if ($old && is_file(__DIR__ . '/../uploads/' . basename($old))) @unlink(__DIR__ . '/../uploads/' . basename($old));
            $message = 'Product deleted.';
        }
    } catch (Throwable $e) {
        $error = $e instanceof mysqli_sql_exception ? 'Database error. Check the selected category and table structure.' : $e->getMessage();
    }
}
$cats = $conn->query('SELECT id,name FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$edit = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $s = $conn->prepare('SELECT * FROM products WHERE id=?');
    $s->bind_param('i', $id);
    $s->execute();
    $edit = $s->get_result()->fetch_assoc();
    $s->close();
}
$products = $conn->query('SELECT p.*,c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <main class="wrap">
        <nav><a href="dashboard.php">Dashboard</a> · <a href="categories.php">Categories</a> · <a href="../index.php">User site</a></nav>
        <h1>Manage Products</h1><?php if ($message): ?><p class="success"><?= admin_h($message) ?></p><?php endif; ?><?php if ($error): ?><p class="error"><?= admin_h($error) ?></p><?php endif; ?>
        <section class="panel">
            <h2><?= $edit ? 'Edit' : 'Add' ?> product</h2>
            <form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= admin_h($_SESSION['admin_csrf']) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>"><label>Product name<input name="pname" required maxlength="150" value="<?= admin_h($edit['pname'] ?? '') ?>"></label><label>Category<select name="category_id" required>
                        <option value="">Select category</option><?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= ((int)($edit['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= admin_h($c['name']) ?></option><?php endforeach; ?>
                    </select></label><label>Price<input name="price" type="number" min="0" step="0.01" required value="<?= admin_h($edit['price'] ?? '') ?>"></label><label>Description<textarea name="description" rows="3"><?= admin_h($edit['description'] ?? '') ?></textarea></label><label>Picture (JPG/PNG/GIF/WebP, max 5 MB)<input type="file" name="picture" accept="image/jpeg,image/png,image/gif,image/webp"></label><button>Save product</button><?php if ($edit): ?> <a href="products.php">Cancel</a><?php endif; ?></form>
        </section>
        <section class="panel">
            <h2>Product list</h2>
            <table>
                <thead>
                    <tr>
                        <th>Picture</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($products as $p): ?><tr>
                            <td><?php if ($p['picture']): ?><img class="thumb" src="../uploads/<?= admin_h(basename($p['picture'])) ?>" alt=""><?php endif; ?></td>
                            <td><?= admin_h($p['pname']) ?></td>
                            <td><?= admin_h($p['category_name']) ?></td>
                            <td>₹<?= number_format((float)$p['price'], 2) ?></td>
                            <td><a href="?edit=<?= (int)$p['id'] ?>">Edit</a>
                                <form class="inline" method="post" onsubmit="return confirm('Delete product?')"><input type="hidden" name="csrf" value="<?= admin_h($_SESSION['admin_csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="danger">Delete</button></form>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </section>
    </main>
</body>

</html>