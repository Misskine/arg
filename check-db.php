<?php
// check-db.php - Vérifier l'état de la base de données
include 'config.php';

echo "<h2>Vérification de la base de données</h2>";

// Vérifier les tables
$tables = ['users', 'repositories', 'arg_apps', 'repository_files', 'player_progress'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch();
        echo "<p>$table: " . $result['count'] . " entrées</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>$table: ERREUR - " . $e->getMessage() . "</p>";
    }
}

// Vérifier les contraintes de clés étrangères
echo "<h3>Vérification des relations:</h3>";

// Vérifier que chaque arg_apps a un repository valide
$stmt = $pdo->query("
    SELECT COUNT(*) as invalid_count 
    FROM arg_apps a 
    LEFT JOIN repositories r ON a.repository_id = r.id 
    WHERE r.id IS NULL
");
$result = $stmt->fetch();
echo "<p>Applications ARG sans repository: " . $result['invalid_count'] . "</p>";

// Vérifier que chaque repository a un utilisateur valide
$stmt = $pdo->query("
    SELECT COUNT(*) as invalid_count 
    FROM repositories r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE u.id IS NULL
");
$result = $stmt->fetch();
echo "<p>Repositories sans utilisateur: " . $result['invalid_count'] . "</p>";

// Afficher les applications ARG
echo "<h3>Applications ARG configurées:</h3>";
$stmt = $pdo->query("
    SELECT a.*, r.name as repo_name, u.username 
    FROM arg_apps a
    JOIN repositories r ON a.repository_id = r.id
    JOIN users u ON r.user_id = u.id
    ORDER BY u.unlock_order
");
$apps = $stmt->fetchAll();

foreach ($apps as $app) {
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<strong>" . htmlspecialchars($app['username']) . "/" . htmlspecialchars($app['repo_name']) . "</strong><br>";
    echo "App: " . htmlspecialchars($app['app_filename']) . "<br>";
    echo "Clé secrète: " . htmlspecialchars($app['secret_key']) . "<br>";
    echo "Code de déverrouillage: " . htmlspecialchars($app['unlock_code']) . "<br>";
    echo "</div>";
}
?>