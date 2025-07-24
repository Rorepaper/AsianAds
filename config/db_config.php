<?php

const DB_FILE = __DIR__ . '/../database/asian_ads';
function getDbConnection() {
    try {
        if (!file_exists(DB_FILE)) {
            $dir = dirname(DB_FILE);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            touch(DB_FILE);
            chmod(DB_FILE, 0666);
        }
        
        // Connect to SQLite database
        $conn = new PDO(
            "sqlite:" . DB_FILE,
            null,
            null,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            )
        );
        
        // Initialize database if needed
        if (!checkDatabase()) {
            initDatabase();
        }
        
        return $conn;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        die("Database connection error. Please check error log for details.");
    }
}

// Initialize database if it doesn't exist
function initDatabase() {
    try {
        $conn = getDbConnection();
        
        // Create videos table
        $sql = "CREATE TABLE IF NOT EXISTS videos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            video_id TEXT NOT NULL UNIQUE,
            title TEXT NOT NULL,
            likes INTEGER DEFAULT 0,
            comments INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        
        // Create comments table
        $sql = "CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            video_id TEXT NOT NULL,
            username TEXT NOT NULL,
            comment_text TEXT NOT NULL,
            likes INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (video_id) REFERENCES videos(video_id)
        )";
        $conn->exec($sql);
        
        return true;
    } catch(PDOException $e) {
        error_log("Error creating tables: " . $e->getMessage());
        return false;
    }
}

// Check if database needs initialization
function checkDatabase() {
    try {
        $conn = getDbConnection();
        $stmt = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='videos'");
        return $stmt->fetch() !== false;
    } catch(PDOException $e) {
        error_log("Database check error: " . $e->getMessage());
        return false;
    }
}

// Initialize database if needed
checkDatabase();
?>
