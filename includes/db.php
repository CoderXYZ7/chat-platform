<?php
// Database connection using SQLite
class Database {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new SQLite3('chat.db');
            $this->createTables();
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        // Users table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                is_admin INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');
        
        // Chatrooms table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS chatrooms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                description TEXT,
                created_by INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )
        ');
        
        // Messages table
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                chatroom_id INTEGER,
                user_id INTEGER,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (chatroom_id) REFERENCES chatrooms(id),
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ');
        
        // Create a default admin user if none exists
        $result = $this->db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
        $row = $result->fetchArray();
        if ($row['count'] == 0) {
            $defaultPassword = password_hash("admin123", PASSWORD_DEFAULT);
            $this->db->exec("
                INSERT INTO users (username, password, email, is_admin)
                VALUES ('admin', '$defaultPassword', 'admin@example.com', 1)
            ");
        }
        
        // Create a default chatroom if none exists
        $result = $this->db->query("SELECT COUNT(*) as count FROM chatrooms");
        $row = $result->fetchArray();
        if ($row['count'] == 0) {
            $this->db->exec("
                INSERT INTO chatrooms (name, description, created_by)
                VALUES ('General', 'General chat room', 1)
            ");
        }
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $result = $stmt->execute();
        return $result;
    }
    
    public function fetch($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql, $data);
        
        return $this->db->lastInsertRowID();
    }
    
    public function update($table, $data, $where, $whereParams) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = :$column";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        $params = array_merge($data, $whereParams);
        $this->query($sql, $params);
    }
    
    public function delete($table, $where, $params) {
        $sql = "DELETE FROM $table WHERE $where";
        $this->query($sql, $params);
    }
    
    public function close() {
        $this->db->close();
    }
}