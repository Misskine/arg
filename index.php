<?php
// index.php - Page d'accueil publique
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #24292e;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        .header-buttons a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 16px;
            border: 1px solid #fff;
            border-radius: 6px;
        }
        .hero {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .cta-button {
            background-color: #2ea44f;
            color: white;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 18px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">GitHub</a>
        <div class="header-buttons">
            <a href="login.php">Sign in</a>
            <a href="register.php">Sign up</a>
        </div>
    </header>
    
    <div class="hero">
        <h1>Where developers build and discover</h1>
        <p>Millions of developers use GitHub to build personal projects, support their businesses, and work together.</p>
        <p>But sometimes, there are secrets hidden in the code...</p>
        <a href="register.php" class="cta-button">Sign up for GitHub ARG</a>
    </div>
</body>
</html>