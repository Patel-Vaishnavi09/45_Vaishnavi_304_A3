<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}
admin_csrf_check();
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
session_regenerate_id(true);
header('Location: login.php');
exit;
