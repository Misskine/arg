<?php
// file.php - Visualisation d'un fichier
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$file_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT rf.*, r.id as repo_id, r.name as repo_name, r.user_id as repo_owner_id, 
           u.username as owner_name, u.is_arg_character
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

// Vérifier l'accès
if ($file['is_arg_character']) {
    $stmt = $pdo->prepare("SELECT id FROM player_progress WHERE player_id = ? AND unlocked_user_id = ?");
    $stmt->execute([$_SESSION['user_id'], $file['repo_owner_id']]);
    if (!$stmt->fetch()) {
        header('Location: dashboard.php');
        exit();
    }
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($file['filename']); ?> · <?php echo htmlspecialchars($file['repo_name']); ?></title>
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
            padding: 16px;
        }
        .breadcrumb {
            margin: 20px 0;
            font-size: 18px;
        }
        .breadcrumb a {
            color: #0969da;
            text-decoration: none;
        }
        .file-header {
            background: white;
            border: 1px solid #d8dee4;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            padding: 12px 16px;
            font-weight: 600;
        }
        .file-content {
            background: #f6f8fa;
            border: 1px solid #d8dee4;
            border-top: none;
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
            padding: 16px;
            overflow-x: auto;
        }
        .line-numbers {
            display: table;
            width: 100%;
        }
        .line {
            display: table-row;
        }
        .line-number {
            display: table-cell;
            padding-right: 16px;
            color: #586069;
            text-align: right;
            user-select: none;
            width: 1%;
        }
        .line-code {
            display: table-cell;
            white-space: pre;
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
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
        <div class="breadcrumb">
            <a href="profile.php?user=<?php echo urlencode($file['owner_name']); ?>">
                <?php echo htmlspecialchars($file['owner_name']); ?>
            </a>
            /
            <a href="repo.php?id=<?php echo $file['repo_id']; ?>">
                <?php echo htmlspecialchars($file['repo_name']); ?>
            </a>
            /
            <?php echo htmlspecialchars($file['filename']); ?>
        </div>
        
        <div class="file-header">
            <?php echo htmlspecialchars($file['filename']); ?>
        </div>
        
        <div class="file-content">
            <?php
            $lines = explode("\n", $file['content']);
            echo '<div class="line-numbers">';
            foreach ($lines as $index => $line) {
                $lineNum = $index + 1;
                echo '<div class="line">';
                echo '<span class="line-number">' . $lineNum . '</span>';
                echo '<span class="line-code">' . htmlspecialchars($line) . '</span>';
                echo '</div>';
            }
            echo '</div>';
            ?>
        </div>
        
        <p style="margin-top: 20px;">
            <a href="repo.php?id=<?php echo $file['repo_id']; ?>">← Back to repository</a>
        </p>
    </div>
</body>
</html>