<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
$error = '';
$success = '';
if (empty($_SESSION['captcha_a']) || empty($_SESSION['captcha_b'])) {
    $_SESSION['captcha_a'] = random_int(1, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');
    $answer = (string)($_SESSION['captcha_a'] + $_SESSION['captcha_b']);
    if (!hash_equals($_SESSION['q1_csrf'] ?? '', $_POST['csrf'] ?? '')) $error = 'Invalid form. Refresh the page.';
    elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) $error = 'Enter a name, valid email, and password with at least 8 characters.';
    elseif ($captcha !== $answer) $error = 'Incorrect CAPTCHA answer.';
    else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $s = $conn->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)');
        $s->bind_param('sss', $name, $email, $hash);
        if ($s->execute()) {
            $success = 'Registration successful. You can now log in.';
            unset($_SESSION['captcha_a'], $_SESSION['captcha_b']);
        } else {
            $error = $s->errno === 1062 ? 'This email is already registered.' : 'Registration failed.';
        }
        $s->close();
    }
}
if (empty($_SESSION['q1_csrf'])) $_SESSION['q1_csrf'] = bin2hex(random_bytes(32));
$a = $_SESSION['captcha_a'] ?? random_int(1, 9);
$b = $_SESSION['captcha_b'] ?? random_int(1, 9);
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <main class="auth">
        <h1>User Registration</h1><?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?><?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['q1_csrf']) ?>"><label>Name<input name="name" required></label><label>Email<input type="email" name="email" required></label><label>Password (minimum 8 characters)<input type="password" name="password" minlength="8" required></label><label>CAPTCHA: <?= $a ?> + <?= $b ?> = ?<input name="captcha" required inputmode="numeric"></label><button>Register</button></form>
        <p><a href="login.php">Already registered? Login</a></p>
        <p><a href="../index.php">Back to shop</a></p>
    </main>
</body>

</html>