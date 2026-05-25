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
$stmt = $conn->prepare('SELECT id, name, price FROM products ORDER BY id');
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $products[(int)$row['id']] = $row;
}
$stmt->close();

$errors  = [];
$success = '';

$prefill_pid = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity   = filter_input(INPUT_POST, 'quantity',   FILTER_VALIDATE_INT);

    if ($product_id === false || $product_id === null || $product_id <= 0) {
        $errors[] = 'Please choose a valid product.';
    } elseif (!isset($products[$product_id])) {
        $errors[] = 'Selected product does not exist.';
    }

    if ($quantity === false || $quantity === null) {
        $errors[] = 'Quantity must be an integer.';
    } elseif ($quantity < 1 || $quantity > 100) {
        $errors[] = 'Quantity must be between 1 and 100.';
    }

    if (empty($errors)) {
        $price = (float)$products[$product_id]['price'];
        $total = $price * $quantity;

        $ins = $conn->prepare(
            'INSERT INTO orders (user_id, product_id, quantity, total) VALUES (?, ?, ?, ?)'
        );
        $ins->bind_param('iiid', $user['id'], $product_id, $quantity, $total);
        if ($ins->execute()) {
            $success = 'Order placed for ' . $quantity . ' x '
                . $products[$product_id]['name']
                . ' ($' . number_format($total, 2) . ').';
            $prefill_pid = 0;
        } else {
            $errors[] = 'Could not place order: ' . $ins->error;
        }
        $ins->close();
    }
}

$history = [];
$stmt = $conn->prepare(
    'SELECT o.id, p.name, o.quantity, o.total, o.ordered_at
       FROM orders o
       JOIN products p ON p.id = o.product_id
      WHERE o.user_id = ?
      ORDER BY o.ordered_at DESC'
);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();

$sid_q = urlencode($sid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopstore - Place Order</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function validateOrder(form) {
            var pid = parseInt(form.product_id.value, 10);
            var q   = parseInt(form.quantity.value, 10);
            if (!pid || pid <= 0) {
                alert('Please choose a product.');
                return false;
            }
            if (isNaN(q) || q < 1 || q > 100) {
                alert('Quantity must be a whole number between 1 and 100.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<header><h1>Shopstore</h1></header>
<div class="container">
    <h2>Place an Order</h2>
    <p>Signed in as <strong><?php echo htmlspecialchars($user['username']); ?></strong>.</p>

    <div class="sid-banner">
        Session ID in URL: <?php echo htmlspecialchars($sid); ?>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) {
                echo '<div>' . htmlspecialchars($e) . '</div>';
            } ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" action="order.php?sid=<?php echo $sid_q; ?>"
          onsubmit="return validateOrder(this);">
        <label for="product_id">Product</label>
        <select id="product_id" name="product_id" required>
            <option value="">-- choose a product --</option>
            <?php foreach ($products as $pid => $p): ?>
                <option value="<?php echo (int)$pid; ?>"
                    <?php if ($pid === $prefill_pid) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($p['name']); ?>
                    ($<?php echo htmlspecialchars(number_format((float)$p['price'], 2)); ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="quantity">Quantity (1 - 100)</label>
        <input type="number" id="quantity" name="quantity" required min="1" max="100" value="1">

        <button type="submit">Place order</button>
    </form>

    <h3>Your previous orders</h3>
    <?php if (empty($history)): ?>
        <p>No previous orders.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Ordered at</th>
            </tr>
            <?php foreach ($history as $h): ?>
                <tr>
                    <td><?php echo (int)$h['id']; ?></td>
                    <td><?php echo htmlspecialchars($h['name']); ?></td>
                    <td><?php echo (int)$h['quantity']; ?></td>
                    <td>$<?php echo htmlspecialchars(number_format((float)$h['total'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($h['ordered_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

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
