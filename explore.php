<?php
// explore.php - Page Explore GitHub-like
include 'config.php';

// Récupérer les repositories populaires
$stmt = $pdo->prepare("
    SELECT r.*, u.username as owner_name,
           (SELECT COUNT(*) FROM repository_files rf WHERE rf.repository_id = r.id) as file_count,
           (SELECT COUNT(*) FROM commits c WHERE c.repository_id = r.id) as commit_count,
           (SELECT COUNT(*) FROM issues i WHERE i.repository_id = r.id) as issue_count
    FROM repositories r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 20
");
$stmt->execute();
$repositories = $stmt->fetchAll();

// Récupérer les utilisateurs actifs
$stmt = $pdo->prepare("
    SELECT u.*, COUNT(DISTINCT r.id) as repo_count,
           COUNT(DISTINCT c.id) as commit_count
    FROM users u
    LEFT JOIN repositories r ON r.user_id = u.id
    LEFT JOIN commits c ON c.repository_id = r.id
    GROUP BY u.id
    ORDER BY repo_count DESC, commit_count DESC
    LIMIT 10
");
$stmt->execute();
$active_users = $stmt->fetchAll();

// Récupérer les issues récentes
$stmt = $pdo->prepare("
    SELECT i.*, r.name as repo_name, u.username as owner_name
    FROM issues i
    JOIN repositories r ON i.repository_id = r.id
    JOIN users u ON r.user_id = u.id
    ORDER BY i.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_issues = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore · GitHub</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        
        .section-title {
            font-size: 24px;
            margin: 40px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #e1e4e8;
        }
        
        .repos-grid, .users-grid, .issues-grid {
            display: grid;
            gap: 20px;
        }
        
        .repos-grid {
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }
        
        .users-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
        
        .repo-card, .user-card, .issue-card {
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 16px;
            background: white;
            transition: transform 0.2s;
        }
        
        .repo-card:hover, .user-card:hover, .issue-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        
        .repo-name, .user-name, .issue-title {
            color: #0969da;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }
        
        .repo-desc, .issue-desc {
            color: #586069;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        
        .repo-meta, .user-meta, .issue-meta {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: #586069;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #6e5494, #c9510c);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-bottom: 12px;
        }
        
        .language {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #f1e05a;
            margin-right: 4px;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="explore.php">Explore</a>
                <a href="notifications.php">Notifications</a>
                <div class="user-menu" style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="width: 20px; height: 20px; border-radius: 50%; background: #ddd;"></div>
                    <a href="profile.php" style="color: white; text-decoration: none;">
                        <?php 
                        if (isset($_SESSION['user_id'])) {
                            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $user = $stmt->fetch();
                            echo htmlspecialchars($user['username']);
                        }
                        ?>
                    </a>
                </div>
            <?php else: ?>
                <a href="login.php">Sign in</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <div class="container">
        <h1 style="font-size: 32px; margin-bottom: 32px;">Explore GitHub</h1>
        
        <!-- Trending Repositories -->
        <div>
            <h2 class="section-title">Trending Repositories</h2>
            <div class="repos-grid">
                <?php foreach($repositories as $repo): ?>
                    <div class="repo-card">
                        <a href="repo.php?id=<?php echo $repo['id']; ?>" class="repo-name">
                            <?php echo htmlspecialchars($repo['owner_name']); ?>/<?php echo htmlspecialchars($repo['name']); ?>
                        </a>
                        <p class="repo-desc">
                            <?php echo htmlspecialchars($repo['description'] ?: 'No description'); ?>
                        </p>
                        <div class="repo-meta">
                            <span>
                                <span class="language"></span>
                                PHP
                            </span>
                            <span>⭐ 0</span>
                            <span>📁 <?php echo $repo['file_count']; ?></span>
                            <span>🔀 <?php echo $repo['commit_count']; ?></span>
                            <span>📝 <?php echo $repo['issue_count']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Popular Developers -->
        <div>
            <h2 class="section-title">Popular Developers</h2>
            <div class="users-grid">
                <?php foreach($active_users as $user): ?>
                    <div class="user-card">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <a href="profile.php?user=<?php echo urlencode($user['username']); ?>" class="user-name">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <div class="user-meta">
                            <span>📁 <?php echo $user['repo_count']; ?> repos</span>
                            <span>🔀 <?php echo $user['commit_count']; ?> commits</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Recent Issues -->
        <div>
            <h2 class="section-title">Recent Issues</h2>
            <div class="issues-grid">
                <?php foreach($recent_issues as $issue): ?>
                    <div class="issue-card">
                        <a href="issue.php?id=<?php echo $issue['id']; ?>" class="issue-title">
                            <?php echo htmlspecialchars($issue['title']); ?>
                        </a>
                        <p class="issue-desc">
                            <?php 
                            $desc = $issue['description'];
                            echo htmlspecialchars(strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc);
                            ?>
                        </p>
                        <div class="issue-meta">
                            <span>
                                in <a href="repo.php?id=<?php echo $issue['repository_id']; ?>" style="color: #0969da;">
                                    <?php echo htmlspecialchars($issue['repo_name']); ?>
                                </a>
                            </span>
                            <span>by <?php echo htmlspecialchars($issue['owner_name']); ?></span>
                            <span><?php echo time_ago($issue['created_at']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>