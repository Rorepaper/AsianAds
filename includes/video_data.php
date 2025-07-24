<?php
require_once 'db_operations.php';

// Получаем ID видео из URL
$videoId = basename($_SERVER['PHP_SELF'], '.html');

// Инициализируем данные видео
$videoData = getVideoData($videoId) ?: initializeVideo($videoId, 'Реклама про молоко');

// Обработка отправки комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = htmlspecialchars($_POST['comment']);
    if (addComment($videoId, 'Anonymous', $comment)) {
        updateVideoLikes($videoId, 1);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Получаем комментарии
$comments = getComments($videoId);
?>
