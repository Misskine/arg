<?php
// repo.php - Vue détaillée d'un repository
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$repo_id = $_GET['id'] ?? 0;

// Récupérer le repository
$stmt = $pdo->prepare("
    SELECT r.*, u.username as owner_name, u.id as owner_id, u.is_arg_character
    FROM repositories r
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$repo_id]);
$repo = $stmt->fetch();

if (!$repo) {
    header('Location: dashboard.php');
    exit();
}

// Vérifier l'accès si c'est un personnage ARG
if ($repo['is_arg_character']) {
    $stmt = $pdo->prepare("SELECT id FROM player_progress WHERE player_id = ? AND unlocked_user_id = ?");
    $stmt->execute([$_SESSION['user_id'], $repo['owner_id']]);
    if (!$stmt->fetch()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Récupérer les fichiers
$stmt = $pdo->prepare("SELECT * FROM repository_files WHERE repository_id = ? ORDER BY filepath, filename");
$stmt->execute([$repo_id]);
$files = $stmt->fetchAll();

// Récupérer les commits
$stmt = $pdo->prepare("
    SELECT c.*, u.username as author_name
    FROM commits c
    JOIN users u ON c.committed_by = u.id
    WHERE c.repository_id = ?
    ORDER BY c.committed_at DESC
    LIMIT 10
");
$stmt->execute([$repo_id]);
$commits = $stmt->fetchAll();

// Récupérer les issues
$stmt = $pdo->prepare("
    SELECT i.*, u.username as creator_name
    FROM issues i
    JOIN users u ON i.created_by = u.id
    WHERE i.repository_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$repo_id]);
$issues = $stmt->fetchAll();

// Récupérer l'application fonctionnelle si elle existe
$stmt = $pdo->prepare("SELECT * FROM functional_apps WHERE repository_id = ?");
$stmt->execute([$repo_id]);
$app = $stmt->fetch();

// Vérification du code secret
$secret_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['secret_key'])) {
    $entered_key = trim($_POST['secret_key']);
    if ($app && $entered_key === $app['secret_key']) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO found_secrets (player_id, repository_id, secret_key) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $repo_id, $entered_key]);
        $secret_message = 'Correct! Use this code in search: ' . htmlspecialchars($app['unlock_code']);
    } else {
        $secret_message = 'Incorrect key.';
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
    <title><?php echo htmlspecialchars($repo['owner_name'] . '/' . $repo['name']); ?> · GitHub</title>
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
        .repo-header {
            margin: 20px 0;
        }
        .repo-title {
            font-size: 24px;
            color: #0969da;
            text-decoration: none;
        }
        .tabs {
            border-bottom: 1px solid #d8dee4;
            margin: 20px 0;
        }
        .tabs a {
            display: inline-block;
            padding: 12px 16px;
            color: #24292e;
            text-decoration: none;
            border-bottom: 2px solid transparent;
        }
        .tabs a.active {
            border-bottom-color: #fd8c73;
        }
        .file-browser {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            margin: 20px 0;
        }
        .file-item {
            padding: 12px 16px;
            border-bottom: 1px solid #d8dee4;
        }
        .file-item:last-child {
            border-bottom: none;
        }
        .file-item a {
            color: #0969da;
            text-decoration: none;
        }
        .code-viewer {
            background: #f6f8fa;
            padding: 16px;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            margin: 20px 0;
        }
        .code-viewer pre {
            margin: 0;
            overflow-x: auto;
        }
        .commit-list, .issue-list {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            margin: 20px 0;
        }
        .commit-item, .issue-item {
            padding: 12px 16px;
            border-bottom: 1px solid #d8dee4;
        }
        .commit-item:last-child, .issue-item:last-child {
            border-bottom: none;
        }
        .app-link {
            background: #2ea44f;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            margin: 20px 0;
        }
        .secret-form {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .secret-form input {
            padding: 8px 12px;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            width: 300px;
        }
        .secret-form button {
            background: #0969da;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .message {
            padding: 12px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .message.success {
            background: #dafbe1;
            border: 1px solid #2ea44f;
            color: #1a7f37;
        }
        .message.error {
            background: #ffebe9;
            border: 1px solid #ff7b72;
            color: #cf222e;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <input type="text" class="search-input" placeholder="Search or jump to..." onclick="window.location.href='search.php'">
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php"><?php echo htmlspecialchars($current_user['username']); ?></a>
        </nav>
    </header>
    
    <div class="container">
        <div class="repo-header">
            <h1>
                <a href="profile.php?user=<?php echo urlencode($repo['owner_name']); ?>" class="repo-title">
                    <?php echo htmlspecialchars($repo['owner_name']); ?>
                </a>
                / 
                <span class="repo-title"><?php echo htmlspecialchars($repo['name']); ?></span>
            </h1>
            <p><?php echo htmlspecialchars($repo['description']); ?></p>
        </div>
        
        <div class="tabs">
            <a href="#" class="active">Code</a>
            <a href="#issues">Issues (<?php echo count($issues); ?>)</a>
            <a href="#commits">Commits (<?php echo count($commits); ?>)</a>
        </div>
        
        <?php if ($app): ?>
            <a href="apps/<?php echo htmlspecialchars($app['app_filename']); ?>?repo_id=<?php echo $repo_id; ?>" class="app-link" target="_blank">
                🚀 View Live Application
            </a>
            
            <div class="secret-form">
                <h3>Found something interesting?</h3>
                <p>If you discovered a secret key in the application, enter it here:</p>
                <form method="POST">
                    <input type="text" name="secret_key" placeholder="Enter secret key">
                    <button type="submit">Verify</button>
                </form>
                <?php if ($secret_message): ?>
                    <div class="message <?php echo strpos($secret_message, 'Correct') !== false ? 'success' : 'error'; ?>">
                        <?php echo $secret_message; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <h2>Files</h2>
<div class="file-browser">
    <?php if (empty($files)): ?>
        <div class="file-item">No files in this repository.</div>
    <?php else: ?>
        <?php foreach ($files as $file): ?>
            <div class="file-item">
                <a href="file.php?id=<?php echo $file['id']; ?>">
                    📄 <?php echo htmlspecialchars($file['filepath'] ? $file['filepath'] . '/' : ''); ?><?php echo htmlspecialchars($file['filename']); ?>
                </a>
                <?php 
                // Ajouter un indicateur si le fichier contient un indice ARG
                $has_clue = strpos(strtolower($file['content']), 'secret') !== false || 
                           strpos(strtolower($file['content']), 'key') !== false ||
                           strpos(strtolower($file['content']), 'password') !== false;
                if ($has_clue && $repo['is_arg_character']): ?>
                    <span style="background: #fff8c5; color: #d4a72c; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 10px;">
                        🔍 Contains clues
                    </span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($app): ?>
    <!-- Application ARG avec formulaire pour entrer des clés -->
    <div style="background: linear-gradient(45deg, #6e40c9, #3d8bb1); color: white; padding: 20px; border-radius: 6px; margin: 20px 0;">
        <h3>🚀 Live Application: <?php echo htmlspecialchars($app['app_filename']); ?></h3>
        <p>This functional application may contain hidden clues. Interact with it to find secrets!</p>
        <a href="apps/<?php echo htmlspecialchars($app['app_filename']); ?>" class="app-link" target="_blank" 
           style="background: white; color: #6e40c9; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-block; margin-top: 10px;">
            Open Application
        </a>
    </div>
    
    <div class="secret-form">
        <h3>Found a secret key?</h3>
        <p>If you discovered a secret key in the application or code, enter it here:</p>
        <form method="POST">
            <input type="text" name="secret_key" placeholder="Enter secret key (e.g., alpha-123)">
            <button type="submit" style="background: #2ea44f; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer;">
                Verify Key
            </button>
        </form>
        <?php if ($secret_message): ?>
            <div style="margin-top: 10px; padding: 10px; background: <?php echo strpos($secret_message, 'Correct') !== false ? '#dafbe1' : '#ffebe9'; ?>; 
                 border: 1px solid <?php echo strpos($secret_message, 'Correct') !== false ? '#2ea44f' : '#cf222e'; ?>; border-radius: 6px;">
                <?php echo $secret_message; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
        
        <h2>Recent Commits</h2>
        <div class="commit-list">
            <?php if (empty($commits)): ?>
                <div class="commit-item">No commits yet.</div>
            <?php else: ?>
                <?php foreach ($commits as $commit): ?>
                    <div class="commit-item">
                        <strong><?php echo htmlspecialchars($commit['message']); ?></strong><br>
                        <small>
                            <?php echo htmlspecialchars($commit['author_name']); ?> committed 
                            <?php echo time_ago($commit['committed_at']); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <h2 id="issues">Issues</h2>
        <div class="issue-list">
            <?php if (empty($issues)): ?>
                <div class="issue-item">No issues.</div>
            <?php else: ?>
                <?php foreach ($issues as $issue): ?>
                    <div class="issue-item">
                        <a href="issue.php?id=<?php echo $issue['id']; ?>" style="color: #24292e; text-decoration: none;">
                            <strong><?php echo htmlspecialchars($issue['title']); ?></strong>
                            <span style="color: <?php echo $issue['status'] === 'open' ? '#1a7f37' : '#cf222e'; ?>">
                                [<?php echo strtoupper($issue['status']); ?>]
                            </span>
                        </a><br>
                        <small>
                            Opened by <?php echo htmlspecialchars($issue['creator_name']); ?> 
                            <?php echo time_ago($issue['created_at']); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>