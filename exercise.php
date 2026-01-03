<?php
// exercise.php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$exercise_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Récupérer l'exercice
$stmt = $pdo->prepare("
    SELECT e.*, r.name as repo_name, r.id as repo_id
    FROM exercises e
    JOIN repositories r ON e.repository_id = r.id
    WHERE e.id = ?
");
$stmt->execute([$exercise_id]);
$exercise = $stmt->fetch();

if (!$exercise) {
    header('Location: dashboard.php');
    exit();
}

// Vérifier l'accès au repository
$stmt = $pdo->prepare("
    SELECT up.is_unlocked 
    FROM user_progress up 
    WHERE up.user_id = ? AND up.repository_id = ?
");
$stmt->execute([$user_id, $exercise['repo_id']]);
$access = $stmt->fetch();

if (!$access || !$access['is_unlocked']) {
    header('Location: unlock_repository.php?id=' . $exercise['repo_id']);
    exit();
}

// Traitement de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_code = $_POST['code'] ?? '';
    
    // Exécuter le code et vérifier avec les tests
    $stmt = $pdo->prepare("SELECT * FROM tests WHERE exercise_id = ?");
    $stmt->execute([$exercise_id]);
    $tests = $stmt->fetchAll();
    
    $all_passed = true;
    $results = [];
    
    // Simulation de test (à adapter selon le langage)
    foreach ($tests as $test) {
        // Ici, il faudrait un interpréteur/exécuteur de code sécurisé
        // Pour l'instant, simple comparaison
        $passed = ($user_code === $exercise['solution']);
        $all_passed = $all_passed && $passed;
        $results[] = [
            'test' => $test,
            'passed' => $passed
        ];
    }
    
    if ($all_passed) {
        // Marquer l'exercice comme complété
        $stmt = $pdo->prepare("
            INSERT INTO user_exercise_progress (user_id, exercise_id, completed_at, code_submitted)
            VALUES (?, ?, NOW(), ?)
            ON DUPLICATE KEY UPDATE completed_at = NOW(), code_submitted = ?
        ");
        $stmt->execute([$user_id, $exercise_id, $user_code, $user_code]);
        
        $_SESSION['success'] = "Exercice résolu avec succès!";
        header('Location: exercise.php?id=' . $exercise_id);
        exit();
    } else {
        $error = "Certains tests ont échoué. Vérifiez votre solution.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exercise['title']); ?></title>
    <style>
        .code-editor {
            width: 100%;
            height: 300px;
            font-family: monospace;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            resize: vertical;
        }
        
        .test-results {
            margin-top: 20px;
        }
        
        .test-result {
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        
        .test-passed {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .test-failed {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h1><?php echo htmlspecialchars($exercise['title']); ?></h1>
        <p>Repository: <a href="repository.php?id=<?php echo $exercise['repo_id']; ?>">
            <?php echo htmlspecialchars($exercise['repo_name']); ?>
        </a></p>
        
        <div class="card">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($exercise['description'])); ?></p>
        </div>
        
        <div class="card">
            <h3>Code Initial</h3>
            <pre><code><?php echo htmlspecialchars($exercise['initial_code']); ?></code></pre>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="code">Votre Solution</label>
                <textarea id="code" name="code" class="code-editor" required><?php 
                    echo isset($_POST['code']) ? htmlspecialchars($_POST['code']) : $exercise['initial_code'];
                ?></textarea>
            </div>
            
            <button type="submit" class="btn">Tester la Solution</button>
        </form>
        
        <?php if (isset($results)): ?>
            <div class="test-results">
                <h3>Résultats des Tests</h3>
                <?php foreach ($results as $result): ?>
                    <div class="test-result <?php echo $result['passed'] ? 'test-passed' : 'test-failed'; ?>">
                        <?php if ($result['passed']): ?>
                            ✓ Test réussi
                        <?php else: ?>
                            ✗ Test échoué
                        <?php endif; ?>
                        <?php if (!empty($result['test']['input_data'])): ?>
                            <div>Entrée: <?php echo htmlspecialchars($result['test']['input_data']); ?></div>
                        <?php endif; ?>
                        <div>Résultat attendu: <?php echo htmlspecialchars($result['test']['expected_output']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($exercise['solution']) && $all_passed ?? false): ?>
            <div class="card" style="background: #d4edda; border-color: #c3e6cb;">
                <h3>🎉 Félicitations !</h3>
                <p>Vous avez résolu cet exercice. Vous pouvez maintenant passer à la suite.</p>
                <a href="repository.php?id=<?php echo $exercise['repo_id']; ?>" class="btn">
                    Retour au Repository
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
