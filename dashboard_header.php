<?php
// dashboard_header.php - Header spécifique au dashboard GitHub
?>
<header>
    <a href="dashboard.php" class="logo">GitHub</a>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="explore.php">Explore</a>
        <a href="notifications.php">Notifications</a>
        <div class="user-menu">
            <div class="avatar"></div>
            <a href="profile.php"><?php echo htmlspecialchars($user['username'] ?? ''); ?></a>
        </div>
    </nav>
</header>