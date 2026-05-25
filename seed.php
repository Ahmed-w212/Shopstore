<?php
// Open this file once after importing database.sql to set some usernames and passwords for testing.
require_once __DIR__ . '/db.php';

$seed_users = [
    ['username' => 'admin', 'password' => 'admin123'],
    ['username' => 'ahmed', 'password' => 'pass123'],
    ['username' => 'jalil',   'password' => 'pass123'],
];

$updated = [];
$errors  = [];

foreach ($seed_users as $u) {
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE username = ?');
    if (!$stmt) {
        $errors[] = 'Prepare failed: ' . $conn->error;
        continue;
    }
    $stmt->bind_param('ss', $hash, $u['username']);
    if ($stmt->execute()) {
        $updated[] = $u['username'];
    } else {
        $errors[] = 'Execute failed for ' . $u['username'] . ': ' . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopstore - Seed Users</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Shopstore</h1></header>
<div class="container">
    <h2>Seed Users</h2>
    <p>This page sets password hashes for the default users.
        running it once then ignore the file.</p>

    <?php if (!empty($updated)): ?>
        <div class="success">
            <strong>Updated passwords for:</strong>
            <?php echo htmlspecialchars(implode(', ', $updated)); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) {
                echo '<div>' . htmlspecialchars($e) . '</div>';
            } ?>
        </div>
    <?php endif; ?>

    <h3>Default credentials</h3>
    <table>
        <tr><th>Username</th><th>Password</th><th>Role</th></tr>
        <tr><td>admin</td><td>admin123</td><td>admin</td></tr>
        <tr><td>ahmed</td><td>pass123</td><td>customer</td></tr>
        <tr><td>jalil</td><td>pass123</td><td>customer</td></tr>
    </table>

    <div class="nav">
        <a href="index.php">Home</a>
    </div>
</div>
</body>
</html>
