<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$chatManager = new ChatManager();

// Check if user is logged in and is admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Get current user information
$currentUser = $auth->getCurrentUser();

// Success and error messages
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add chatroom
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($name)) {
            $error = 'Chatroom name is required';
        } else {
            $result = $chatManager->createChatroom($name, $description, $currentUser['id']);
            
            if ($result['success']) {
                $success = 'Chatroom created successfully';
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Edit chatroom
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $chatroomId = $_POST['chatroom_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($chatroomId) || empty($name)) {
            $error = 'Chatroom ID and name are required';
        } else {
            $result = $chatManager->updateChatroom($chatroomId, $name, $description);
            
            if ($result['success']) {
                $success = 'Chatroom updated successfully';
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Delete chatroom
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $chatroomId = $_POST['chatroom_id'] ?? '';
        
        if (empty($chatroomId)) {
            $error = 'Invalid chatroom ID';
        } else {
            $result = $chatManager->deleteChatroom($chatroomId);
            
            if ($result['success']) {
                $success = 'Chatroom deleted successfully';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get all chatrooms
$chatrooms = $chatManager->getChatrooms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Chatrooms - Admin - Chat Platform</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Manage Chatrooms</h1>
            <div class="admin-actions">
                <a href="../chat.php" class="btn">Back to Chat</a>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </header>
        
        <div class="admin-main">
            <aside class="admin-sidebar">
                <h2>Navigation</h2>
                <nav>
                    <ul>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="users.php">Manage Users</a></li>
                        <li class="active"><a href="chatrooms.php">Manage Chatrooms</a></li>
                    </ul>
                </nav>
            </aside>
            
            <div class="admin-content">
                <h2>Chatrooms</h2>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="admin-actions-top">
                    <button class="btn" id="showAddChatroomForm">Add New Chatroom</button>
                </div>
                
                <!-- Add Chatroom Form (hidden by default) -->
                <div class="admin-form" id="addChatroomForm" style="display: none;">
                    <h3>Add New Chatroom</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="add-name">Chatroom Name</label>
                            <input type="text" id="add-name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add-description">Description</label>
                            <textarea id="add-description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add Chatroom</button>
                            <button type="button" class="btn" id="cancelAddChatroom">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <!-- Chatrooms Table -->
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chatrooms as $chatroom): ?>
                            <tr>
                                <td><?php echo $chatroom['id']; ?></td>
                                <td><?php echo htmlspecialchars($chatroom['name']); ?></td>
                                <td><?php echo htmlspecialchars($chatroom['description']); ?></td>
                                <td><?php echo htmlspecialchars($chatroom['created_at']); ?></td>
                                <td class="actions">
                                    <button class="btn btn-sm edit-chatroom" data-id="<?php echo $chatroom['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($chatroom['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($chatroom['description']); ?>">
                                        Edit
                                    </button>
                                    
                                    <form method="POST" action="" class="inline-form delete-form">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="chatroom_id" value="<?php echo $chatroom['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger delete-chatroom">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Edit Chatroom Modal -->
                <div class="modal" id="editChatroomModal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h3>Edit Chatroom</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="chatroom_id" id="edit-chatroom-id">
                            
                            <div class="form-group">
                                <label for="edit-name">Chatroom Name</label>
                                <input type="text" id="edit-name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-description">Description</label>
                                <textarea id="edit-description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Chatroom</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../js/admin.js"></script>
</body>
</html>