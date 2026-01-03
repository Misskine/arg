<?php
// login.php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Incorrect username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to GitHub · GitHub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f6f8fa;
        }
        
        header {
            background-color: #24292e;
            padding: 16px 32px;
        }
        
        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        
        .login-container {
            max-width: 340px;
            margin: 80px auto;
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            padding: 20px;
        }
        
        .login-container h1 {
            font-size: 24px;
            font-weight: 300;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin: 16px 0;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 400;
        }
        
        input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #2ea44f;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: #2c974b;
        }
        
        .error {
            background-color: #ffebe9;
            border: 1px solid #ff7b72;
            color: #cf222e;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        
        .create-account {
            margin-top: 20px;
            padding: 16px;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            text-align: center;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            color: #586069;
        }
        
        footer a {
            color: #0969da;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">GitHub</a>
    </header>
    
    <div class="login-container">
        <h1>Sign in to GitHub</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="login">Username or email address</label>
                <input type="text" id="login" name="login" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Sign in</button>
        </form>
        
        <div class="create-account">
            New to GitHub? <a href="register.php">Create an account</a>.
        </div>
    </div>
    
    <footer>
        <a href="index.php">Terms</a> · 
        <a href="index.php">Privacy</a> · 
        <a href="index.php">Security</a> · 
        <a href="index.php">Contact GitHub</a>
    </footer>
</body>
</html>