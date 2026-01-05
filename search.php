<?php
// search.php - Recherche améliorée avec déverrouillage ARG
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$search_query = trim($_GET['q'] ?? '');
$results = [];
$unlock_message = '';

if ($search_query) {
    // 1. Vérifier si c'est un code de déverrouillage ARG
    $stmt = $pdo->prepare("
        SELECT fa.*, r.id as repo_id, r.name as repo_name, r.user_id, u.username 
        FROM arg_apps fa
        JOIN repositories r ON fa.repository_id = r.id
        JOIN users u ON r.user_id = u.id
        WHERE fa.unlock_code = ? OR fa.secret_key = ?
    ");
    $stmt->execute([$search_query, $search_query]);
    $arg_item = $stmt->fetch();
    
    if ($arg_item) {
        // Si c'est un code de déverrouillage
        if ($search_query === $arg_item['unlock_code']) {
            // Trouver l'utilisateur ARG lié à ce repository
            $stmt = $pdo->prepare("
                SELECT u.* 
                FROM users u 
                JOIN repositories r ON u.id = r.user_id 
                WHERE r.id = ?
            ");
            $stmt->execute([$arg_item['repo_id']]);
            $target_user = $stmt->fetch();
            
            if ($target_user && $target_user['is_arg_character']) {
                // Vérifier si déjà débloqué
                $stmt = $pdo->prepare("
                    SELECT id FROM player_progress 
                    WHERE player_id = ? AND unlocked_user_id = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $target_user['id']]);
                
                if (!$stmt->fetch()) {
                    // Débloquer ce personnage
                    $stmt = $pdo->prepare("
                        INSERT INTO player_progress (player_id, unlocked_user_id) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$_SESSION['user_id'], $target_user['id']]);
                    
                    // Envoyer un message du personnage débloqué
                    $welcome_messages = [
                        2 => "You found me. I'm dev_beta. Alpha was right to be suspicious. Check my encryption-tools repository. The key is in the XOR function.",
                        3 => "dev_gamma here. I've been analyzing the data. Something's happening at midnight UTC. Look at my data-analysis repo.",
                        4 => "This is dev_delta. The system logs show unauthorized access patterns. Start with my system-monitor repo.",
                        5 => "Epsilon here. I know what happened now. But I can't say it here. Check my private-notes repository. The final key is 'epsilon-final'."
                    ];
                    
                    if (isset($welcome_messages[$target_user['unlock_order']])) {
                        $stmt = $pdo->prepare("
                            INSERT INTO messages (sender_id, recipient_id, subject, body) 
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $target_user['id'],
                            $_SESSION['user_id'],
                            "You found me",
                            $welcome_messages[$target_user['unlock_order']]
                        ]);
                    }
                    
                    $unlock_message = "🔓 Profile unlocked: " . htmlspecialchars($target_user['username']) . "!";
                } else {
                    $unlock_message = "✅ You already unlocked this profile.";
                }
            }
        } 
        // Si c'est une clé secrète
        elseif ($search_query === $arg_item['secret_key']) {
            // Enregistrer la clé trouvée
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO found_secrets (player_id, repository_id, secret_key) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $arg_item['repo_id'], $search_query]);
            
            $unlock_message = "🔑 Secret key found! Hint: Try entering this in search: " . 
                             htmlspecialchars($arg_item['unlock_code']);
        }
    }
    
    // 2. Recherche normale (seulement les repositories débloqués)
    $stmt = $pdo->prepare("
        SELECT r.*, u.username as owner_name,
               (SELECT COUNT(*) FROM commits c WHERE c.repository_id = r.id) as commit_count
        FROM repositories r
        JOIN users u ON r.user_id = u.id
        WHERE (
            -- L'utilisateur est le propriétaire
            r.user_id = ?
            -- Ou c'est un utilisateur normal
            OR u.is_arg_character = FALSE
            -- Ou c'est un personnage ARG débloqué
            OR EXISTS (
                SELECT 1 FROM player_progress pp 
                WHERE pp.player_id = ? 
                AND pp.unlocked_user_id = u.id
            )
        )
        AND (r.name LIKE ? OR r.description LIKE ? OR u.username LIKE ?)
        ORDER BY r.updated_at DESC
    ");
    $search_term = '%' . $search_query . '%';
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $search_term, $search_term, $search_term]);
    $results = $stmt->fetchAll();
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
    <title>Search · GitHub</title>
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
        .search-form {
            flex: 1;
            max-width: 600px;
        }
        .search-form input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #444;
            border-radius: 6px;
            background-color: #1c1f23;
            color: white;
            font-size: 16px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        .arg-result {
            background: linear-gradient(45deg, #6e40c9, #3d8bb1);
            color: white;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .result-item {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .result-item h3 {
            margin: 0 0 8px 0;
        }
        .result-item h3 a {
            color: #0969da;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <form class="search-form" method="GET" action="search.php">
            <input type="text" name="q" placeholder="Search repositories, users, or enter ARG codes..." 
                   value="<?php echo htmlspecialchars($search_query); ?>" autofocus>
        </form>
        <nav>
            <a href="dashboard.php" style="color: white; text-decoration: none;">Dashboard</a>
        </nav>
    </header>
    
    <div class="container">
        <?php if ($unlock_message): ?>
            <div class="arg-result">
                <h3>🎉 ARG Progress!</h3>
                <p><?php echo $unlock_message; ?></p>
                <p><small>Check your dashboard to see the new content!</small></p>
            </div>
        <?php endif; ?>
        
        <?php if ($search_query): ?>
            <h2>Search results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
            
            <?php if (empty($results)): ?>
                <p>No repositories found.</p>
                <?php if (strlen($search_query) >= 6 && !$arg_item): ?>
                    <div style="background: #fff8c5; padding: 16px; border-radius: 6px; margin-top: 20px;">
                        <p>🔍 This looks like it could be an ARG code. Try exploring repositories to find where it belongs!</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php foreach ($results as $repo): ?>
                    <div class="result-item">
                        <h3>
                            <a href="repo.php?id=<?php echo $repo['id']; ?>">
                                <?php echo htmlspecialchars($repo['owner_name']); ?>/<?php echo htmlspecialchars($repo['name']); ?>
                            </a>
                        </h3>
                        <p><?php echo htmlspecialchars($repo['description']); ?></p>
                        <div style="color: #586069; font-size: 14px;">
                            Updated <?php echo time_ago($repo['updated_at']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <h2>Search GitHub</h2>
                <p>Find repositories, users, or enter ARG codes to unlock new content.</p>
                <div style="background: #f6f8fa; padding: 20px; border-radius: 6px; margin-top: 30px; max-width: 600px; margin: 30px auto;">
                    <h4>💡 How the ARG works:</h4>
                    <ol style="text-align: left; margin-left: 20px;">
                        <li>Explore repositories of unlocked characters</li>
                        <li>Look for secret keys in code comments or applications</li>
                        <li>Enter found keys in this search bar</li>
                        <li>Unlock new characters and their repositories</li>
                    </ol>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>