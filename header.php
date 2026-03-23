<?php
// header.php — GitHub-style header include
// Usage: include 'header.php';   ($page_title optionally set before include)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Count unread messages
$unread = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE recipient_id = ? AND is_read = FALSE");
    $stmt->execute([$_SESSION['user_id']]);
    $unread = (int)($stmt->fetch()['count'] ?? 0);
} catch (Exception $e) {}

$current_username = $_SESSION['username'] ?? 'User';
$title = isset($page_title) ? $page_title . ' · GitHub' : 'GitHub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="github-style.css">
</head>
<body>

<header class="github-header">
    <div class="github-header-inner">
        <!-- Logo -->
        <a href="dashboard.php" class="logo" aria-label="Homepage">
            <svg height="32" aria-hidden="true" viewBox="0 0 16 16" width="32">
                <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
            </svg>
        </a>

        <!-- Search -->
        <div class="header-search">
            <form action="search.php" method="GET">
                <input type="text" name="q"
                       placeholder="Search or jump to..."
                       value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
                       aria-label="Search GitHub">
            </form>
        </div>

        <!-- Nav links -->
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="explore.php">Explore</a>
            <a href="notifications.php">
                Notifications
                <?php if ($unread > 0): ?>
                    <span class="notification-badge"><?php echo $unread; ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <!-- User menu -->
        <div class="user-menu">
            <a href="profile.php?user=<?php echo urlencode($current_username); ?>">
                <div class="avatar" aria-label="<?php echo htmlspecialchars($current_username); ?>">
                    <?php echo strtoupper(substr($current_username, 0, 1)); ?>
                </div>
            </a>
            <a href="profile.php?user=<?php echo urlencode($current_username); ?>">
                <?php echo htmlspecialchars($current_username); ?>
            </a>
            <a href="logout.php" title="Sign out" style="color: rgba(255,255,255,0.5);">↩</a>
        </div>
    </div>
</header>