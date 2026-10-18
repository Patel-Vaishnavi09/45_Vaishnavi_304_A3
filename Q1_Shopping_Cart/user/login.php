<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
$error = '';
if (empty($_SESSION['q1_csrf'])) $_SESSION['q1_csrf'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['q1_csrf'], $_POST['csrf'] ?? '')) $error = 'Invalid form. Refresh and try again.';
    else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $s = $conn->prepare('SELECT id,name,password FROM users WHERE email=?');
        $s->bind_param('s', $email);
        $s->execute();
        $u = $s->get_result()->fetch_assoc();
        $s->close();
        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            $_SESSION['user_name'] = $u['name'];
            header('Location: ../index.php');
            exit;
        } else $error = 'Invalid email or password.';
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>User Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <main class="auth">
        <h1>User Login</h1><?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['q1_csrf']) ?>"><label>Email<input type="email" name="email" required></label><label>Password<input type="password" name="password" required></label><button>Login</button></form>
        <p><a href="register.php">Register</a></p>
        <p><a href="../index.php">Back to shop</a></p>
    </main>
</body>

</html>