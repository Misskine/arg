<?php
// repositories.php
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// À VOUS : Écrire la requête SQL qui récupère tous les repositories
// avec l'information de déblocage pour cet utilisateur
// Utilisez une jointure LEFT JOIN avec user_progress

$sql = "SELECT r.*, up.is_unlocked 
        FROM repositories r 
        LEFT JOIN user_progress up ON r.id = up.repository_id AND up.user_id = ?
        ORDER BY r.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$repos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Repositories</h1>

<?php if (empty($repos)): ?>
    <p>Aucun repository disponible pour le moment.</p>
<?php else: ?>
    <div class="repositories-grid">
        <?php foreach ($repos as $repo): ?>
            <?php 
            // Le premier repository est toujours accessible
            $is_unlocked = ($repo['id'] == 1) ? true : ($repo['is_unlocked'] ?? false);
            ?>
            
            <div class="repository-card <?php echo $is_unlocked ? 'unlocked' : 'locked'; ?>">
                <h3><?php echo htmlspecialchars($repo['name']); ?></h3>
                <p><?php echo htmlspecialchars($repo['description'] ?? 'Pas de description'); ?></p>
                
                <div class="repo-status">
                    <?php if ($is_unlocked): ?>
                        <a href="repository.php?id=<?php echo $repo['id']; ?>" class="btn btn-primary">Accéder</a>
                        
                        <?php if ($repo['is_unlocked'] && $repo['completed_at']): ?>
                            <span class="completed">✓ Complété</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="locked">🔒 Verrouillé</span>
                        <button class="btn btn-disabled" disabled>Débloquez le précédent</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.repositories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.repository-card {
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.repository-card.unlocked {
    border-color: #238636;
    background-color: #f0fff4;
}

.repository-card.unlocked:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(35, 134, 54, 0.2);
}

.repository-card.locked {
    border-color: #ddd;
    background-color: #f6f8fa;
    opacity: 0.7;
}

.repo-status {
    margin-top: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn {
    padding: 8px 16px;
    background-color: #238636;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #2ea043;
}

.btn-disabled {
    background-color: #ddd;
    color: #666;
    cursor: not-allowed;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
}

.locked {
    color: #6e7781;
    font-weight: bold;
}

.completed {
    color: #238636;
    font-weight: bold;
}
</style>

<?php include 'footer.php'; ?>