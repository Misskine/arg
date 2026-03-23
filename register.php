<?php
// register.php
include 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $email && $password) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);
            $player_id = $pdo->lastInsertId();

            // Unlock first ARG character
            $first_char = $pdo->query("SELECT id FROM users WHERE is_arg_character = TRUE AND unlock_order = 1 LIMIT 1")->fetch();
            if ($first_char) {
                $stmt = $pdo->prepare("INSERT INTO player_progress (player_id, unlocked_user_id) VALUES (?, ?)");
                $stmt->execute([$player_id, $first_char['id']]);
            }

            $_SESSION['user_id'] = $player_id;
            $_SESSION['username'] = $username;
            header('Location: dashboard.php');
            exit();
        } catch (PDOException $e) {
            $error = 'Registration failed. Username or email may already be taken.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GitHub · GitHub</title>
    <link rel="stylesheet" href="github-style.css">
</head>
<body>

<header class="github-header">
    <div class="github-header-inner">
        <a href="index.php" class="logo" aria-label="Homepage">
            <svg height="32" aria-hidden="true" viewBox="0 0 16 16" width="32">
                <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
            </svg>
        </a>
    </div>
</header>

<div class="auth-page">
    <div class="auth-logo">
        <svg height="48" viewBox="0 0 16 16" width="48">
            <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
        </svg>
    </div>

    <div class="auth-box">
        <h1>Join GitHub</h1>

        <?php if ($error): ?>
            <div class="flash flash-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       autocomplete="username" autofocus required>
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password" required>
                <p class="input-note">Make sure it's at least 15 characters OR at least 8 characters including a number and a lowercase letter.</p>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Create account</button>
        </form>
    </div>

    <div class="auth-footer">
        Already have an account? <a href="login.php">Sign in</a>.
    </div>
</div>

</body>
</html>