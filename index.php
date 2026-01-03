<?php
// index.php - Page d'accueil GitHub
include 'config.php';

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub - Let's build from here</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        
        header {
            background-color: #24292e;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 16px;
        }
        
        .hero {
            max-width: 800px;
            margin: 80px auto;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 48px;
            font-weight: 300;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }
        
        .form-group {
            margin: 16px 0;
        }
        
        input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 300px;
            font-size: 16px;
        }
        
        button {
            background-color: #2ea44f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        
        footer {
            margin-top: 100px;
            padding: 40px 0;
            border-top: 1px solid #eaeaea;
            color: #586069;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-top: 64px;
        }
        
        .feature {
            text-align: center;
        }
        
        .feature h3 {
            font-weight: 400;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">GitHub</a>
        <nav>
            <a href="login.php">Sign in</a>
            <a href="register.php">Sign up</a>
        </nav>
    </header>
    
    <div class="hero">
        <h1>Let's build from here</h1>
        <p>The AI-powered developer platform to build, scale, and deliver secure software.</p>
        
        <div style="margin-top: 32px;">
            <a href="login.php" style="background-color: #2ea44f; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block;">
                Sign up for GitHub
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="features">
            <div class="feature">
                <h3>Collaborate</h3>
                <p>Work with millions of developers across the globe.</p>
            </div>
            <div class="feature">
                <h3>Build</h3>
                <p>Create software that changes the world.</p>
            </div>
            <div class="feature">
                <h3>Secure</h3>
                <p>Enterprise-grade security for your projects.</p>
            </div>
        </div>
    </div>
    
    <footer class="container">
        <p>&copy; 2023 GitHub, Inc. All rights reserved.</p>
    </footer>
</body>
</html>