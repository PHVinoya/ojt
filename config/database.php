<?php
class Database {
    private $db_file = 'database/attendance.db';
    private $pdo;
    
    public function __construct() {
        try {
            // Create database directory if it doesn't exist
            if (!file_exists('database')) {
                mkdir('database', 0777, true);
            }
            
            $this->pdo = new PDO('sqlite:' . $this->db_file);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        // Users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id VARCHAR(50) UNIQUE NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(100) NOT NULL,
            age INTEGER,
            phone VARCHAR(20),
            school VARCHAR(100),
            course VARCHAR(100),
            department VARCHAR(100) NOT NULL,
            agency VARCHAR(100) DEFAULT 'SSS Dagupan',
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'trainee',
            qr_code VARCHAR(255) NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);
        
        // Attendance logs table
        $sql = "CREATE TABLE IF NOT EXISTS attendance_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            action VARCHAR(10) NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users (id)
        )";
        $this->pdo->exec($sql);
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}
?>
