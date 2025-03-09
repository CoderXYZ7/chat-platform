<?php
require_once 'db.php';

class ChatManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getChatrooms() {
        return $this->db->fetchAll("SELECT * FROM chatrooms ORDER BY name");
    }
    
    public function getChatroom($id) {
        return $this->db->fetch(
            "SELECT * FROM chatrooms WHERE id = :id",
            ['id' => $id]
        );
    }
    
    public function createChatroom($name, $description, $userId) {
        // Check if a chatroom with the same name already exists
        $existingChatroom = $this->db->fetch(
            "SELECT * FROM chatrooms WHERE name = :name",
            ['name' => $name]
        );
        
        if ($existingChatroom) {
            return [
                'success' => false,
                'message' => 'A chatroom with this name already exists'
            ];
        }
        
        $chatroomId = $this->db->insert('chatrooms', [
            'name' => $name,
            'description' => $description,
            'created_by' => $userId
        ]);
        
        return [
            'success' => true,
            'message' => 'Chatroom created successfully',
            'chatroom_id' => $chatroomId
        ];
    }
    
    public function updateChatroom($id, $name, $description) {
        // Check if a different chatroom with the same name already exists
        $existingChatroom = $this->db->fetch(
            "SELECT * FROM chatrooms WHERE name = :name AND id != :id",
            ['name' => $name, 'id' => $id]
        );
        
        if ($existingChatroom) {
            return [
                'success' => false,
                'message' => 'A chatroom with this name already exists'
            ];
        }
        
        $this->db->update(
            'chatrooms',
            [
                'name' => $name,
                'description' => $description
            ],
            'id = :id',
            ['id' => $id]
        );
        
        return [
            'success' => true,
            'message' => 'Chatroom updated successfully'
        ];
    }
    
    public function deleteChatroom($id) {
        // Delete all messages in the chatroom first
        $this->db->delete('messages', 'chatroom_id = :id', ['id' => $id]);
        
        // Then delete the chatroom
        $this->db->delete('chatrooms', 'id = :id', ['id' => $id]);
        
        return [
            'success' => true,
            'message' => 'Chatroom deleted successfully'
        ];
    }
    
    public function getMessages($chatroomId, $limit = 50) {
        return $this->db->fetchAll(
            "SELECT m.*, u.username 
             FROM messages m 
             JOIN users u ON m.user_id = u.id 
             WHERE m.chatroom_id = :chatroom_id 
             ORDER BY m.created_at DESC 
             LIMIT :limit",
            ['chatroom_id' => $chatroomId, 'limit' => $limit]
        );
    }
    
    public function addMessage($chatroomId, $userId, $message) {
        $messageId = $this->db->insert('messages', [
            'chatroom_id' => $chatroomId,
            'user_id' => $userId,
            'message' => $message
        ]);
        
        return [
            'success' => true,
            'message_id' => $messageId
        ];
    }
}

class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getUsers() {
        return $this->db->fetchAll(
            "SELECT id, username, email, is_admin, created_at FROM users ORDER BY username"
        );
    }
    
    public function getUser($id) {
        return $this->db->fetch(
            "SELECT id, username, email, is_admin, created_at FROM users WHERE id = :id",
            ['id' => $id]
        );
    }
    
    public function updateUser($id, $data) {
        // Check if username or email already exists for another user
        if (isset($data['username']) || isset($data['email'])) {
            $existingUser = $this->db->fetch(
                "SELECT * FROM users WHERE (username = :username OR email = :email) AND id != :id",
                [
                    'username' => $data['username'] ?? '',
                    'email' => $data['email'] ?? '',
                    'id' => $id
                ]
            );
            
            if ($existingUser) {
                return [
                    'success' => false,
                    'message' => 'Username or email already exists'
                ];
            }
        }
        
        // If password is being updated, hash it
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        $this->db->update('users', $data, 'id = :id', ['id' => $id]);
        
        return [
            'success' => true,
            'message' => 'User updated successfully'
        ];
    }
    
    public function deleteUser($id) {
        // First, check if this is the last admin
        if ($this->isLastAdmin($id)) {
            return [
                'success' => false,
                'message' => 'Cannot delete the last administrator account'
            ];
        }
        
        // Update messages to anonymous user or delete them
        $this->db->update(
            'messages',
            ['user_id' => null],
            'user_id = :id',
            ['id' => $id]
        );
        
        // Delete the user
        $this->db->delete('users', 'id = :id', ['id' => $id]);
        
        return [
            'success' => true,
            'message' => 'User deleted successfully'
        ];
    }
    
    private function isLastAdmin($id) {
        $user = $this->db->fetch(
            "SELECT is_admin FROM users WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$user || $user['is_admin'] != 1) {
            return false;
        }
        
        $adminCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE is_admin = 1"
        );
        
        return $adminCount['count'] <= 1;
    }
}