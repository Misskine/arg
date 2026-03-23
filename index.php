<?php
// index.php - Page d'accueil publique
?>
<!DOCTYPE html>
<html lang="en" data-color-mode="light" data-light-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub: Let's build from here</title>
    <link rel="stylesheet" href="github-style.css">
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="github-header">
    <div class="github-header-inner">
        <a href="index.php" class="logo" aria-label="Homepage">
            <svg height="32" aria-hidden="true" viewBox="0 0 16 16" width="32">
                <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
            </svg>
        </a>
        <nav>
            <a href="#">Product</a>
            <a href="#">Solutions</a>
            <a href="#">Open Source</a>
            <a href="#">Pricing</a>
        </nav>
        <div class="header-search">
            <input type="text" placeholder="Search or jump to..." aria-label="Search GitHub">
        </div>
        <div class="user-menu">
            <a href="login.php">Sign in</a>
            <a href="register.php" class="btn btn-primary btn-sm" style="padding: 5px 16px; font-size: 14px;">Sign up</a>
        </div>
    </div>
</header>

<!-- ===== HERO ===== -->
<section class="hero">
    <div style="position: relative; z-index: 1;">
        <h1>Let's build from <em>here</em></h1>
        <p>The world's leading AI-powered developer platform. Millions of developers use GitHub to build personal projects, support their businesses, and work together.</p>
        <div class="hero-cta">
            <a href="register.php" class="btn btn-primary btn-large">Sign up for GitHub</a>
            <a href="login.php" class="hero-sign-in">Sign in</a>
        </div>
    </div>
</section>

<!-- ===== FEATURES ===== -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <h3>🔐 Secure by default</h3>
                <p>Protect your code with enterprise-level security features, private repositories, and role-based access control.</p>
            </div>
            <div class="feature-card">
                <h3>🤝 Collaborate at scale</h3>
                <p>Pull requests, code reviews, and issues — built-in tools that make collaboration seamless for teams of any size.</p>
            </div>
            <div class="feature-card">
                <h3>🚀 Ship faster</h3>
                <p>Automate your workflow with GitHub Actions, CI/CD pipelines, and integrations with your favorite tools.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
    <div class="container">
        <div class="footer-links">
            <a href="#">© 2024 GitHub, Inc.</a>
            <a href="#">Terms</a>
            <a href="#">Privacy</a>
            <a href="#">Security</a>
            <a href="#">Status</a>
            <a href="#">Docs</a>
            <a href="#">Contact</a>
        </div>
    </div>
</footer>

</body>
</html>