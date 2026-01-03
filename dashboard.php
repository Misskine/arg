<?php
// dashboard.php - Version corrigée pour immersion GitHub
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Récupérer les repositories
$stmt = $pdo->prepare("
    SELECT r.*, u.username as owner_name,
           (SELECT COUNT(*) FROM commits c WHERE c.repository_id = r.id) as commit_count
    FROM repositories r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
$stmt->execute();
$repositories = $stmt->fetchAll();

// Solution TEMPORAIRE : Vérifier si la colonne created_by existe dans issues
$table_check = $pdo->query("SHOW COLUMNS FROM issues LIKE 'created_by'")->fetch();

if ($table_check) {
    // Récupérer les Issues de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT i.*, r.name as repo_name 
        FROM issues i 
        JOIN repositories r ON i.repository_id = r.id 
        WHERE i.created_by = ? OR i.assigned_to = ?
        ORDER BY i.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id, $user_id]);
    $recent_issues = $stmt->fetchAll();
} else {
    // Utiliser la table issues normale (sans created_by)
    $stmt = $pdo->prepare("
        SELECT i.*, r.name as repo_name 
        FROM issues i 
        JOIN repositories r ON i.repository_id = r.id 
        ORDER BY i.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_issues = $stmt->fetchAll();
}

// Récupérer la progression
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT r.id) as repo_count,
        COUNT(DISTINCT c.id) as commit_count,
        COUNT(DISTINCT i.id) as issue_count
    FROM repositories r
    LEFT JOIN commits c ON c.repository_id = r.id
    LEFT JOIN issues i ON i.repository_id = r.id
    WHERE r.user_id = ?
");
$stmt->execute([$user_id]);
$progress = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Dashboard</title>
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
        
        nav {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #ddd;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }
        
        .dashboard-header {
            margin: 32px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .repositories {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 16px;
        }
        
        .repository {
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 16px;
        }
        
        .repository h3 {
            margin: 0 0 8px 0;
        }
        
        .repository h3 a {
            color: #0969da;
            text-decoration: none;
        }
        
        .repository p {
            color: #586069;
            margin: 8px 0;
        }
        
        .repo-meta {
            display: flex;
            gap: 16px;
            font-size: 14px;
            color: #586069;
        }
        
        .language {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #f1e05a;
            margin-right: 4px;
        }
        
        /* Styles pour la nouvelle section */
        .activity-feed {
            margin-top: 40px;
            padding: 20px;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
        }
        
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #eaeaea;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            font-size: 18px;
        }
        
        .activity-text a {
            color: #0969da;
            text-decoration: none;
        }
        
        .activity-time {
            margin-left: auto;
            color: #586069;
            font-size: 14px;
        }
        
        .progress-stats {
            display: flex;
            gap: 40px;
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 32px;
            font-weight: bold;
            color: #24292e;
        }
        
        .stat-label {
            color: #586069;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="explore.php">Explore</a>
            <a href="notifications.php">Notifications</a>
            <div class="user-menu">
                <div class="avatar"></div>
                <a href="profile.php"><?php echo htmlspecialchars($user['username']); ?></a>
            </div>
        </nav>
    </header>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Repositories</h1>
            <a href="new-repo.php" style="background-color: #2ea44f; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none;">
                New
            </a>
        </div>
        
        <div class="repositories">
            <?php foreach ($repositories as $repo): ?>
                <div class="repository">
                    <h3>
                        <a href="repo.php?id=<?php echo $repo['id']; ?>">
                            <?php echo htmlspecialchars($repo['name']); ?>
                        </a>
                    </h3>
                    <p><?php echo htmlspecialchars($repo['description']); ?></p>
                    <div class="repo-meta">
                        <span>
                            <span class="language"></span>
                            <?php 
                            // Déterminer le langage du repo
                            $lang_stmt = $pdo->prepare("SELECT language FROM repository_files WHERE repository_id = ? LIMIT 1");
                            $lang_stmt->execute([$repo['id']]);
                            $lang = $lang_stmt->fetch();
                            echo $lang ? htmlspecialchars($lang['language']) : 'PHP';
                            ?>
                        </span>
                        <span>
                            Par <a href="profile.php?user=<?php echo urlencode($repo['owner_name']); ?>" 
                                   style="color: #586069; text-decoration: none;">
                                <?php echo htmlspecialchars($repo['owner_name']); ?>
                            </a>
                        </span>
                        <span>Updated <?php echo date('M d', strtotime($repo['created_at'])); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="activity-feed">
            <h3>Recent Activity</h3>
            <?php if(empty($recent_issues)): ?>
                <p>No recent activity.</p>
            <?php else: ?>
                <?php foreach($recent_issues as $issue): ?>
                    <div class="activity-item">
                        <span class="activity-icon">📝</span>
                        <span class="activity-text">
                            <?php 
                            if (isset($issue['created_by']) && $issue['created_by'] == $user_id) {
                                echo 'Opened issue in ';
                            } else {
                                echo 'Commented on issue in ';
                            }
                            ?>
                            <a href="repo.php?id=<?php echo $issue['repository_id']; ?>">
                            <?php echo htmlspecialchars($issue['repo_name']); ?></a>
                        </span>
                        <span class="activity-time">
                            <?php echo time_ago($issue['created_at']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="progress-stats">
            <div class="stat">
                <span class="stat-number"><?php echo $progress['repo_count'] ?? 0; ?></span>
                <span class="stat-label">Repositories</span>
            </div>
            <div class="stat">
                <span class="stat-number"><?php echo $progress['commit_count'] ?? 0; ?></span>
                <span class="stat-label">Commits</span>
            </div>
            <div class="stat">
                <span class="stat-number"><?php echo $progress['issue_count'] ?? 0; ?></span>
                <span class="stat-label">Issues</span>
            </div>
        </div>
    </div>
</body>
</html>