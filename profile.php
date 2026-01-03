<?php
// profile.php - Version améliorée
include 'config.php';

// Si non connecté et pas de paramètre user, rediriger vers login
if (!isset($_SESSION['user_id']) && empty($_GET['user'])) {
    header('Location: login.php');
    exit();
}

$username = $_GET['user'] ?? '';

if (empty($username) && isset($_SESSION['user_id'])) {
    // Voir son propre profil
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $username = $user['username'];
} elseif (empty($username)) {
    header('Location: login.php');
    exit();
}

// Récupérer les infos de l'utilisateur
$stmt = $pdo->prepare("
    SELECT u.*, 
           v.name as full_name, v.role, v.disappearance_date, v.bio,
           v.last_seen_location, v.profile_image
    FROM users u
    LEFT JOIN victims v ON v.repository_id IN (
        SELECT id FROM repositories WHERE user_id = u.id
    )
    WHERE u.username = ?
    LIMIT 1
");
$stmt->execute([$username]);
$profile = $stmt->fetch();

if (!$profile) {
    $_SESSION['error'] = "Utilisateur non trouvé";
    header('Location: dashboard.php');
    exit();
}

// Récupérer les repositories de l'utilisateur
$stmt = $pdo->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM commits c WHERE c.repository_id = r.id) as commit_count,
           (SELECT COUNT(*) FROM repository_files rf WHERE rf.repository_id = r.id) as file_count
    FROM repositories r 
    WHERE r.user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$profile['id']]);
$repositories = $stmt->fetchAll();

// Récupérer les statistiques
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT r.id) as total_repos,
        COUNT(DISTINCT c.id) as total_commits,
        COUNT(DISTINCT i.id) as total_issues
    FROM users u
    LEFT JOIN repositories r ON r.user_id = u.id
    LEFT JOIN commits c ON c.repository_id = r.id
    LEFT JOIN issues i ON i.repository_id = r.id
    WHERE u.username = ?
");
$stmt->execute([$username]);
$stats = $stmt->fetch();

// Récupérer les commits récents
$stmt = $pdo->prepare("
    SELECT c.*, r.name as repo_name
    FROM commits c
    JOIN repositories r ON c.repository_id = r.id
    WHERE r.user_id = ?
    ORDER BY c.committed_at DESC
    LIMIT 10
");
$stmt->execute([$profile['id']]);
$recent_commits = $stmt->fetchAll();

// Récupérer l'année de création du compte
$join_year = date('Y', strtotime($profile['created_at']));
$current_year = date('Y');
$years_on_github = $current_year - $join_year;

// Déterminer si c'est un compte "disparu"
$is_missing = !empty($profile['disappearance_date']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['full_name'] ?? $profile['username']); ?> · GitHub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f6f8fa;
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
        
        .profile-header {
            display: flex;
            gap: 32px;
            margin-bottom: 32px;
        }
        
        .avatar-section {
            flex: 0 0 300px;
        }
        
        .avatar {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: white;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 100px;
            color: white;
            font-weight: bold;
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-name {
            font-size: 32px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }
        
        .profile-username {
            font-size: 24px;
            font-weight: 300;
            color: #586069;
            margin: 0 0 16px 0;
        }
        
        .profile-bio {
            font-size: 16px;
            line-height: 1.5;
            margin: 16px 0;
            color: #24292e;
        }
        
        .profile-details {
            margin: 24px 0;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 8px 0;
            color: #586069;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin: 24px 0;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 28px;
            font-weight: 600;
            color: #24292e;
        }
        
        .stat-label {
            color: #586069;
            font-size: 14px;
        }
        
        .missing-warning {
            background: #fff8c5;
            border: 1px solid #f0c420;
            border-radius: 6px;
            padding: 16px;
            margin: 24px 0;
        }
        
        .repositories-section {
            margin-top: 48px;
        }
        
        .repo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }
        
        .repo-card {
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 16px;
            transition: transform 0.2s;
        }
        
        .repo-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        
        .repo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .repo-name {
            color: #0969da;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
        }
        
        .repo-description {
            color: #586069;
            margin: 8px 0;
            line-height: 1.5;
        }
        
        .repo-meta {
            display: flex;
            gap: 16px;
            font-size: 12px;
            color: #586069;
            margin-top: 12px;
        }
        
        .language {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #f1e05a;
            margin-right: 4px;
        }
        
        .commits-section {
            margin-top: 48px;
        }
        
        .commit-list {
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 16px;
        }
        
        .commit-item {
            padding: 12px 16px;
            border-bottom: 1px solid #e1e4e8;
        }
        
        .commit-item:last-child {
            border-bottom: none;
        }
        
        .commit-message {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .commit-meta {
            font-size: 12px;
            color: #586069;
            display: flex;
            gap: 16px;
        }
        
        .search-users {
            margin-bottom: 24px;
            padding: 16px;
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
        }
        
        .search-form {
            display: flex;
            gap: 8px;
        }
        
        .search-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .search-btn {
            background: #2ea44f;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="users.php">Users</a>
                <a href="profile.php">My Profile</a>
                <a href="logout.php">Sign out</a>
            <?php else: ?>
                <a href="login.php">Sign in</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <div class="container">
        <!-- Barre de recherche d'utilisateurs -->
        <div class="search-users">
            <form action="users.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Rechercher des utilisateurs..." class="search-input" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="search-btn">Rechercher</button>
            </form>
        </div>
        
        <div class="profile-header">
            <div class="avatar-section">
                <div class="avatar">
                    <?php echo strtoupper(substr($profile['username'], 0, 1)); ?>
                </div>
                <div class="detail-item">
                    <strong>Membre depuis</strong> <?php echo date('F Y', strtotime($profile['created_at'])); ?>
                </div>
                <div class="detail-item">
                    <strong>Sur GitHub depuis</strong> <?php echo $years_on_github; ?> an<?php echo $years_on_github > 1 ? 's' : ''; ?>
                </div>
            </div>
            
            <div class="profile-info">
                <h1 class="profile-name"><?php echo htmlspecialchars($profile['full_name'] ?? $profile['username']); ?></h1>
                <h2 class="profile-username">@<?php echo htmlspecialchars($profile['username']); ?></h2>
                
                <?php if (!empty($profile['bio'])): ?>
                    <div class="profile-bio"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($profile['role'])): ?>
                    <div class="profile-details">
                        <div class="detail-item">
                            <strong>🏢 Rôle:</strong> <?php echo htmlspecialchars($profile['role']); ?>
                        </div>
                        <?php if (!empty($profile['last_seen_location'])): ?>
                            <div class="detail-item">
                                <strong>📍 Dernière vue à:</strong> <?php echo htmlspecialchars($profile['last_seen_location']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($is_missing): ?>
                    <div class="missing-warning">
                        <strong>⚠️ Compte inactif</strong><br>
                        Dernière contribution le <?php echo date('j F Y', strtotime($profile['disappearance_date'])); ?>.
                        <?php if (!empty($profile['last_seen_location'])): ?>
                            <br>Dernière localisation connue: <?php echo htmlspecialchars($profile['last_seen_location']); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $stats['total_repos'] ?? 0; ?></span>
                        <span class="stat-label">Repositories</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $stats['total_commits'] ?? 0; ?></span>
                        <span class="stat-label">Commits</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $stats['total_issues'] ?? 0; ?></span>
                        <span class="stat-label">Issues</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="repositories-section">
            <h2>Repositories</h2>
            <?php if (empty($repositories)): ?>
                <p>Aucun repository public.</p>
            <?php else: ?>
                <div class="repo-grid">
                    <?php foreach ($repositories as $repo): ?>
                        <div class="repo-card">
                            <div class="repo-header">
                                <a href="repo.php?id=<?php echo $repo['id']; ?>" class="repo-name">
                                    <?php echo htmlspecialchars($repo['name']); ?>
                                </a>
                                <span style="font-size: 12px; color: #586069;">
                                    <?php echo date('M j', strtotime($repo['created_at'])); ?>
                                </span>
                            </div>
                            <p class="repo-description">
                                <?php echo htmlspecialchars($repo['description'] ?? 'Pas de description'); ?>
                            </p>
                            <div class="repo-meta">
                                <span>
                                    <span class="language"></span>
                                    PHP
                                </span>
                                <span>📁 <?php echo $repo['file_count']; ?> fichiers</span>
                                <span>🔀 <?php echo $repo['commit_count']; ?> commits</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($recent_commits)): ?>
            <div class="commits-section">
                <h2>Commits récents</h2>
                <div class="commit-list">
                    <?php foreach ($recent_commits as $commit): ?>
                        <div class="commit-item">
                            <div class="commit-message">
                                <?php echo htmlspecialchars($commit['message']); ?>
                            </div>
                            <div class="commit-meta">
                                <span>
                                    dans <a href="repo.php?id=<?php echo $commit['repository_id']; ?>" style="color: #0969da;">
                                        <?php echo htmlspecialchars($commit['repo_name']); ?>
                                    </a>
                                </span>
                                <span>
                                    <?php echo date('j M Y, H:i', strtotime($commit['committed_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>