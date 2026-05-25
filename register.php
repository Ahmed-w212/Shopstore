<?php
require_once __DIR__ . '/db.php';

$errors  = [];
$success = '';
$old     = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
    $password = isset($_POST['password']) ? $_POST['password']       : '';

    $old['username'] = $username;
    $old['email']    = $email;

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if ($username !== '' && !preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $errors[] = 'Username may contain only letters, digits and underscores.';
    }
    if ($username !== '' && (strlen($username) < 3 || strlen($username) > 50)) {
        $errors[] = 'Username must be 3 to 50 characters long.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is not valid.';
    }
    if ($email !== '' && strlen($email) > 100) {
        $errors[] = 'Email must be at most 100 characters.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Username or email is already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, "customer")'
        );
        $stmt->bind_param('sss', $username, $email, $hash);
        if ($stmt->execute()) {
            $success = 'Account created. You may now log in.';
            $old = ['username' => '', 'email' => ''];
        } else {
            $errors[] = 'Could not create account: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopstore - Register</title>
    <link rel="stylesheet" href="style.css">
    <script>
        
        function validateRegister(form) {
            var u = form.username.value.trim();
            var e = form.email.value.trim();
            var p = form.password.value;
            if (u === '' || e === '' || p === '') {
                alert('All fields are required.');
                return false;
            }
            if (!/^[A-Za-z0-9_]{3,50}$/.test(u)) {
                alert('Username must be 3-50 chars: letters, digits, underscore.');
                return false;
            }
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRe.test(e)) {
                alert('Please enter a valid email address.');
                return false;
            }
            if (p.length < 6) {
                alert('Password must be at least 6 characters.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<header><h1>Shopstore</h1></header>
<div class="container">
    <h2>Register</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) {
                echo '<div>' . htmlspecialchars($e) . '</div>';
            } ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?>
            <br><a href="login.php">Go to login</a>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php" onsubmit="return validateRegister(this);">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required
               minlength="3" maxlength="50"
               pattern="[A-Za-z0-9_]{3,50}"
               value="<?php echo htmlspecialchars($old['username']); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required maxlength="100"
               value="<?php echo htmlspecialchars($old['email']); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required
               minlength="6" maxlength="100">

        <button type="submit">Create account</button>
    </form>

    <div class="nav">
        <a href="index.php">Home</a> |
        <a href="login.php">Login</a> |
        <a href="register.php">Register</a>
    </div>
    
</div>
</body>
</html>
