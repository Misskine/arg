<?php
// mark_as_clue.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die('Non autorisé');
}

$user_id = $_SESSION['user_id'];
$file_id = $_POST['file_id'] ?? 0;
$clue_type = $_POST['clue_type'] ?? '';
$description = $_POST['description'] ?? '';

if (!$file_id || empty($clue_type) || empty($description)) {
    die('Données manquantes');
}

// Récupérer le repository_id du fichier
$stmt = $pdo->prepare("SELECT repository_id FROM repository_files WHERE id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if (!$file) {
    die('Fichier non trouvé');
}

// Sauvegarder dans clues
$stmt = $pdo->prepare("
    INSERT INTO clues (repository_id, clue_type, title, content, found_by, found_at)
    VALUES (?, ?, 'Indice dans le code', ?, ?, NOW())
");
$stmt->execute([$file['repository_id'], $clue_type, $description, $user_id]);

echo 'OK';
?>