<?php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopstore - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header><h1>Shopstore</h1></header>
<div class="container">
    <h2>Welcome to Shopstore</h2>
    <p>Shopstore is a small online store. We sell computer accessories
       and demonstrates the <strong>Weak Session IDs</strong> vulnerability</p>

    <p>Pick an option to continue:</p>
    <ul>
        <li><a href="login.php">Login</a> &mdash; existing users</li>
        <li><a href="register.php">Register</a> &mdash; new users</li>
    </ul>

    <div class="nav">
        <a href="index.php">Home</a> |
        <a href="login.php">Login</a> |
        <a href="register.php">Register</a>
    </div>

</div>
</body>
</html>
