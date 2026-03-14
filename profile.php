<?php
// profile.php - Profil utilisateur
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_GET['user'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$profile_user = $stmt->fetch();

if (!$profile_user) {
    header('Location: dashboard.php');
    exit();
}

// Vérifier l'accès si c'est un personnage ARG
if ($profile_user['is_arg_character']) {
    $stmt = $pdo->prepare("SELECT id FROM player_progress WHERE player_id = ? AND unlocked_user_id = ?");
    $stmt->execute([$_SESSION['user_id'], $profile_user['id']]);
    if (!$stmt->fetch()) {
        echo "<!DOCTYPE html><html><head><title>Private Profile</title></head><body>";
        echo "<h1>This profile is private</h1>";
        echo "<p>You don't have access to this profile yet.</p>";
        echo "<a href='dashboard.php'>Back to Dashboard</a>";
        echo "</body></html>";
        exit();
    }
}

// Récupérer les repositories
$stmt = $pdo->prepare("SELECT * FROM repositories WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->execute([$profile_user['id']]);
$repositories = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile_user['username']); ?> · GitHub</title>
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
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        .search-input {
            flex: 1;
            max-width: 600px;
            padding: 8px 12px;
            border: 1px solid #444;
            border-radius: 6px;
            background-color: #1c1f23;
            color: white;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 16px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 16px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 32px;
        }
        .profile-sidebar {
            position: sticky;
            top: 32px;
            height: fit-content;
        }
        .avatar-large {
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 120px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .profile-name {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .profile-username {
            font-size: 20px;
            color: #586069;
            margin-bottom: 16px;
        }
        .profile-bio {
            margin: 16px 0;
            color: #24292e;
        }
        .profile-content h2 {
            border-bottom: 1px solid #d8dee4;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .repo-card {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .repo-card h3 {
            margin: 0 0 8px 0;
        }
        .repo-card h3 a {
            color: #0969da;
            text-decoration: none;
        }
        .repo-card p {
            color: #586069;
            margin: 8px 0;
        }
        .repo-meta {
            display: flex;
            gap: 16px;
            font-size: 14px;
            color: #586069;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <input type="text" class="search-input" placeholder="Search or jump to..." onclick="window.location.href='search.php'">
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php?user=<?php echo urlencode($current_user['username']); ?>"><?php echo htmlspecialchars($current_user['username']); ?></a>
        </nav>
    </header>
    
    <div class="container">
        <div class="profile-sidebar">
            <div class="avatar-large">
                <?php echo strtoupper(substr($profile_user['username'], 0, 1)); ?>
            </div>
            <div class="profile-name">
                <?php echo htmlspecialchars($profile_user['username']); ?>
            </div>
            <?php if ($profile_user['bio']): ?>
                <div class="profile-bio">
                    <?php echo nl2br(htmlspecialchars($profile_user['bio'])); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-content">
            <h2>Repositories (<?php echo count($repositories); ?>)</h2>
            
            <?php if (empty($repositories)): ?>
                <p>No public repositories.</p>
            <?php else: ?>
                <?php foreach ($repositories as $repo): ?>
                    <div class="repo-card">
                        <h3>
                            <a href="repo.php?id=<?php echo $repo['id']; ?>">
                                <?php echo htmlspecialchars($repo['name']); ?>
                            </a>
                        </h3>
                        <p><?php echo htmlspecialchars($repo['description']); ?></p>
                        <div class="repo-meta">
                            <span>Updated <?php echo time_ago($repo['updated_at']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>