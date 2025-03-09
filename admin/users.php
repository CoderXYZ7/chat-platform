<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$userManager = new UserManager();

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
    // Add user
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Please fill in all required fields';
        } else {
            $result = $auth->register($username, $password, $email);
            
            if ($result['success']) {
                if ($isAdmin) {
                    $userManager->updateUser($result['user_id'], ['is_admin' => 1]);
                }
                $success = 'User added successfully';
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Edit user
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $userId = $_POST['user_id'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
        
        if (empty($userId) || empty($username) || empty($email)) {
            $error = 'Please fill in all required fields';
        } else {
            $data = [
                'username' => $username,
                'email' => $email,
                'is_admin' => $isAdmin
            ];
            
            if (!empty($password)) {
                $data['password'] = $password;
            }
            
            $result = $userManager->updateUser($userId, $data);
            
            if ($result['success']) {
                $success = 'User updated successfully';
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Delete user
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $userId = $_POST['user_id'] ?? '';
        
        if (empty($userId)) {
            $error = 'Invalid user ID';
        } elseif ($userId == $currentUser['id']) {
            $error = 'You cannot delete your own account';
        } else {
            $result = $userManager->deleteUser($userId);
            
            if ($result['success']) {
                $success = 'User deleted successfully';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get all users
$users = $userManager->getUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin - Chat Platform</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Manage Users</h1>
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
                        <li class="active"><a href="users.php">Manage Users</a></li>
                        <li><a href="chatrooms.php">Manage Chatrooms</a></li>
                    </ul>
                </nav>
            </aside>
            
            <div class="admin-content">
                <h2>Users</h2>
                
                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="admin-actions-top">
                    <button class="btn" id="showAddUserForm">Add New User</button>
                </div>
                
                <!-- Add User Form (hidden by default) -->
                <div class="admin-form" id="addUserForm" style="display: none;">
                    <h3>Add New User</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="add-username">Username</label>
                            <input type="text" id="add-username" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add-email">Email</label>
                            <input type="email" id="add-email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add-password">Password</label>
                            <input type="password" id="add-password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add-is-admin">
                                <input type="checkbox" id="add-is-admin" name="is_admin">
                                Administrator
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add User</button>
                            <button type="button" class="btn" id="cancelAddUser">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <!-- Users Table -->
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Admin</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td class="actions">
                                    <button class="btn btn-sm edit-user" data-id="<?php echo $user['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                            data-is-admin="<?php echo $user['is_admin']; ?>">
                                        Edit
                                    </button>
                                    
                                    <?php if ($user['id'] != $currentUser['id']): ?>
                                        <form method="POST" action="" class="inline-form delete-form">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger delete-user">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Edit User Modal -->
                <div class="modal" id="editUserModal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h3>Edit User</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="user_id" id="edit-user-id">
                            
                            <div class="form-group">
                                <label for="edit-username">Username</label>
                                <input type="text" id="edit-username" name="username" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-email">Email</label>
                                <input type="email" id="edit-email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-password">Password (leave empty to keep current)</label>
                                <input type="password" id="edit-password" name="password">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-is-admin">
                                    <input type="checkbox" id="edit-is-admin" name="is_admin">
                                    Administrator
                                </label>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update User</button>
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