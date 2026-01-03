<?php
// register.php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['login'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation simple
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            // Créer l'utilisateur
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: dashboard.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GitHub · GitHub</title>
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
        
        .register-container {
            max-width: 340px;
            margin: 40px auto;
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            padding: 20px;
        }
        
        .register-container h1 {
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
        
        .error {
            background-color: #ffebe9;
            border: 1px solid #ff7b72;
            color: #cf222e;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        
        .password-requirements {
            font-size: 14px;
            color: #586069;
            margin-top: 8px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #d8dee4;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">GitHub</a>
    </header>
    
    <div class="register-container">
        <h1>Join GitHub</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="login">Username</label>
                <input type="text" id="login" name="login" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-requirements">
                    Make sure it's at least 8 characters.
                </div>
            </div>
            
            <button type="submit" class="btn">Create account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Sign in</a>.
        </div>
    </div>
</body>
</html>