<?php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hash]);
                
                $player_id = $pdo->lastInsertId();
                
                // Débloquer automatiquement le premier personnage ARG
                $stmt = $pdo->prepare("SELECT id FROM users WHERE is_arg_character = TRUE AND unlock_order = 1");
                $stmt->execute();
                $first_char = $stmt->fetch();
                
                if ($first_char) {
                    $stmt = $pdo->prepare("INSERT INTO player_progress (player_id, unlocked_user_id) VALUES (?, ?)");
                    $stmt->execute([$player_id, $first_char['id']]);
                }
                
                $_SESSION['user_id'] = $player_id;
                $_SESSION['username'] = $username;
                header('Location: dashboard.php');
                exit();
            }
        } catch(PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GitHub</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; background-color: #f6f8fa; }
        header { background-color: #24292e; padding: 16px 32px; }
        .logo { color: white; font-size: 24px; font-weight: bold; text-decoration: none; }
        .register-container { max-width: 340px; margin: 80px auto; background: white; border: 1px solid #d8dee4; border-radius: 6px; padding: 20px; }
        .form-group { margin: 16px 0; }
        .btn { width: 100%; padding: 10px; background-color: #2ea44f; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; }
        .error { background-color: #ffebe9; border: 1px solid #ff7b72; color: #cf222e; padding: 12px; border-radius: 6px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <header><a href="index.php" class="logo">GitHub</a></header>
    <div class="register-container">
        <h1 style="text-align: center; font-weight: 300;">Join GitHub</h1>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Create account</button>
        </form>
        <p style="margin-top: 20px; text-align: center;">
            Already have an account? <a href="login.php">Sign in</a>.
        </p>
    </div>
</body>
</html>