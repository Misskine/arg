<?php
// new-repo.php (placeholder)
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Repository · GitHub</title>
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
            max-width: 600px;
            margin: 0 auto;
            padding: 48px 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input, textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            background: #2ea44f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:disabled {
            background: #94d3a2;
            cursor: not-allowed;
        }
        
        .note {
            color: #586069;
            font-size: 14px;
            margin-top: 8px;
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
            <div class="user-menu" style="display: inline-flex; align-items: center; gap: 8px;">
                <div style="width: 20px; height: 20px; border-radius: 50%; background: #ddd;"></div>
                <a href="profile.php" style="color: white; text-decoration: none;">
                    <?php echo htmlspecialchars($user['username']); ?>
                </a>
            </div>
        </nav>
    </header>
    
    <div class="container">
        <h1 style="margin-bottom: 32px;">Create a new repository</h1>
        
        <div style="background: #fff8c5; border: 1px solid #f0c420; border-radius: 6px; padding: 16px; margin-bottom: 24px;">
            <p style="margin: 0;">
                <strong>Note:</strong> Repository creation is currently limited to administrators for this demonstration.
            </p>
        </div>
        
        <form method="POST" action="#">
            <div class="form-group">
                <label for="repo-name">Repository name *</label>
                <input type="text" id="repo-name" name="repo_name" required disabled>
                <p class="note">Great repository names are short and memorable.</p>
            </div>
            
            <div class="form-group">
                <label for="repo-desc">Description (optional)</label>
                <textarea id="repo-desc" name="repo_desc" disabled></textarea>
                <p class="note">A short description of the repository.</p>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_private" disabled>
                    Make this repository private
                </label>
                <p class="note">Private repositories are only visible to you and people you share them with.</p>
            </div>
            
            <button type="submit" class="btn" disabled>Create repository</button>
            <a href="dashboard.php" style="margin-left: 12px; color: #0969da;">Cancel</a>
        </form>
    </div>
</body>
</html>