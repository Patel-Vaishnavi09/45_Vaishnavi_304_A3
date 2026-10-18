<?php
session_start();
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');
function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
$cat = max(0, (int)($_GET['category'] ?? 0));
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 6;
if ($cat) {
    $s = $conn->prepare('SELECT COUNT(*) total FROM products WHERE category_id=?');
    $s->bind_param('i', $cat);
} else {
    $s = $conn->prepare('SELECT COUNT(*) total FROM products');
}
$s->execute();
$total = (int)$s->get_result()->fetch_assoc()['total'];
$s->close();
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);
$offset = ($page - 1) * $per;
$sql = 'SELECT p.id,p.pname,p.price,p.picture,p.description,c.name category_name FROM products p JOIN categories c ON c.id=p.category_id ' . ($cat ? 'WHERE p.category_id=? ' : '') . 'ORDER BY p.id DESC LIMIT ? OFFSET ?';
$s = $conn->prepare($sql);
if ($cat) $s->bind_param('iii', $cat, $per, $offset);
else $s->bind_param('ii', $per, $offset);
$s->execute();
$rs = $s->get_result();
$html = '';
while ($p = $rs->fetch_assoc()) {
    $html .= '<article class="product">';
    if ($p['picture'] && is_file(__DIR__ . '/uploads/' . basename($p['picture']))) {
        $html .= '<img src="uploads/' . h(basename($p['picture'])) . '" alt="' . h($p['pname']) . '">';
    } else {
        $html .= '<div class="noimage">No picture</div>';
    }
    $html .= '<div class="pbody"><small>' . h($p['category_name']) . '</small><h3>' . h($p['pname']) . '</h3><p>' . h($p['description'] ?? '') . '</p><strong>₹' . number_format((float)$p['price'], 2) . '</strong></div></article>';
}
$s->close();
$conn->close();
echo json_encode(['html' => $html, 'total' => $total, 'page' => $page, 'pages' => $pages]);
