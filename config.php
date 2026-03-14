<?php
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gitrepo');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}

// Fonction time_ago corrigée
function time_ago($datetime) {
    if (!$datetime) return 'Just now';
    
    $time = strtotime($datetime);
    if ($time === false) return 'Some time ago';
    
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . ' second' . ($diff !== 1 ? 's' : '') . ' ago';
    $diff = floor($diff / 60);
    if ($diff < 60) return $diff . ' minute' . ($diff !== 1 ? 's' : '') . ' ago';
    $diff = floor($diff / 60);
    if ($diff < 24) return $diff . ' hour' . ($diff !== 1 ? 's' : '') . ' ago';
    $diff = floor($diff / 24);
    if ($diff < 7) return $diff . ' day' . ($diff !== 1 ? 's' : '') . ' ago';
    if ($diff < 30) return floor($diff / 7) . ' week' . (floor($diff / 7) !== 1 ? 's' : '') . ' ago';
    if ($diff < 365) return floor($diff / 30) . ' month' . (floor($diff / 30) !== 1 ? 's' : '') . ' ago';
    return floor($diff / 365) . ' year' . (floor($diff / 365) !== 1 ? 's' : '') . ' ago';
}

// Fonction pour vérifier si un utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Fonction de redirection
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fonction pour obtenir l'utilisateur connecté (CHANGÉ LE NOM)
function get_current_user_data($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting user data: " . $e->getMessage());
        return null;
    }
}

// Fonction pour vérifier l'accès à un repository ARG
function has_access_to_repo($pdo, $user_id, $repo_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, u.is_arg_character 
            FROM repositories r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$repo_id]);
        $repo = $stmt->fetch();
        
        if (!$repo) return false;
        
        // Si ce n'est pas un personnage ARG, l'accès est autorisé
        if (!$repo['is_arg_character']) return true;
        
        // Si c'est un personnage ARG, vérifier s'il est débloqué
        $stmt = $pdo->prepare("
            SELECT id FROM player_progress 
            WHERE player_id = ? AND unlocked_user_id = ?
        ");
        $stmt->execute([$user_id, $repo['user_id']]);
        
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        error_log("Error checking repo access: " . $e->getMessage());
        return false;
    }
}

// Vérification de session
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    session_unset();
    session_destroy();
    redirect('login.php');
}
$_SESSION['last_activity'] = time();
?>