<?php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to GitHub · GitHub</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; background-color: #f6f8fa; }
        header { background-color: #24292e; padding: 16px 32px; }
        .logo { color: white; font-size: 24px; font-weight: bold; text-decoration: none; }
        .login-container { max-width: 340px; margin: 80px auto; background: white; border: 1px solid #d8dee4; border-radius: 6px; padding: 20px; }
        .form-group { margin: 16px 0; }
        label { display: block; margin-bottom: 8px; font-weight: 400; }
        input { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background-color: #2ea44f; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
        .error { background-color: #ffebe9; border: 1px solid #ff7b72; color: #cf222e; padding: 12px; border-radius: 6px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <header><a href="index.php" class="logo">GitHub</a></header>
    <div class="login-container">
        <h1 style="text-align: center; font-weight: 300;">Sign in to GitHub</h1>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username or email address</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Sign in</button>
        </form>
        <p style="margin-top: 20px; text-align: center;">
            New to GitHub? <a href="register.php">Create an account</a>.
        </p>
    </div>
</body>
</html>