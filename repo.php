<?php
// repo.php - Vue détaillée d'un repository
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$repo_id = $_GET['id'] ?? 0;

// Récupérer le repository
$stmt = $pdo->prepare("
    SELECT r.*, u.username as owner_name, u.id as owner_id, u.is_arg_character
    FROM repositories r
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$repo_id]);
$repo = $stmt->fetch();

if (!$repo) {
    header('Location: dashboard.php');
    exit();
}

// Vérifier l'accès si c'est un personnage ARG
if ($repo['is_arg_character']) {
    $stmt = $pdo->prepare("SELECT id FROM player_progress WHERE player_id = ? AND unlocked_user_id = ?");
    $stmt->execute([$_SESSION['user_id'], $repo['owner_id']]);
    if (!$stmt->fetch()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Récupérer les fichiers
$stmt = $pdo->prepare("SELECT * FROM repository_files WHERE repository_id = ? ORDER BY filepath, filename");
$stmt->execute([$repo_id]);
$files = $stmt->fetchAll();

// Récupérer les commits
$stmt = $pdo->prepare("
    SELECT c.*, u.username as author_name
    FROM commits c
    JOIN users u ON c.committed_by = u.id
    WHERE c.repository_id = ?
    ORDER BY c.committed_at DESC
    LIMIT 10
");
$stmt->execute([$repo_id]);
$commits = $stmt->fetchAll();

// Récupérer les issues
$stmt = $pdo->prepare("
    SELECT i.*, u.username as creator_name
    FROM issues i
    JOIN users u ON i.created_by = u.id
    WHERE i.repository_id = ?
    ORDER BY i.created_at DESC
");
$stmt->execute([$repo_id]);
$issues = $stmt->fetchAll();

// Récupérer l'application fonctionnelle si elle existe
$stmt = $pdo->prepare("SELECT * FROM functional_apps WHERE repository_id = ?");
$stmt->execute([$repo_id]);
$app = $stmt->fetch();

// Vérification du code secret
$secret_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['secret_key'])) {
    $entered_key = trim($_POST['secret_key']);
    if ($app && $entered_key === $app['secret_key']) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO found_secrets (player_id, repository_id, secret_key) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $repo_id, $entered_key]);
        $secret_message = 'Correct! Use this code in search: ' . htmlspecialchars($app['unlock_code']);
    } else {
        $secret_message = 'Incorrect key.';
    }
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($repo['owner_name'] . '/' . $repo['name']); ?> · GitHub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f6f8fa;
        }
        header {
            background-color: #24292e;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        .search-input {
            flex: 1;
            max-width: 600px;
            padding: 8px 12px;
            border: 1px solid #444;
            border-radius: 6px;
            background-color: #1c1f23;
            color: white;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 16px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px;
        }
        .repo-header {
            margin: 20px 0;
        }
        .repo-title {
            font-size: 24px;
            color: #0969da;
            text-decoration: none;
        }
        .tabs {
            border-bottom: 1px solid #d8dee4;
            margin: 20px 0;
        }
        .tabs a {
            display: inline-block;
            padding: 12px 16px;
            color: #24292e;
            text-decoration: none;
            border-bottom: 2px solid transparent;
        }
        .tabs a.active {
            border-bottom-color: #fd8c73;
        }
        .file-browser {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            margin: 20px 0;
        }
        .file-item {
            padding: 12px 16px;
            border-bottom: 1px solid #d8dee4;
        }
        .file-item:last-child {
            border-bottom: none;
        }
        .file-item a {
            color: #0969da;
            text-decoration: none;
        }
        .code-viewer {
            background: #f6f8fa;
            padding: 16px;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            margin: 20px 0;
        }
        .code-viewer pre {
            margin: 0;
            overflow-x: auto;
        }
        .commit-list, .issue-list {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            margin: 20px 0;
        }
        .commit-item, .issue-item {
            padding: 12px 16px;
            border-bottom: 1px solid #d8dee4;
        }
        .commit-item:last-child, .issue-item:last-child {
            border-bottom: none;
        }
        .app-link {
            background: #2ea44f;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            margin: 20px 0;
        }
        .secret-form {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .secret-form input {
            padding: 8px 12px;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            width: 300px;
        }
        .secret-form button {
            background: #0969da;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .message {
            padding: 12px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .message.success {
            background: #dafbe1;
            border: 1px solid #2ea44f;
            color: #1a7f37;
        }
        .message.error {
            background: #ffebe9;
            border: 1px solid #ff7b72;
            color: #cf222e;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <input type="text" class="search-input" placeholder="Search or jump to..." onclick="window.location.href='search.php'">
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php"><?php echo htmlspecialchars($current_user['username']); ?></a>
        </nav>
    </header>
    
    <div class="container">
        <div class="repo-header">
            <h1>
                <a href="profile.php?user=<?php echo urlencode($repo['owner_name']); ?>" class="repo-title">
                    <?php echo htmlspecialchars($repo['owner_name']); ?>
                </a>
                / 
                <span class="repo-title"><?php echo htmlspecialchars($repo['name']); ?></span>
            </h1>
            <p><?php echo htmlspecialchars($repo['description']); ?></p>
        </div>
        
        <div class="tabs">
            <a href="#" class="active">Code</a>
            <a href="#issues">Issues (<?php echo count($issues); ?>)</a>
            <a href="#commits">Commits (<?php echo count($commits); ?>)</a>
        </div>
        
        <h2>Files</h2>
        <div class="file-browser">
            <?php if (empty($files)): ?>
                <div class="file-item">No files in this repository.</div>
            <?php else: ?>
                <?php foreach ($files as $file): ?>
                    <div class="file-item">
                        <a href="file.php?id=<?php echo $file['id']; ?>">
                            📄 <?php echo htmlspecialchars($file['filepath'] ? $file['filepath'] . '/' : ''); ?><?php echo htmlspecialchars($file['filename']); ?>
                        </a>
                        <?php
                        $has_clue = strpos(strtolower($file['content'] ?? ''), 'secret') !== false ||
                                    strpos(strtolower($file['content'] ?? ''), 'key') !== false ||
                                    strpos(strtolower($file['content'] ?? ''), 'password') !== false;
                        if ($has_clue && $repo['is_arg_character']): ?>
                            <span style="background: #fff8c5; color: #d4a72c; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 10px;">
                                🔍 Contains clues
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($app): ?>
            <div style="background: linear-gradient(45deg, #6e40c9, #3d8bb1); color: white; padding: 20px; border-radius: 6px; margin: 20px 0;">
                <h3>🚀 Live Application: <?php echo htmlspecialchars($app['app_filename']); ?></h3>
                <p>This functional application may contain hidden clues. Interact with it to find secrets!</p>
                <a href="apps/<?php echo htmlspecialchars($app['app_filename']); ?>" target="_blank"
                   style="background: white; color: #6e40c9; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-block; margin-top: 10px;">
                    Open Application
                </a>
            </div>

            <div class="secret-form">
                <h3>Found a secret key?</h3>
                <p>If you discovered a secret key in the application or code, enter it here:</p>
                <form method="POST">
                    <input type="text" name="secret_key" placeholder="Enter secret key (e.g., alpha-123)">
                    <button type="submit" style="background: #2ea44f; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer;">
                        Verify Key
                    </button>
                </form>
                <?php if ($secret_message): ?>
                    <div style="margin-top: 10px; padding: 10px;
                         background: <?php echo strpos($secret_message, 'Correct') !== false ? '#dafbe1' : '#ffebe9'; ?>;
                         border: 1px solid <?php echo strpos($secret_message, 'Correct') !== false ? '#2ea44f' : '#cf222e'; ?>;
                         border-radius: 6px;">
                        <?php echo $secret_message; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <h2>Recent Commits</h2>
        <div class="commit-list">
            <?php if (empty($commits)): ?>
                <div class="commit-item">No commits yet.</div>
            <?php else: ?>
                <?php foreach ($commits as $commit): ?>
                    <div class="commit-item">
                        <strong><?php echo htmlspecialchars($commit['message']); ?></strong><br>
                        <small>
                            <?php echo htmlspecialchars($commit['author_name']); ?> committed
                            <?php echo time_ago($commit['committed_at']); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <h2 id="issues">Issues</h2>
        <div class="issue-list">
            <?php if (empty($issues)): ?>
                <div class="issue-item">No issues.</div>
            <?php else: ?>
                <?php foreach ($issues as $issue): ?>
                    <div class="issue-item">
                        <a href="issue.php?id=<?php echo $issue['id']; ?>" style="color: #24292e; text-decoration: none;">
                            <strong><?php echo htmlspecialchars($issue['title']); ?></strong>
                            <span style="color: <?php echo $issue['status'] === 'open' ? '#1a7f37' : '#cf222e'; ?>">
                                [<?php echo strtoupper($issue['status']); ?>]
                            </span>
                        </a><br>
                        <small>
                            Opened by <?php echo htmlspecialchars($issue['creator_name']); ?> 
                            <?php echo time_ago($issue['created_at']); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<!-- ============================================================
     EASTER EGG: GRAVITY (alpha-init-2)
     ============================================================ -->
<style>
.gravity-active * { transition: none !important; }
</style>

<!-- ============================================================
     EASTER EGG: CURSED (beta-enc-2)
     ============================================================ -->
<div id="cursed-overlay" style="
    display:none; position:fixed; inset:0; z-index:99999;
    background:#000; overflow:hidden; cursor:pointer;
">
    <!-- Scan lines -->
    <div style="position:absolute;inset:0;pointer-events:none;
         background:repeating-linear-gradient(0deg,rgba(0,0,0,0.35) 0px,rgba(0,0,0,0.35) 1px,transparent 1px,transparent 3px);
         z-index:2;"></div>
    <!-- Grain noise canvas -->
    <canvas id="cursed-grain" style="position:absolute;inset:0;opacity:0.18;z-index:3;"></canvas>
    <!-- Chromatic aberration layers -->
    <img id="cursed-img-r" src="images/cursed_face.png" alt="" style="
        position:absolute; width:100%; height:100%; object-fit:cover;
        mix-blend-mode:screen; opacity:0.6; filter:url(#glitch-r);
        transform:translate(4px,0); z-index:4;">
    <img id="cursed-img-b" src="images/cursed_face.png" alt="" style="
        position:absolute; width:100%; height:100%; object-fit:cover;
        mix-blend-mode:screen; opacity:0.6; filter:url(#glitch-b);
        transform:translate(-4px,0); z-index:4;">
    <img id="cursed-img" src="images/cursed_face.png" alt="" style="
        position:absolute; width:100%; height:100%; object-fit:cover;
        z-index:5; animation: cursed-pulse 0.08s infinite;">
    <!-- Glitch slices -->
    <div id="cursed-slices" style="position:absolute;inset:0;z-index:6;pointer-events:none;"></div>
    <!-- Creepy text -->
    <div id="cursed-text" style="
        position:absolute; bottom:8vh; left:0; right:0; text-align:center;
        z-index:10; pointer-events:none;
        font-family: 'Courier New', monospace; color:#ff0000;
        font-size: clamp(14px,2.5vw,28px); letter-spacing:0.3em;
        text-shadow: 0 0 8px #f00, 0 0 20px #f00;
    "></div>
    <!-- Click to close hint -->
    <div style="position:absolute;top:16px;right:24px;z-index:20;
                color:rgba(255,50,50,0.35);font-size:11px;font-family:monospace;letter-spacing:0.2em;">
        CLICK TO CLOSE
    </div>

    <!-- SVG filters for channel split -->
    <svg style="position:absolute;width:0;height:0">
        <filter id="glitch-r" color-interpolation-filters="sRGB">
            <feColorMatrix type="matrix"
                values="1 0 0 0 0   0 0 0 0 0   0 0 0 0 0   0 0 0 1 0"/>
        </filter>
        <filter id="glitch-b" color-interpolation-filters="sRGB">
            <feColorMatrix type="matrix"
                values="0 0 0 0 0   0 0 0 0 0   0 0 1 0 0   0 0 0 1 0"/>
        </filter>
    </svg>
</div>

<style>
@keyframes cursed-pulse {
    0%,100% { opacity:1; transform:scale(1) skewX(0deg); filter:brightness(1) contrast(1.2); }
    15%     { opacity:0.85; transform:scale(1.002) skewX(-0.4deg); filter:brightness(1.4) contrast(2) hue-rotate(5deg); }
    30%     { opacity:1; transform:scale(0.999) skewX(0.2deg); filter:brightness(0.9) contrast(1.5) saturate(2); }
    50%     { opacity:0.95; transform:scale(1.001) skewX(0.3deg); filter:brightness(1.1) contrast(1.8); }
    70%     { opacity:0.7; transform:scale(1) skewX(-0.15deg); filter:brightness(2) contrast(3) hue-rotate(-10deg); }
}
@keyframes cursed-text-glitch {
    0%,100% { clip-path:none; transform:translate(0,0); }
    20%     { clip-path:inset(40% 0 30% 0); transform:translate(-8px,0); }
    40%     { clip-path:inset(10% 0 60% 0); transform:translate(6px,0); }
    60%     { clip-path:none; transform:translate(0,0); }
    80%     { clip-path:inset(70% 0 5% 0);  transform:translate(-3px,0); }
}
</style>

<script>
/* =====================================================
   TAG DES COMMITS EASTER EGG — détection par texte DOM
   ===================================================== */
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.commit-item').forEach(function(item){
        const msg = item.querySelector('strong');
        if (!msg) return;
        const text = msg.textContent.trim();
        if (text === 'Add ROT-13 shortcut, fix edge cases'){
            item.dataset.easter = 'gravity';
            item.style.cursor   = 'pointer';
            const tag = document.createElement('span');
            tag.textContent = ' [click me]';
            tag.style.cssText = 'font-size:10px;color:#888;';
            msg.appendChild(tag);
        }
        if (text === 'WIP: AES wrapper (not ready)'){
            item.dataset.easter = 'cursed';
            item.style.cursor   = 'pointer';
            const tag = document.createElement('span');
            tag.textContent = ' [click me]';
            tag.style.cssText = 'font-size:10px;color:#888;';
            msg.appendChild(tag);
        }
    });
});

/* =====================================================
   GOOGLE GRAVITY — alpha-init-2
   ===================================================== */
(function(){
    function launchGravity(){
        // On rend tout le body en position relative puis on simule la physique
        document.body.style.overflow = 'hidden';

        // Collecter TOUS les éléments visibles de premier niveau et leurs enfants directs
        const candidates = Array.from(document.querySelectorAll(
            'header, .container, .container > *, .commit-list, .file-browser, .issue-list, h1, h2, h3, p, .tabs, .secret-form'
        ));

        // Dédupliquer (garder les plus hauts dans le DOM)
        const elements = candidates.filter(el => {
            return !candidates.some(other => other !== el && other.contains(el));
        });

        const physics = elements.map(el => {
            const rect = el.getBoundingClientRect();
            const clone = el.cloneNode(true);
            // Style absolu fixe
            clone.style.cssText = `
                position:fixed !important;
                left:${rect.left}px !important;
                top:${rect.top}px !important;
                width:${rect.width}px !important;
                height:${rect.height}px !important;
                margin:0 !important;
                z-index:9000;
                pointer-events:none;
                box-sizing:border-box;
                transform-origin:center center;
            `;
            document.body.appendChild(clone);
            el.style.visibility = 'hidden';
            return {
                el: clone,
                x: rect.left,
                y: rect.top,
                w: rect.width,
                h: rect.height,
                vx: (Math.random() - 0.5) * 2,
                vy: Math.random() * -2,
                angle: 0,
                av: (Math.random() - 0.5) * 3,
            };
        });

        const g = 0.6;
        const floor = window.innerHeight;

        function tick(){
            let allStopped = true;
            physics.forEach(p => {
                p.vy += g;
                p.x  += p.vx;
                p.y  += p.vy;

                // Sol
                if (p.y + p.h >= floor){
                    p.y = floor - p.h;
                    p.vy *= -0.35;
                    p.vx *= 0.88;
                    p.av *= 0.85;
                    if (Math.abs(p.vy) < 0.8) { p.vy = 0; }
                }
                // Murs
                if (p.x < 0)                    { p.x = 0;                    p.vx *= -0.4; }
                if (p.x + p.w > window.innerWidth){ p.x = window.innerWidth - p.w; p.vx *= -0.4; }

                p.angle += p.av;
                p.el.style.transform = `rotate(${p.angle}deg)`;
                p.el.style.left = p.x + 'px';
                p.el.style.top  = p.y + 'px';

                if (Math.abs(p.vy) > 0.5 || Math.abs(p.vx) > 0.5) allStopped = false;
            });
            if (!allStopped) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);

        // Fond noir progressif
        const bg = document.createElement('div');
        bg.style.cssText = 'position:fixed;inset:0;z-index:8999;background:#0d1117;';
        document.body.insertBefore(bg, document.body.firstChild);
    }

    document.addEventListener('click', function(e){
        const item = e.target.closest('[data-easter="gravity"]');
        if (item) launchGravity();
    });
})();


/* =====================================================
   CURSED — beta-enc-2
   ===================================================== */
(function(){
    const overlay  = document.getElementById('cursed-overlay');
    const textEl   = document.getElementById('cursed-text');
    const slicesEl = document.getElementById('cursed-slices');
    const grain    = document.getElementById('cursed-grain');
    const gctx     = grain ? grain.getContext('2d') : null;
    const imgR     = document.getElementById('cursed-img-r');
    const imgB     = document.getElementById('cursed-img-b');

    if (!overlay) return;

    const messages = [
        'W̷H̵O̶ ̷A̴R̵E̵ ̴Y̶O̴U̷',
        'y̸̢̛͓̣͉̦͊̿͂̊̓o̷͍̅̿̎̍̏ǘ̸͚͇͗̃ ̸̨̹̏̃̕s̶̡̒͌h̷͓̞̉̓ő̶̱̘̹u̵͍̤͗̓l̴̤͑̄d̸̨͛n̸͍̤̙̐̔̍̄͠͝ͅ'̷͚̓͝t̴̤̺͊͜ ̴̡̦͒͊b̷̡̈ẻ̶̻͉̱̊̊͜ ̴̛̳͌̑ȟ̸̯̮̀̓̕ͅe̸͙̗͊̒r̷̡̢̈́e̴̱͒',
        'IT KNOWS YOU CLICKED',
        'c̸̛͕͔͈̹̑̄̈́ǒ̸̢m̴̡̨̍͐̋̂ë̶͕͙̱̺́ ̷̧͎̄̊̐c̸̯̊̈́͌l̶̦͙̿̈͝o̵̖͆̍̒s̶̨̺̟̈́͒̑ē̷̬̖͋r̴̘͝',
        '> ERROR: REALITY.EXE HAS STOPPED',
        'IM INSIDE THE SCREEN',
        'D̷̜̈́O̶̰̎N̸͔̐T̷̥̓ ̸̹̿T̵̬͋U̴̲̍R̸̜̍N̷̻͆ ̴̠̎A̶̦͘R̴͇̿O̵͚͠U̵̡͂N̵̤̽D̷͓̾',
        '404: SOUL NOT FOUND',
        'ᴵ ᴴᴬᵛᴱ ᴮᴱᴱᴺ ᵂᴬᴵᵀᴵᴺᴳ',
        'the code was never about encryption',
    ];
    let msgIdx = 0, msgTimer, glitchTimer, chromaTimer;

    function showNextMessage(){
        if (!textEl) return;
        textEl.style.animation = 'none';
        textEl.textContent = messages[msgIdx % messages.length];
        msgIdx++;
        // glitch the text
        void textEl.offsetWidth;
        textEl.style.animation = 'cursed-text-glitch 0.4s steps(1) 3';
    }

    function spawnSlice(){
        if (!slicesEl) return;
        const s = document.createElement('div');
        const h = 4 + Math.random() * 40;
        const y = Math.random() * 100;
        const offset = (Math.random() - 0.5) * 80;
        s.style.cssText = `
            position:absolute; left:0; right:0;
            top:${y}%; height:${h}px;
            background:inherit;
            transform:translateX(${offset}px);
            overflow:hidden;
            mix-blend-mode:difference;
            background-color:rgba(${Math.random()>0.5?'255,0,0':'0,255,200'},0.12);
        `;
        slicesEl.appendChild(s);
        setTimeout(()=> s.remove(), 80 + Math.random()*120);
    }

    function animateChroma(){
        if (!imgR || !imgB) return;
        const ox = (Math.random()-0.5)*18;
        const oy = (Math.random()-0.5)*6;
        imgR.style.transform = `translate(${ox}px,${oy}px)`;
        imgB.style.transform = `translate(${-ox}px,${-oy}px)`;
    }

    function drawGrain(){
        if (!gctx || !grain) return;
        grain.width  = window.innerWidth;
        grain.height = window.innerHeight;
        const img = gctx.createImageData(grain.width, grain.height);
        for (let i=0; i<img.data.length; i+=4){
            const v = Math.random()*255|0;
            img.data[i]=img.data[i+1]=img.data[i+2]=v;
            img.data[i+3]=255;
        }
        gctx.putImageData(img,0,0);
    }

    function shakeOverlay(){
        const dx = (Math.random()-0.5)*10;
        const dy = (Math.random()-0.5)*6;
        overlay.style.transform = `translate(${dx}px,${dy}px)`;
        setTimeout(()=>{ overlay.style.transform=''; }, 50);
    }

    function openCursed(){
        overlay.style.display = 'block';
        msgIdx = 0;
        showNextMessage();
        msgTimer   = setInterval(()=>{ showNextMessage(); shakeOverlay(); }, 1800);
        glitchTimer= setInterval(()=>{ spawnSlice(); spawnSlice(); drawGrain(); }, 60);
        chromaTimer= setInterval(animateChroma, 120);

        // Bruit blanc — AudioContext
        try {
            const ctx = new (window.AudioContext||window.webkitAudioContext)();
            const buf = ctx.createBuffer(1, ctx.sampleRate*0.3, ctx.sampleRate);
            const d   = buf.getChannelData(0);
            for(let i=0;i<d.length;i++) d[i]=(Math.random()*2-1)*0.15;
            const src  = ctx.createBufferSource();
            const gain = ctx.createGain();
            src.buffer=buf; src.loop=true;
            gain.gain.value=0.12;
            src.connect(gain); gain.connect(ctx.destination);
            src.start();
            overlay._audioCtx = ctx;
            overlay._audioSrc = src;
        } catch(e){}
    }

    function closeCursed(){
        overlay.style.display = 'none';
        clearInterval(msgTimer);
        clearInterval(glitchTimer);
        clearInterval(chromaTimer);
        if(slicesEl) slicesEl.innerHTML='';
        try{ overlay._audioSrc && overlay._audioSrc.stop(); overlay._audioCtx && overlay._audioCtx.close(); } catch(e){}
    }

    overlay.addEventListener('click', closeCursed);

    document.addEventListener('click', function(e){
        const item = e.target.closest('[data-easter="cursed"]');
        if (item) openCursed();
    });
})();
</script>
</body>
</html>