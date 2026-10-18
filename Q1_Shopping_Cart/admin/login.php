<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

$error = "";

if (!empty($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, username, password FROM admins WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (
        $admin &&
        (
            password_verify($password, $admin["password"]) ||
            hash_equals($admin["password"], $password)
        )
    ) {
        // Upgrade an existing plain-text password to a secure hash.
        if (!password_verify($password, $admin["password"])) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $update = $conn->prepare(
                "UPDATE admins SET password = ? WHERE id = ?"
            );

            $update->bind_param("si", $hashedPassword, $admin["id"]);
            $update->execute();
            $update->close();
        }

        session_regenerate_id(true);
        $_SESSION["admin_id"] = (int) $admin["id"];
        $_SESSION["admin_username"] = $admin["username"];

        header("Location: dashboard.php");
        exit;
    }

    $error = "Invalid username or password.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <main class="auth">
        <h1>Admin Login</h1>

        <?php if ($error !== ""): ?>
            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form method="post">
            <label>
                Username
                <input
                    type="text"
                    name="username"
                    required
                    autocomplete="username">
            </label>

            <label>
                Password
                <input
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password">
            </label>

            <button type="submit">Login</button>
        </form>

        <p>
            <a href="../index.php">Back to shop</a>
        </p>
    </main>
</body>

</html>