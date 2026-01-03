<?php
// repository.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$repo_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Vérifier l'accès au repository
$stmt = $pdo->prepare("
    SELECT r.*, u.username as owner_name,
           v.name as victim_name, v.role as victim_role, v.disappearance_date,
           up.is_unlocked, up.completed_at
    FROM repositories r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN victims v ON r.victim_id = v.id
    LEFT JOIN user_progress up ON r.id = up.repository_id AND up.user_id = ?
    WHERE r.id = ?
");
$stmt->execute([$user_id, $repo_id]);
$repo = $stmt->fetch();

if (!$repo) {
    header('Location: dashboard.php');
    exit();
}

// Vérifier si le repository est débloqué (le premier l'est toujours)
$is_unlocked = ($repo['id'] == 1) ? true : ($repo['is_unlocked'] ?? false);

if (!$is_unlocked) {
    header('Location: unlock_repository.php?id=' . $repo_id);
    exit();
}

// Récupérer les fichiers du repository
$stmt = $pdo->prepare("
    SELECT * FROM repository_files 
    WHERE repository_id = ? 
    ORDER BY filename
");
$stmt->execute([$repo_id]);
$files = $stmt->fetchAll();

// Récupérer les exercices
$stmt = $pdo->prepare("
    SELECT * FROM exercises 
    WHERE repository_id = ? 
    ORDER BY difficulty, id
");
$stmt->execute([$repo_id]);
$exercises = $stmt->fetchAll();

// Récupérer les branches
$stmt = $pdo->prepare("
    SELECT b.*, u.username as creator_name
    FROM branches b
    LEFT JOIN users u ON b.created_by = u.id
    WHERE b.repository_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$repo_id]);
$branches = $stmt->fetchAll();

// Récupérer les issues (indices)
$stmt = $pdo->prepare("
    SELECT i.*, u.username as creator_name
    FROM issues i
    LEFT JOIN users u ON i.created_by = u.id
    WHERE i.repository_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$repo_id]);
$issues = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($repo['name']); ?> - Investigation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f6f8fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .repo-header {
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .repo-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .repo-tabs {
            display: flex;
            border-bottom: 1px solid #e1e4e8;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        
        .tab.active {
            border-bottom-color: #f9826c;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 20px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .file-list, .exercise-list, .branch-list, .issue-list {
            display: grid;
            gap: 10px;
        }
        
        .file-item, .exercise-item, .branch-item, .issue-item {
            padding: 15px;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            background: white;
        }
        
        .victim-info {
            background: #fff8c5;
            border: 1px solid #f0c420;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #2ea44f;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .progress-bar {
            height: 10px;
            background: #e1e4e8;
            border-radius: 5px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: #2ea44f;
            border-radius: 5px;
        }
        .code-display {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace;
    font-size: 14px;
    line-height: 1.5;
    tab-size: 4;
    white-space: pre-wrap;
    }

    /* Surlignage syntaxique basique */
    .keyword { color: #d73a49; }
    .string { color: #032f62; }
    .comment { color: #6a737d; }
    .number { color: #005cc5; }
    .function { color: #6f42c1; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="repo-header">
            <div class="repo-title">
                <h1><?php echo htmlspecialchars($repo['name']); ?></h1>
                <?php if ($repo['completed_at']): ?>
                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                        ✓ Complété
                    </span>
                <?php endif; ?>
            </div>
            <p><?php echo htmlspecialchars($repo['description']); ?></p>
            
            <?php if ($repo['victim_name']): ?>
                <div class="victim-info">
                    <h3>Victime: <?php echo htmlspecialchars($repo['victim_name']); ?></h3>
                    <p><strong>Rôle:</strong> <?php echo htmlspecialchars($repo['victim_role']); ?></p>
                    <p><strong>Disparu le:</strong> <?php echo date('d/m/Y', strtotime($repo['disappearance_date'])); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php 
                    $total_items = count($files) + count($exercises);
                    $completed_items = 0; // À calculer avec les exercices complétés
                    echo ($total_items > 0) ? ($completed_items / $total_items * 100) : 0;
                ?>%"></div>
            </div>
            <p>Progression: <?php echo $completed_items; ?>/<?php echo $total_items; ?></p>
        </div>
        
        <div class="repo-tabs">
            <div class="tab active" onclick="switchTab('files')">📁 Fichiers</div>
            <div class="tab" onclick="switchTab('exercises')">💻 Exercices</div>
            <div class="tab" onclick="switchTab('branches')">🌿 Branches</div>
            <div class="tab" onclick="switchTab('issues')">🔍 Indices</div>
        </div>
        
        <div id="files" class="tab-content active">
            <h2>Fichiers du Repository</h2>
            <div class="file-list">
                <?php foreach ($files as $file): ?>
                    <div class="file-item">
                        <h3>
                            <a href="file.php?id=<?php echo $file['id']; ?>">
                                📄 <?php echo htmlspecialchars($file['filename']); ?>
                            </a>
                        </h3>
                        <?php if ($file['is_suspicious']): ?>
                            <span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                                Suspect
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div id="exercises" class="tab-content">
            <h2>Exercices de Programmation</h2>
            <div class="exercise-list">
                <?php foreach ($exercises as $exercise): ?>
                    <div class="exercise-item">
                        <h3><?php echo htmlspecialchars($exercise['title']); ?></h3>
                        <p><?php echo htmlspecialchars($exercise['description']); ?></p>
                        <p><strong>Difficulté:</strong> 
                            <?php 
                            $colors = [
                                'easy' => '#28a745',
                                'medium' => '#ffc107',
                                'hard' => '#dc3545'
                            ];
                            $color = $colors[$exercise['difficulty']] ?? '#6c757d';
                            ?>
                            <span style="color: <?php echo $color; ?>">
                                <?php echo ucfirst($exercise['difficulty']); ?>
                            </span>
                        </p>
                        <a href="exercise.php?id=<?php echo $exercise['id']; ?>" class="btn">
                            Résoudre
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div id="branches" class="tab-content">
    <h2>Code du Repository</h2>
    
    <div class="file-selector" style="margin-bottom: 20px;">
        <label for="file-select">Sélectionner un fichier :</label>
        <select id="file-select" onchange="loadFileContent(this.value)" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
            <option value="">-- Choisir un fichier --</option>
            <?php foreach ($files as $file): ?>
                <option value="<?php echo $file['id']; ?>">
                    <?php echo htmlspecialchars($file['filename']); ?>
                    <?php if ($file['is_suspicious']): ?>🔍<?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div id="file-content" style="display: none;">
        <div style="background: #f6f8fa; border: 1px solid #e1e4e8; border-radius: 6px; overflow: hidden;">
            <div style="background: white; border-bottom: 1px solid #e1e4e8; padding: 12px 16px;">
                <span id="filename-display"></span>
            </div>
            <pre style="margin: 0; padding: 16px; overflow-x: auto; background: white;">
                <code id="code-display"></code>
            </pre>
        </div>
        
        <div style="margin-top: 20px;">
            <button onclick="saveObservation()" class="btn">
                💾 Sauvegarder une observation
            </button>
            <button onclick="markAsClue()" class="btn" style="background: #ffc107;">
                🔍 Marquer comme indice
            </button>
        </div>
    </div>
</div>

<script>
// Données des fichiers
const filesData = {
    <?php foreach ($files as $file): ?>
        <?php echo $file['id']; ?>: {
            filename: "<?php echo addslashes($file['filename']); ?>",
            content: `<?php echo addslashes($file['content']); ?>`,
            language: "<?php echo addslashes($file['language']); ?>",
            isSuspicious: <?php echo $file['is_suspicious'] ? 'true' : 'false'; ?>
        },
    <?php endforeach; ?>
};

function loadFileContent(fileId) {
    if (!fileId) {
        document.getElementById('file-content').style.display = 'none';
        return;
    }
    
    const file = filesData[fileId];
    if (!file) return;
    
    document.getElementById('filename-display').textContent = file.filename;
    document.getElementById('code-display').textContent = file.content;
    document.getElementById('file-content').style.display = 'block';
    
    // Mettre en surbrillance si suspect
    if (file.isSuspicious) {
        document.getElementById('file-content').style.border = '2px solid #dc3545';
    } else {
        document.getElementById('file-content').style.border = '1px solid #e1e4e8';
    }
}

function saveObservation() {
    const fileSelect = document.getElementById('file-select');
    const fileId = fileSelect.value;
    const observation = prompt("Qu'avez-vous observé dans ce code ?");
    
    if (observation && fileId) {
        // Envoyer l'observation au serveur
        fetch('save_observation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'file_id=' + fileId + '&observation=' + encodeURIComponent(observation)
        })
        .then(response => response.text())
        .then(data => {
            alert('Observation sauvegardée !');
        });
    }
}

function markAsClue() {
    const fileSelect = document.getElementById('file-select');
    const fileId = fileSelect.value;
    const clueType = prompt("Type d'indice (bug, pattern, anomalie, etc.) :");
    const description = prompt("Description de l'indice :");
    
    if (clueType && description && fileId) {
        fetch('mark_as_clue.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'file_id=' + fileId + 
                  '&clue_type=' + encodeURIComponent(clueType) +
                  '&description=' + encodeURIComponent(description)
        })
        .then(response => response.text())
        .then(data => {
            alert('Indice marqué !');
        });
    }
}
</script>
        
        <div id="issues" class="tab-content">
            <h2>Indices Trouvés</h2>
            <a href="new_issue.php?repo_id=<?php echo $repo_id; ?>" class="btn" style="margin-bottom: 20px;">
                + Ajouter un indice
            </a>
            <div class="issue-list">
                <?php foreach ($issues as $issue): ?>
                    <div class="issue-item">
                        <h3><?php echo htmlspecialchars($issue['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($issue['description'], 0, 200)); ?>...</p>
                        <p><small>Par <?php echo htmlspecialchars($issue['creator_name']); ?> 
                        le <?php echo date('d/m/Y H:i', strtotime($issue['created_at'])); ?></small></p>
                        <a href="issue_detail.php?id=<?php echo $issue['id']; ?>" class="btn-secondary">
                            Voir détails
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tabName) {
            // Masquer tous les onglets
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Afficher l'onglet sélectionné
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>