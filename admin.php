<?php
// admin-only view of users and orders.

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

$sid_q = urlencode($sid);

if ($user['role'] !== 'admin') {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Shopstore - Access denied</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <header><h1>Shopstore</h1></header>
    <div class="container">
        <h2>403 - Access denied</h2>
        <div class="error">
            You do not have permission to view the admin panel.
        </div>
        <div class="nav">
            <a href="products.php?sid=<?php echo $sid_q; ?>">Products</a> |
            <a href="logout.php?sid=<?php echo $sid_q; ?>">Logout</a>
        </div>
        <div class="arrows">
            <a href="products.php?sid=<?php echo $sid_q; ?>">&larr; Back to products</a>
            <span></span>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Load all users.
$users = [];
$stmt = $conn->prepare('SELECT id, username, email, role, created_at FROM users ORDER BY id');
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

// Load all orders joined with users and products.
$orders = [];
$stmt = $conn->prepare(
    'SELECT o.id, u.username, p.name AS product, o.quantity, o.total, o.ordered_at
       FROM orders o
       JOIN users u    ON u.id = o.user_id
       JOIN products p ON p.id = o.product_id
      ORDER BY o.ordered_at DESC'
);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopstore - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Shopstore</h1></header>
<div class="container">
    <h2>Admin Panel</h2>
    <p>Signed in as <strong><?php echo htmlspecialchars($user['username']); ?></strong> (admin).</p>

    <div class="sid-banner">
        Session ID in URL: <?php echo htmlspecialchars($sid); ?>
    </div>

    <h3>All users</h3>
    <table>
        <tr>
            <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th>
        </tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo (int)$u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['role']); ?></td>
                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>All orders</h3>
    <?php if (empty($orders)): ?>
        <p>No orders yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>#</th><th>User</th><th>Product</th><th>Qty</th><th>Total</th><th>Ordered at</th>
            </tr>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?php echo (int)$o['id']; ?></td>
                    <td><?php echo htmlspecialchars($o['username']); ?></td>
                    <td><?php echo htmlspecialchars($o['product']); ?></td>
                    <td><?php echo (int)$o['quantity']; ?></td>
                    <td>$<?php echo htmlspecialchars(number_format((float)$o['total'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($o['ordered_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="nav">
        <a href="index.php">Home</a> |
        <a href="products.php?sid=<?php echo $sid_q; ?>">Products</a> |
        <a href="order.php?sid=<?php echo $sid_q; ?>">Order</a> |
        <a href="admin.php?sid=<?php echo $sid_q; ?>">Admin</a> |
        <a href="logout.php?sid=<?php echo $sid_q; ?>">Logout</a>
    </div>
    
</div>
</body>
</html>
