<?php
// unlock_repository.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$repo_id = $_GET['id'] ?? 0;

// Vérifier si le repository existe
$stmt = $pdo->prepare("SELECT id FROM repositories WHERE id = ?");
$stmt->execute([$repo_id]);
$repo = $stmt->fetch();

if (!$repo) {
    header('Location: dashboard.php');
    exit();
}

// Vérifier si l'utilisateur a déjà débloqué ce repository
$stmt = $pdo->prepare("SELECT id FROM user_progress WHERE user_id = ? AND repository_id = ?");
$stmt->execute([$user_id, $repo_id]);
$progress = $stmt->fetch();

if (!$progress) {
    // Vérifier si le repository précédent est complété
    $stmt = $pdo->prepare("
        SELECT id FROM repositories 
        WHERE id < ? 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$repo_id]);
    $previous_repo = $stmt->fetch();
    
    if ($previous_repo) {
        $stmt = $pdo->prepare("
            SELECT is_unlocked, completed_at 
            FROM user_progress 
            WHERE user_id = ? AND repository_id = ?
        ");
        $stmt->execute([$user_id, $previous_repo['id']]);
        $previous_progress = $stmt->fetch();
        
        if (!$previous_progress || !$previous_progress['completed_at']) {
            $_SESSION['error'] = "Vous devez d'abord compléter le repository précédent.";
            header('Location: investigation.php');
            exit();
        }
    }
    
    // Débloquer le repository
    $stmt = $pdo->prepare("
        INSERT INTO user_progress (user_id, repository_id, is_unlocked, created_at)
        VALUES (?, ?, TRUE, NOW())
    ");
    $stmt->execute([$user_id, $repo_id]);
    
    $_SESSION['success'] = "Repository débloqué avec succès!";
}

header('Location: repository.php?id=' . $repo_id);
exit();
?>