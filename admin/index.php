<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$userManager = new UserManager();
$chatManager = new ChatManager();

// Check if user is logged in and is admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Get current user information
$currentUser = $auth->getCurrentUser();

// Get statistics
$users = $userManager->getUsers();
$userCount = count($users);

$chatrooms = $chatManager->getChatrooms();
$chatroomCount = count($chatrooms);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Chat Platform</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
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
                        <li class="active"><a href="index.php">Dashboard</a></li>
                        <li><a href="users.php">Manage Users</a></li>
                        <li><a href="chatrooms.php">Manage Chatrooms</a></li>
                    </ul>
                </nav>
            </aside>
            
            <div class="admin-content">
                <h2>Dashboard</h2>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Users</h3>
                        <div class="stat-value"><?php echo $userCount; ?></div>
                        <a href="users.php" class="btn btn-sm">Manage Users</a>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Chatrooms</h3>
                        <div class="stat-value"><?php echo $chatroomCount; ?></div>
                        <a href="chatrooms.php" class="btn btn-sm">Manage Chatrooms</a>
                    </div>
                </div>
                
                <div class="recent-section">
                    <h3>Recent Users</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Admin</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recentUsers = array_slice($users, 0, 5);
                            foreach ($recentUsers as $user): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="recent-section">
                    <h3>Chatrooms</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chatrooms as $chatroom): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($chatroom['name']); ?></td>
                                    <td><?php echo htmlspecialchars($chatroom['description']); ?></td>
                                    <td><?php echo htmlspecialchars($chatroom['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../js/admin.js"></script>
</body>
</html>