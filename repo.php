<?php
// repo.php
include 'config.php';

$repo_id = $_GET['id'] ?? 1;

// Récupérer le repository
$stmt = $pdo->prepare("
    SELECT r.*, u.username as owner_name,
           v.name as victim_name, v.role as victim_role, v.disappearance_date
    FROM repositories r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN victims v ON r.victim_id = v.id
    WHERE r.id = ?
");
$stmt->execute([$repo_id]);
$repo = $stmt->fetch();

if (!$repo) {
    header('Location: dashboard.php');
    exit();
}

// Récupérer les fichiers
$stmt = $pdo->prepare("SELECT * FROM repository_files WHERE repository_id = ?");
$stmt->execute([$repo_id]);
$files = $stmt->fetchAll();

// Récupérer les commits
$stmt = $pdo->prepare("SELECT * FROM commits WHERE repository_id = ? ORDER BY committed_at DESC");
$stmt->execute([$repo_id]);
$commits = $stmt->fetchAll();

// Récupérer les issues
$stmt = $pdo->prepare("SELECT * FROM issues WHERE repository_id = ? ORDER BY created_at DESC");
$stmt->execute([$repo_id]);
$issues = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($repo['name']); ?> · <?php echo htmlspecialchars($repo['owner_name']); ?>/<?php echo htmlspecialchars($repo['name']); ?></title>
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
            padding: 0 16px;
        }
        
        .repo-header {
            padding: 32px 0;
            border-bottom: 1px solid #e1e4e8;
        }
        
        .repo-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .repo-owner {
            color: #0969da;
            font-weight: 600;
        }
        
        .repo-name {
            color: #0969da;
            font-weight: 600;
            font-size: 20px;
        }
        
        .repo-description {
            color: #24292e;
            margin: 16px 0;
        }
        
        .repo-nav {
            display: flex;
            gap: 16px;
            margin-top: 24px;
        }
        
        .repo-nav a {
            padding: 8px 16px;
            text-decoration: none;
            color: #24292e;
            border-bottom: 2px solid transparent;
        }
        
        .repo-nav a.active {
            border-bottom-color: #f9826c;
            font-weight: 600;
        }
        
        .file-browser {
            margin: 32px 0;
        }
        
        .file-item {
            padding: 12px 16px;
            border: 1px solid #e1e4e8;
            border-top: none;
            display: flex;
            align-items: center;
        }
        
        .file-item:first-child {
            border-top: 1px solid #e1e4e8;
            border-radius: 6px 6px 0 0;
        }
        
        .file-item:last-child {
            border-radius: 0 0 6px 6px;
        }
        
        .file-icon {
            margin-right: 8px;
        }
        
        .file-name {
            color: #0969da;
            text-decoration: none;
        }
        
        .code-viewer {
            background: #f6f8fa;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            margin: 32px 0;
            overflow: hidden;
        }
        
        .code-header {
            background: white;
            border-bottom: 1px solid #e1e4e8;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        pre {
            margin: 0;
            padding: 16px;
            overflow-x: auto;
        }
        
        code {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 14px;
        }
        
        .commit {
            padding: 16px;
            border: 1px solid #e1e4e8;
            border-top: none;
        }
        
        .commit:first-child {
            border-top: 1px solid #e1e4e8;
            border-radius: 6px 6px 0 0;
        }
        
        .commit:last-child {
            border-radius: 0 0 6px 6px;
        }
        
        .commit-hash {
            font-family: monospace;
            color: #586069;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Sign out</a>
            <?php else: ?>
                <a href="login.php">Sign in</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <div class="container">
        <div class="repo-header">
            <div class="repo-title">
            <span class="repo-owner">
                <a href="profile.php?user=<?php echo urlencode($repo['owner_name']); ?>" 
                style="color: #0969da; text-decoration: none;">
                    <?php echo htmlspecialchars($repo['owner_name']); ?>
                </a>
            </span>
            <span>/</span>
            <span class="repo-name"><?php echo htmlspecialchars($repo['name']); ?></span>
        </div>
            
            <p class="repo-description"><?php echo htmlspecialchars($repo['description']); ?></p>
            
            <?php if (!empty($repo['victim_name'])): ?>
                <div style="background: #fff8c5; border: 1px solid #f0c420; padding: 12px; border-radius: 6px; margin-top: 16px;">
                    <strong>Account inactive:</strong> This user hasn't contributed in over 90 days.
                </div>
            <?php endif; ?>
            
            <div class="repo-nav">
                <a href="#" class="active">Code</a>
                <a href="#issues">Issues</a>
                <a href="#commits">Commits</a>
            </div>
        </div>
        
        <div class="file-browser">
            <?php foreach ($files as $file): ?>
                <div class="file-item">
                    <span class="file-icon">📄</span>
                    <a href="file.php?id=<?php echo $file['id']; ?>" class="file-name">
                        <?php echo htmlspecialchars($file['filename']); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div id="commits">
            <h3>Recent commits</h3>
            <?php foreach ($commits as $commit): ?>
                <div class="commit">
                    <div class="commit-hash"><?php echo substr($commit['commit_hash'], 0, 8); ?></div>
                    <div><?php echo htmlspecialchars($commit['message']); ?></div>
                    <div style="color: #586069; font-size: 14px; margin-top: 8px;">
                        <?php echo date('M d, Y', strtotime($commit['committed_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>