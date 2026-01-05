<?php
// issue.php - Détails d'une issue
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$issue_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT i.*, r.id as repo_id, r.name as repo_name, r.user_id as repo_owner_id,
           u.username as owner_name, creator.username as creator_name,
           u.is_arg_character
    FROM issues i
    JOIN repositories r ON i.repository_id = r.id
    JOIN users u ON r.user_id = u.id
    JOIN users creator ON i.created_by = creator.id
    WHERE i.id = ?
");
$stmt->execute([$issue_id]);
$issue = $stmt->fetch();

if (!$issue) {
    header('Location: dashboard.php');
    exit();
}

// Vérifier l'accès
if ($issue['is_arg_character']) {
    $stmt = $pdo->prepare("SELECT id FROM player_progress WHERE player_id = ? AND unlocked_user_id = ?");
    $stmt->execute([$_SESSION['user_id'], $issue['repo_owner_id']]);
    if (!$stmt->fetch()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Récupérer les commentaires
$stmt = $pdo->prepare("
    SELECT c.*, u.username as author_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.issue_id = ?
    ORDER BY c.created_at ASC
");
$stmt->execute([$issue_id]);
$comments = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($issue['title']); ?> · Issue</title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        .breadcrumb {
            margin-bottom: 20px;
        }
        .breadcrumb a {
            color: #0969da;
            text-decoration: none;
        }
        .issue-header {
            margin-bottom: 20px;
        }
        .issue-title {
            font-size: 32px;
            font-weight: 400;
            margin-bottom: 8px;
        }
        .issue-meta {
            color: #586069;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        .status-open {
            background-color: #dafbe1;
            color: #1a7f37;
        }
        .status-closed {
            background-color: #ffebe9;
            color: #cf222e;
        }
        .comment {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .comment-header {
            background-color: #f6f8fa;
            padding: 12px 16px;
            border-bottom: 1px solid #d8dee4;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }
        .comment-body {
            padding: 16px;
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
            <a href="profile.php?user=<?php echo urlencode($issue['owner_name']); ?>">
                <?php echo htmlspecialchars($issue['owner_name']); ?>
            </a>
            /
            <a href="repo.php?id=<?php echo $issue['repo_id']; ?>">
                <?php echo htmlspecialchars($issue['repo_name']); ?>
            </a>
            / Issues / #<?php echo $issue['id']; ?>
        </div>
        
        <div class="issue-header">
            <h1 class="issue-title"><?php echo htmlspecialchars($issue['title']); ?></h1>
            <div class="issue-meta">
                <span class="status-badge status-<?php echo $issue['status']; ?>">
                    <?php echo strtoupper($issue['status']); ?>
                </span>
                <strong><?php echo htmlspecialchars($issue['creator_name']); ?></strong> 
                opened this issue <?php echo time_ago($issue['created_at']); ?>
            </div>
        </div>
        
        <div class="comment">
            <div class="comment-header">
                <strong><?php echo htmlspecialchars($issue['creator_name']); ?></strong>
                commented <?php echo time_ago($issue['created_at']); ?>
            </div>
            <div class="comment-body">
                <?php echo nl2br(htmlspecialchars($issue['body'])); ?>
            </div>
        </div>
        
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <div class="comment-header">
                    <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                    commented <?php echo time_ago($comment['created_at']); ?>
                </div>
                <div class="comment-body">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <p>
            <a href="repo.php?id=<?php echo $issue['repo_id']; ?>">← Back to repository</a>
        </p>
    </div>
</body>
</html>