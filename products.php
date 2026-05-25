<?php
require_once __DIR__ . '/db.php';

$sid = isset($_GET['sid']) ? trim($_GET['sid']) : '';
if ($sid === '' || !preg_match('/^[a-f0-9]{32}$/', $sid)) {
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare(
    'SELECT u.id, u.username, u.role
       FROM sessions s
       JOIN users u ON u.id = s.user_id
      WHERE s.sid = ?'
);
$stmt->bind_param('s', $sid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: login.php');
    exit;
}

$products = [];
$stmt = $conn->prepare('SELECT id, name, description, price FROM products ORDER BY id');
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

$sid_q = urlencode($sid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopstore - Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Shopstore</h1></header>
<div class="container">
    <h2>Product Catalog</h2>
    <p>Signed in as <strong><?php echo htmlspecialchars($user['username']); ?></strong>
       (<?php echo htmlspecialchars($user['role']); ?>).</p>

    <div class="sid-banner">
        Session ID in URL: <?php echo htmlspecialchars($sid); ?>
    </div>

    <table>
        <tr>
            <th>Product</th>
            <th>Description</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['description']); ?></td>
                <td class="product-price">$<?php echo htmlspecialchars(number_format((float)$p['price'], 2)); ?></td>
                <td>
                    <a href="order.php?sid=<?php echo $sid_q; ?>&product_id=<?php echo (int)$p['id']; ?>">Order</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="nav">
        <a href="index.php">Home</a> |
        <a href="products.php?sid=<?php echo $sid_q; ?>">Products</a> |
        <a href="order.php?sid=<?php echo $sid_q; ?>">Order</a> |
        <?php if ($user['role'] === 'admin'): ?>
            <a href="admin.php?sid=<?php echo $sid_q; ?>">Admin</a> |
        <?php endif; ?>
        <a href="logout.php?sid=<?php echo $sid_q; ?>">Logout</a>
    </div>
    
</div>
</body>
</html>
