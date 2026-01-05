<?php if (!isset($_SESSION['user_id'])): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="github-style.css">
</head>
<body>
    <header class="github-header">
        <a href="index.php" class="logo">
            <svg height="32" viewBox="0 0 16 16" width="32">
                <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path>
            </svg>
            <span>GitHub</span>
        </a>
        <nav>
            <a href="explore.php">Explore</a>
            <a href="marketplace.php">Marketplace</a>
            <a href="pricing.php">Pricing</a>
        </nav>
        <div class="header-search">
            <input type="text" placeholder="Search or jump to...">
        </div>
        <div class="user-menu">
            <a href="login.php" class="btn btn-sm">Sign in</a>
            <a href="register.php" class="btn btn-sm btn-primary">Sign up</a>
        </div>
    </header>
<?php else: ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' · GitHub' : 'GitHub'; ?></title>
    <link rel="stylesheet" href="github-style.css">
</head>
<body>
    <header class="github-header">
        <a href="dashboard.php" class="logo">
            <svg height="32" viewBox="0 0 16 16" width="32">
                <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path>
            </svg>
            <span>GitHub</span>
        </a>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="explore.php">Explore</a>
            <a href="notifications.php">
                Notifications
                <?php 
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE recipient_id = ? AND is_read = FALSE");
                $stmt->execute([$_SESSION['user_id']]);
                $unread = $stmt->fetch()['count'];
                if ($unread > 0): ?>
                <span style="background: #1f6feb; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px;">
                    <?php echo $unread; ?>
                </span>
                <?php endif; ?>
            </a>
        </nav>
        <div class="header-search">
            <form action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search or jump to..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
            </form>
        </div>
        <div class="user-menu">
            <a href="messages.php" style="position: relative;">
                <svg height="16" viewBox="0 0 16 16" width="16" fill="currentColor">
                    <path d="M0 2.75C0 1.784.784 1 1.75 1h12.5c.966 0 1.75.784 1.75 1.75v7.5A1.75 1.75 0 0114.25 12h-3.427a.25.25 0 00-.177.073l-2.116 2.116A.25.25 0 018 14.25v-2.177a.25.25 0 00-.073-.177L5.427 9.427A.25.25 0 005.25 9H1.75A1.75 1.75 0 010 7.25v-4.5z"></path>
                </svg>
            </a>
            <div class="avatar">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <div style="position: relative;">
                <svg height="16" viewBox="0 0 16 16" width="16" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 0a8 8 0 110 16A8 8 0 018 0zM4.5 7.5a.5.5 0 000 1h7a.5.5 0 000-1h-7z"></path>
                </svg>
                <div style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid #d0d7de; border-radius: 6px; padding: 8px 0; min-width: 160px; display: none;">
                    <a href="profile.php?user=<?php echo $_SESSION['username']; ?>" class="dropdown-item">Your profile</a>
                    <a href="settings.php" class="dropdown-item">Settings</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">Sign out</a>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>