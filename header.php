<?php
// dashboard.php - Version complète avec header
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer l'utilisateur actuel
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_result = $stmt->fetch();
$user = $user_result ? $user_result : ['username' => 'User'];

// Récupérer SEULEMENT LES REPOSITORIES DÉBLOQUÉS
$stmt = $pdo->prepare("
    SELECT DISTINCT r.*, u.username as owner_name, u.is_arg_character,
           (SELECT COUNT(*) FROM commits c WHERE c.repository_id = r.id) as commit_count
    FROM repositories r
    JOIN users u ON r.user_id = u.id
    WHERE (
        -- Soit c'est l'utilisateur connecté
        u.id = ?
        -- Soit c'est un utilisateur non-ARG (toujours visible)
        OR u.is_arg_character = FALSE
        -- Soit c'est un personnage ARG débloqué par l'utilisateur
        OR EXISTS (
            SELECT 1 FROM player_progress pp 
            WHERE pp.player_id = ? 
            AND pp.unlocked_user_id = u.id
            AND u.is_arg_character = TRUE
        )
    )
    ORDER BY r.updated_at DESC
");
$stmt->execute([$user_id, $user_id]);
$repositories = $stmt->fetchAll();

// Récupérer les personnages ARG débloqués
$stmt = $pdo->prepare("
    SELECT u.* 
    FROM users u
    INNER JOIN player_progress pp ON u.id = pp.unlocked_user_id
    WHERE pp.player_id = ? AND u.is_arg_character = TRUE
    ORDER BY u.unlock_order
");
$stmt->execute([$user_id]);
$unlocked_characters = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · GitHub</title>
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
            position: relative;
        }
        
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #1f6feb;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .search-input {
            padding: 6px 12px;
            border: 1px solid #444;
            border-radius: 6px;
            background-color: #1c1f23;
            color: white;
            width: 300px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
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
            margin-bottom: 40px;
        }
        
        .repository {
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 16px;
            background: white;
        }
        
        .repository h3 {
            margin: 0 0 8px 0;
        }
        
        .repository h3 a {
            color: #0969da;
            text-decoration: none;
        }
        
        .repository h3 a:hover {
            text-decoration: underline;
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
        
        .repo-meta a {
            color: #586069;
            text-decoration: none;
        }
        
        .repo-meta a:hover {
            color: #0969da;
        }
        
        .language {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #f1e05a;
            margin-right: 4px;
        }
        
        /* Style pour la progression ARG */
        .progress-tracker {
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 20px;
            margin: 32px 0;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        
        .progress-step {
            text-align: center;
            position: relative;
            flex: 1;
        }
        
        .progress-step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #e1e4e8;
        }
        
        .progress-step.completed:after {
            background: #2ea44f;
        }
        
        .step-indicator {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e1e4e8;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .step-indicator.completed {
            background: #2ea44f;
            color: white;
        }
        
        .step-indicator.current {
            background: #0969da;
            color: white;
        }
        
        .locked-repo {
            opacity: 0.5;
            filter: blur(3px);
            pointer-events: none;
            position: relative;
        }
        
        .locked-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            z-index: 10;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <nav>
            <input type="text" class="search-input" placeholder="Search or jump to..." onclick="window.location.href='search.php'">
            <a href="dashboard.php">Dashboard</a>
            <a href="messages.php">
                Messages
                <?php 
                // Compter les messages non lus
                $stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE recipient_id = ? AND is_read = FALSE");
                $stmt->execute([$user_id]);
                $unread_result = $stmt->fetch();
                $unread_messages = $unread_result ? $unread_result['unread_count'] : 0;
                if ($unread_messages > 0): ?>
                    <span class="notification-badge"><?php echo $unread_messages; ?></span>
                <?php endif; ?>
            </a>
            <div class="user-menu">
                <div class="avatar">
                    <?php echo isset($user['username']) ? strtoupper(substr($user['username'], 0, 1)) : 'U'; ?>
                </div>
                <a href="profile.php?user=<?php echo urlencode($user['username'] ?? ''); ?>">
                    <?php echo htmlspecialchars($user['username'] ?? 'User'); ?>
                </a>
                <a href="logout.php" style="margin-left: 10px;">Logout</a>
            </div>
        </nav>
    </header>
    
    <div class="container">
        <!-- Traqueur de progression ARG -->
        <div class="progress-tracker">
            <h3 style="margin: 0 0 20px 0; width: 100%; text-align: center;">ARG Progress</h3>
            <?php
            // Récupérer tous les personnages ARG dans l'ordre
            $stmt = $pdo->prepare("
                SELECT u.*, 
                       EXISTS (
                           SELECT 1 FROM player_progress pp 
                           WHERE pp.player_id = ? 
                           AND pp.unlocked_user_id = u.id
                       ) as unlocked
                FROM users u 
                WHERE u.is_arg_character = TRUE 
                ORDER BY u.unlock_order
            ");
            $stmt->execute([$user_id]);
            $arg_characters = $stmt->fetchAll();
            
            foreach ($arg_characters as $char): ?>
            <div class="progress-step">
                <div class="step-indicator <?php 
                    echo $char['unlocked'] ? 'completed' : 
                    ($char['unlock_order'] == 1 ? 'current' : ''); 
                ?>">
                    <?php echo $char['unlocked'] ? '✓' : $char['unlock_order']; ?>
                </div>
                <div style="font-weight: 600;"><?php echo htmlspecialchars($char['username']); ?></div>
                <small style="color: #586069; font-size: 12px;">
                    <?php echo $char['unlocked'] ? '✓ Unlocked' : 'Locked'; ?>
                </small>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Message d'indice -->
        <div style="background: #fff8c5; border: 1px solid #d4a72c; border-radius: 6px; padding: 16px; margin: 20px 0;">
            <strong>💡 Hint:</strong> Explore repositories to find secret keys. Enter them in the search bar to unlock new profiles!
            <?php if (count($unlocked_characters) == 0): ?>
                <br><small>Start by exploring <strong>dev_alpha</strong>'s repositories. Check the code for clues!</small>
            <?php endif; ?>
        </div>
        
        <!-- Repositories (seulement débloqués) -->
        <div class="dashboard-header">
            <h1>Available Repositories</h1>
        </div>
        
        <?php if (empty($repositories)): ?>
            <p>No repositories available. Start by exploring dev_alpha's repositories!</p>
        <?php else: ?>
            <div class="repositories">
                <?php foreach ($repositories as $repo): ?>
                    <div class="repository">
                        <h3>
                            <a href="repo.php?id=<?php echo $repo['id']; ?>">
                                <?php echo htmlspecialchars($repo['owner_name'] . '/' . $repo['name']); ?>
                            </a>
                        </h3>
                        <p><?php echo htmlspecialchars($repo['description'] ?: 'No description'); ?></p>
                        <div class="repo-meta">
                            <span>
                                <span class="language"></span>
                                <?php echo $repo['language'] ?: 'PHP'; ?>
                            </span>
                            <span>
                                <?php if ($repo['is_arg_character']): ?>
                                    <span style="color: #8250df;">🔒 ARG Character</span>
                                <?php else: ?>
                                    By <a href="profile.php?user=<?php echo urlencode($repo['owner_name']); ?>">
                                        <?php echo htmlspecialchars($repo['owner_name']); ?>
                                    </a>
                                <?php endif; ?>
                            </span>
                            <span>
                                <svg height="16" viewBox="0 0 16 16" width="16" fill="currentColor" style="vertical-align: text-bottom;">
                                    <path fill-rule="evenodd" d="M1.643 3.143L.427 1.927A.25.25 0 000 2.104V5.75c0 .138.112.25.25.25h3.646a.25.25 0 00.177-.427L2.715 4.215a6.5 6.5 0 11-1.18 4.458.75.75 0 10-1.493.154 8.001 8.001 0 101.6-5.684zM7.75 4a.75.75 0 01.75.75v2.992l2.028.812a.75.75 0 01-.557 1.392l-2.5-1A.75.75 0 017 8.25v-3.5A.75.75 0 017.75 4z"></path>
                                </svg>
                                Updated <?php echo time_ago($repo['updated_at']); ?>
                            </span>
                            <?php if ($repo['commit_count'] > 0): ?>
                            <span>
                                <svg height="16" viewBox="0 0 16 16" width="16" fill="currentColor" style="vertical-align: text-bottom;">
                                    <path fill-rule="evenodd" d="M1.643 3.143L.427 1.927A.25.25 0 000 2.104V5.75c0 .138.112.25.25.25h3.646a.25.25 0 00.177-.427L2.715 4.215a6.5 6.5 0 11-1.18 4.458.75.75 0 10-1.493.154 8.001 8.001 0 101.6-5.684zM7.75 4a.75.75 0 01.75.75v2.992l2.028.812a.75.75 0 01-.557 1.392l-2.5-1A.75.75 0 017 8.25v-3.5A.75.75 0 017.75 4z"></path>
                                </svg>
                                <?php echo $repo['commit_count']; ?> commits
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats de progression -->
        <div style="display: flex; gap: 40px; margin-top: 30px; padding: 20px; border: 1px solid #e1e4e8; border-radius: 6px; background: white;">
            <div style="text-align: center;">
                <span style="display: block; font-size: 32px; font-weight: bold; color: #24292e;">
                    <?php echo count($repositories); ?>
                </span>
                <span style="color: #586069; font-size: 14px;">Repositories</span>
            </div>
            <div style="text-align: center;">
                <span style="display: block; font-size: 32px; font-weight: bold; color: #24292e;">
                    <?php echo count($unlocked_characters); ?>
                </span>
                <span style="color: #586069; font-size: 14px;">ARG Characters Unlocked</span>
            </div>
            <div style="text-align: center;">
                <span style="display: block; font-size: 32px; font-weight: bold; color: #24292e;">
                    <?php 
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM found_secrets WHERE player_id = ?");
                    $stmt->execute([$user_id]);
                    $secrets_result = $stmt->fetch();
                    echo $secrets_result ? $secrets_result['count'] : 0;
                    ?>
                </span>
                <span style="color: #586069; font-size: 14px;">Secrets Found</span>
            </div>
        </div>
    </div>
</body>
</html>