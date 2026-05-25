<?php
require_once __DIR__ . '/db.php';

$sid = isset($_GET['sid']) ? trim($_GET['sid']) : '';
if ($sid !== '' && preg_match('/^[a-f0-9]{32}$/', $sid)) {
    $stmt = $conn->prepare('DELETE FROM sessions WHERE sid = ?');
    $stmt->bind_param('s', $sid);
    $stmt->execute();
    $stmt->close();
}

header('Location: index.php');
exit;
