<?php
// init.php - Initialisation du jeu ARG
include 'config.php';

// Créer un compte admin pour tester si la base est vide
function initialize_database($pdo) {
    try {
        // Vérifier si des utilisateurs existent
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            // Créer un utilisateur test
            $password_hash = password_hash('test123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute(['admin', 'admin@arg.test', $password_hash]);
            
            echo "<p>✅ Base de données initialisée avec un utilisateur test</p>";
            echo "<p>Username: <strong>admin</strong></p>";
            echo "<p>Password: <strong>test123</strong></p>";
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Fonction pour réinitialiser la progression d'un joueur (pour les tests)
function reset_player_progress($pdo, $player_id) {
    try {
        $pdo->beginTransaction();
        
        // Supprimer la progression
        $stmt = $pdo->prepare("DELETE FROM player_progress WHERE player_id = ?");
        $stmt->execute([$player_id]);
        
        // Supprimer les secrets trouvés
        $stmt = $pdo->prepare("DELETE FROM found_secrets WHERE player_id = ?");
        $stmt->execute([$player_id]);
        
        // Débloquer seulement le premier personnage
        $stmt = $pdo->prepare("SELECT id FROM users WHERE is_arg_character = TRUE AND unlock_order = 1");
        $stmt->execute();
        $first_char = $stmt->fetch();
        
        if ($first_char) {
            $stmt = $pdo->prepare("INSERT INTO player_progress (player_id, unlocked_user_id) VALUES (?, ?)");
            $stmt->execute([$player_id, $first_char['id']]);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}
?>