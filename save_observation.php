<?php
// save_observation.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die('Non autorisé');
}

$user_id = $_SESSION['user_id'];
$file_id = $_POST['file_id'] ?? 0;
$observation = $_POST['observation'] ?? '';

if (!$file_id || empty($observation)) {
    die('Données manquantes');
}

// Récupérer le repository_id du fichier
$stmt = $pdo->prepare("SELECT repository_id FROM repository_files WHERE id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if (!$file) {
    die('Fichier non trouvé');
}

// Sauvegarder dans les issues (observations)
$stmt = $pdo->prepare("
    INSERT INTO issues (repository_id, title, description, created_by, created_at)
    VALUES (?, 'Observation', ?, ?, NOW())
");
$stmt->execute([$file['repository_id'], $observation, $user_id]);

echo 'OK';
?>