<?php
// login.php - validates credentials and creates session ID which is passed via the URL as ?sid=... 

require_once __DIR__ . '/db.php';

$errors = [];
$old    = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password']       : '';
    $old['username'] = $username;

    if ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    }
    if ($username !== '' && !preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $errors[] = 'Username contains invalid characters.';
    }

    if (empty($errors)) {
        
        $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $sid = md5($user['username']);

            $del = $conn->prepare('DELETE FROM sessions WHERE user_id = ?');
            $del->bind_param('i', $user['id']);
            $del->execute();
            $del->close();

            $ins = $conn->prepare('INSERT INTO sessions (sid, user_id) VALUES (?, ?)');
            $ins->bind_param('si', $sid, $user['id']);
            $ins->execute();
            $ins->close();

            header('Location: products.php?sid=' . urlencode($sid));
            exit;
        }

        $errors[] = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shopstore - Login</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function validateLogin(form) {
            var u = form.username.value.trim();
            var p = form.password.value;
            if (u === '' || p === '') {
                alert('Please enter both username and password.');
                return false;
            }
            if (!/^[A-Za-z0-9_]{3,50}$/.test(u)) {
                alert('Username must be 3-50 chars: letters, digits, underscore.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<header><h1>Shopstore</h1></header>
<div class="container">
    <h2>Login</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) {
                echo '<div>' . htmlspecialchars($e) . '</div>';
            } ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" onsubmit="return validateLogin(this);">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required
               minlength="3" maxlength="50"
               pattern="[A-Za-z0-9_]{3,50}"
               value="<?php echo htmlspecialchars($old['username']); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required
               minlength="6" maxlength="100">

        <button type="submit">Sign in</button>
    </form>

    <p>No account yet? <a href="register.php">Create one</a>.</p>

    <div class="nav">
        <a href="index.php">Home</a> |
        <a href="login.php">Login</a> |
        <a href="register.php">Register</a>
    </div>
    
</div>
</body>
</html>
