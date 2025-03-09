<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$chatManager = new ChatManager();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Get current user information
$currentUser = $auth->getCurrentUser();

// Get all chatrooms
$chatrooms = $chatManager->getChatrooms();

// Set default active chatroom if none is selected
$activeChatroomId = $_GET['room'] ?? ($chatrooms[0]['id'] ?? null);
$activeChatroom = null;

if ($activeChatroomId) {
    $activeChatroom = $chatManager->getChatroom($activeChatroomId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo $activeChatroom ? htmlspecialchars($activeChatroom['name']) : 'Chat Platform'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="chat-container">
        <header class="chat-header">
            <h1>Chat Platform</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($currentUser['username']); ?></span>
                <?php if ($auth->isAdmin()): ?>
                    <a href="admin/index.php" class="btn btn-sm">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </header>
        
        <div class="chat-main">
            <aside class="chat-sidebar">
                <h2>Chatrooms</h2>
                <ul class="chatroom-list">
                    <?php foreach ($chatrooms as $chatroom): ?>
                        <li class="<?php echo $chatroom['id'] == $activeChatroomId ? 'active' : ''; ?>">
                            <a href="?room=<?php echo $chatroom['id']; ?>">
                                <?php echo htmlspecialchars($chatroom['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            
            <div class="chat-content">
                <?php if ($activeChatroom): ?>
                    <div class="chat-header-room">
                        <h2><?php echo htmlspecialchars($activeChatroom['name']); ?></h2>
                        <p><?php echo htmlspecialchars($activeChatroom['description']); ?></p>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <!-- Messages will be loaded here via JavaScript -->
                        <div class="loading-messages">Loading messages...</div>
                    </div>
                    
                    <form id="messageForm" class="message-form">
                        <input type="hidden" id="chatroomId" value="<?php echo $activeChatroomId; ?>">
                        <input type="hidden" id="userId" value="<?php echo $currentUser['id']; ?>">
                        <input type="text" id="messageInput" placeholder="Type your message..." required autocomplete="off">
                        <button type="submit">Send</button>
                    </form>
                <?php else: ?>
                    <div class="no-chatroom">
                        <p>No chatroom available. Please select or create a chatroom.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="js/chat.js"></script>
</body>
</html>