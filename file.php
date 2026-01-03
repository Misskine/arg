<?php
// file.php
include 'config.php';

$file_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT rf.*, r.name as repo_name, u.username as owner_name
    FROM repository_files rf
    JOIN repositories r ON rf.repository_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE rf.id = ?
");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if (!$file) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($file['filename']); ?> · <?php echo htmlspecialchars($file['owner_name']); ?>/<?php echo htmlspecialchars($file['repo_name']); ?></title>
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
        
        .file-header {
            padding: 32px 0;
            border-bottom: 1px solid #e1e4e8;
        }
        
        .file-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .file-path {
            color: #586069;
            font-size: 14px;
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
            background: white;
        }
        
        code {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .line-number {
            color: #6a737d;
            text-align: right;
            padding-right: 16px;
            user-select: none;
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
        <div class="file-header">
            <div class="file-title">
                <strong><?php echo htmlspecialchars($file['filename']); ?></strong>
            </div>
            <div class="file-path">
                <a href="profile.php?user=<?php echo urlencode($file['owner_name']); ?>" 
                style="color: #586069; text-decoration: none;">
                    <?php echo htmlspecialchars($file['owner_name']); ?>
                </a>/<?php echo htmlspecialchars($file['repo_name']); ?>
            </div>
        
        <div class="code-viewer">
            <div class="code-header">
                <span><?php echo htmlspecialchars($file['filename']); ?></span>
            </div>
            <pre><code><?php echo htmlspecialchars($file['content']); ?></code></pre>
        </div>
        
 
    </div>
</body>
</html>