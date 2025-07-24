<?php
require_once '../config/db_config.php';

// Function to add video data
function addVideo($videoId, $title, $likes = 0, $comments = 0) {
    try {
        $conn = getDbConnection();
        $sql = "INSERT INTO videos (video_id, title, likes, comments) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$videoId, $title, $likes, $comments]);
        
        if (!$result) {
            error_log("Failed to add video: " . $stmt->errorInfo()[2]);
            return false;
        }
        
        return true;
    } catch(PDOException $e) {
        error_log("Error adding video: " . $e->getMessage());
        return false;
    }
}

// Function to add comment
function addComment($videoId, $username, $commentText) {
    try {
        $conn = getDbConnection();
        $sql = "INSERT INTO comments (video_id, username, comment_text) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$videoId, $username, $commentText]);
        
        if (!$result) {
            error_log("Failed to add comment: " . $stmt->errorInfo()[2]);
            return false;
        }
        
        // Update comments count for video
        $sql = "UPDATE videos SET comments = comments + 1 WHERE video_id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$videoId]);
        
        if (!$result) {
            error_log("Failed to update comments count: " . $stmt->errorInfo()[2]);
        }
        
        return true;
    } catch(PDOException $e) {
        error_log("Error adding comment: " . $e->getMessage());
        return false;
    }
}

// Function to get comments for a video
function getComments($videoId) {
    try {
        $conn = getDbConnection();
        $sql = "SELECT id, username, comment_text, created_at, likes FROM comments WHERE video_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$videoId]);
        
        if (!$result) {
            error_log("Failed to get comments: " . $stmt->errorInfo()[2]);
            return [];
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting comments: " . $e->getMessage());
        return [];
    }
}

// Function to update likes for a video
function updateLikes($videoId, $newLikes) {
    try {
        $conn = getDbConnection();
        $sql = "UPDATE videos SET likes = likes + ? WHERE video_id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$newLikes, $videoId]);
        
        if (!$result) {
            error_log("Failed to update likes: " . $stmt->errorInfo()[2]);
            return false;
        }
        
        return true;
    } catch(PDOException $e) {
        error_log("Error updating likes: " . $e->getMessage());
        return false;
    }
}

// Function to get video data
function getVideoData($videoId) {
    try {
        $conn = getDbConnection();
        $sql = "SELECT * FROM videos WHERE video_id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$videoId]);
        
        if (!$result) {
            error_log("Failed to get video data: " . $stmt->errorInfo()[2]);
            return null;
        }
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error getting video data: " . $e->getMessage());
        return null;
    }
}

// Function to update video likes
function updateVideoLikes($videoId, $newLikes) {
    try {
        $conn = getDbConnection();
        $sql = "UPDATE videos SET likes = likes + ? WHERE video_id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$newLikes, $videoId]);
        
        if (!$result) {
            error_log("Failed to update video likes: " . $stmt->errorInfo()[2]);
            return false;
        }
        
        return true;
    } catch(PDOException $e) {
        error_log("Error updating video likes: " . $e->getMessage());
        return false;
    }
}

// Function to update comment likes
function updateCommentLikes($commentId, $newLikes) {
    try {
        $conn = getDbConnection();
        $sql = "UPDATE comments SET likes = likes + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$newLikes, $commentId]);
        
        if (!$result) {
            error_log("Failed to update comment likes: " . $stmt->errorInfo()[2]);
            return false;
        }
        
        return true;
    } catch(PDOException $e) {
        error_log("Error updating comment likes: " . $e->getMessage());
        return false;
    }
}

// Function to initialize video data if it doesn't exist
function initializeVideo($videoId, $title) {
    try {
        $videoData = getVideoData($videoId);
        
        if (!$videoData) {
            if (!addVideo($videoId, $title)) {
                return null;
            }
        }
        
        return getVideoData($videoId);
    } catch(PDOException $e) {
        error_log("Error initializing video: " . $e->getMessage());
        return null;
    }
}

