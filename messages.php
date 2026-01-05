<?php
// messages.php - Système de messagerie CORRIGÉ
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Marquer un message comme lu
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $msg_id = $_GET['mark_read'];
    $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE id = ? AND recipient_id = ?");
    $stmt->execute([$msg_id, $user_id]);
    header('Location: messages.php');
    exit();
}

// Récupérer l'utilisateur actuel
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_result = $stmt->fetch();
$user = $user_result ? $user_result : ['username' => 'User'];

// Récupérer tous les messages reçus
$stmt = $pdo->prepare("
    SELECT m.*, u.username as sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.recipient_id = ?
    ORDER BY m.sent_at DESC
");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

// Compter les messages non lus
$stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE recipient_id = ? AND is_read = FALSE");
$stmt->execute([$user_id]);
$unread_result = $stmt->fetch();
$unread = $unread_result ? $unread_result['unread_count'] : 0;

// Voir un message spécifique
$selected_message = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $msg_id = $_GET['view'];
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.id = ? AND m.recipient_id = ?
    ");
    $stmt->execute([$msg_id, $user_id]);
    $selected_message = $stmt->fetch();
    
    // Marquer comme lu automatiquement
    if ($selected_message && !$selected_message['is_read']) {
        $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE id = ?");
        $stmt->execute([$msg_id]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages · GitHub</title>
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
        
        nav {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            position: relative;
        }
        
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #1f6feb;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        
        .messages-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
            min-height: 600px;
        }
        
        .messages-list {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .messages-list-header {
            padding: 16px;
            border-bottom: 1px solid #d8dee4;
            background-color: #f6f8fa;
            font-weight: 600;
        }
        
        .message-item {
            padding: 16px;
            border-bottom: 1px solid #d8dee4;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        
        .message-item:hover {
            background-color: #f6f8fa;
        }
        
        .message-item.unread {
            background-color: #f0f6fc;
            border-left: 3px solid #1f6feb;
        }
        
        .message-item.selected {
            background-color: #ddf4ff;
        }
        
        .message-sender {
            font-weight: 600;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .unread-dot {
            width: 8px;
            height: 8px;
            background-color: #1f6feb;
            border-radius: 50%;
        }
        
        .message-subject {
            font-size: 14px;
            margin-bottom: 4px;
            color: #24292e;
        }
        
        .message-preview {
            font-size: 12px;
            color: #586069;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .message-time {
            font-size: 12px;
            color: #586069;
            margin-top: 4px;
        }
        
        .message-content {
            background: white;
            border: 1px solid #d8dee4;
            border-radius: 6px;
            padding: 24px;
        }
        
        .message-header {
            border-bottom: 1px solid #d8dee4;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        
        .message-header h2 {
            margin: 0 0 8px 0;
        }
        
        .message-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #586069;
            font-size: 14px;
        }
        
        .message-meta a {
            color: #0969da;
            text-decoration: none;
        }
        
        .message-body {
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #586069;
        }
        
        .empty-state h3 {
            margin: 0 0 8px 0;
        }
        
        .system-badge {
            background-color: #ddf4ff;
            color: #0969da;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php" class="logo">GitHub</a>
        <input type="text" class="search-input" placeholder="Search or jump to..." onclick="window.location.href='search.php'">
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="messages.php">
                Messages
                <?php if ($unread > 0): ?>
                    <span class="notification-badge"><?php echo $unread; ?></span>
                <?php endif; ?>
            </a>
            <div class="avatar">
                <?php echo isset($user['username']) ? strtoupper(substr($user['username'], 0, 1)) : 'U'; ?>
            </div>
            <a href="profile.php?user=<?php echo urlencode($user['username']); ?>">
                <?php echo htmlspecialchars($user['username']); ?>
            </a>
        </nav>
    </header>
    
    <div class="container">
        <h1>Messages</h1>
        
        <div class="messages-layout">
            <div class="messages-list">
                <div class="messages-list-header">
                    Inbox (<?php echo count($messages); ?>)
                    <?php if ($unread > 0): ?>
                        - <?php echo $unread; ?> unread
                    <?php endif; ?>
                </div>
                
                <?php if (empty($messages)): ?>
                    <div style="padding: 40px 20px; text-align: center; color: #586069;">
                        No messages yet
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <a href="messages.php?view=<?php echo $msg['id']; ?>" 
                           class="message-item <?php echo !$msg['is_read'] ? 'unread' : ''; ?> <?php echo ($selected_message && $selected_message['id'] == $msg['id']) ? 'selected' : ''; ?>">
                            <div class="message-sender">
                                <?php if (!$msg['is_read']): ?>
                                    <span class="unread-dot"></span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($msg['sender_name']); ?>
                                <?php if ($msg['is_system_message']): ?>
                                    <span class="system-badge">SYSTEM</span>
                                <?php endif; ?>
                            </div>
                            <div class="message-subject">
                                <?php echo htmlspecialchars($msg['subject']); ?>
                            </div>
                            <div class="message-preview">
                                <?php echo htmlspecialchars(substr($msg['body'], 0, 60)) . '...'; ?>
                            </div>
                            <div class="message-time">
                                <?php echo time_ago($msg['sent_at']); ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="message-content">
                <?php if ($selected_message): ?>
                    <div class="message-header">
                        <h2><?php echo htmlspecialchars($selected_message['subject']); ?></h2>
                        <div class="message-meta">
                            From: 
                            <a href="profile.php?user=<?php echo urlencode($selected_message['sender_name']); ?>">
                                <?php echo htmlspecialchars($selected_message['sender_name']); ?>
                            </a>
                            · <?php echo date('F j, Y \a\t g:i A', strtotime($selected_message['sent_at'])); ?>
                            <?php if ($selected_message['is_system_message']): ?>
                                · <span class="system-badge">SYSTEM MESSAGE</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="message-body">
                        <?php echo nl2br(htmlspecialchars($selected_message['body'])); ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Select a message to read</h3>
                        <p>Choose a message from the list to view its contents</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>