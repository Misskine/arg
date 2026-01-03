<?php
// notifications.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les notifications (pour l'instant vide)
$notifications = [];

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications · GitHub</title>
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
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        
        .notification-item {
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 16px;
            background: white;
        }
        
        .notification-empty {
            text-align: center;
            padding: 48px;
            color: #586069;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="explore.php">Explore</a>
            <a href="notifications.php" style="font-weight: bold;">Notifications</a>
            <div class="user-menu" style="display: inline-flex; align-items: center; gap: 8px;">
                <div style="width: 20px; height: 20px; border-radius: 50%; background: #ddd;"></div>
                <a href="profile.php" style="color: white; text-decoration: none;">
                    <?php echo htmlspecialchars($user['username']); ?>
                </a>
            </div>
        </nav>
    </header>
    
    <div class="container">
        <h1 style="margin-bottom: 24px;">Notifications</h1>
        
        <?php if (empty($notifications)): ?>
            <div class="notification-empty">
                <p>You don't have any notifications.</p>
                <p style="color: #586069; font-size: 14px; margin-top: 8px;">
                    Notifications for new issues, mentions, and comments will appear here.
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                    <!-- Template pour les notifications futures -->
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>