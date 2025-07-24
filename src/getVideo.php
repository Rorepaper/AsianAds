<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Удаляем все выводы перед JSON
ob_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$videoId = isset($_POST["videoId"]) ? intval($_POST["videoId"]) : 0;
$order = isset($_POST["order"]) ? intval($_POST["order"]) : 3; // По умолчанию сортировка по дате DESC

$conn = mysqli_connect("localhost", "root", "", "AsianAds");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

// Подготовленный запрос для предотвращения SQL инъекции
$stmt = $conn->prepare("SELECT v.name, v.likes, v.description, 
                        c.date, c.name as comment_name, c.comment, c.likes as comment_likes
                     FROM Videos v
                     LEFT JOIN comments c ON v.id = c.videoId
                     WHERE v.id = ?");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $videoId);
$stmt->execute();
$result = $stmt->get_result();

$video = [];
$comments = [];

while($row = $result->fetch_assoc()) {
    if (empty($video)) {
        $video = [
            'name' => $row['name'],
            'likes' => $row['likes'],
            'description' => $row['description']
        ];
    }
    if ($row['comment'] !== null) {
        $comments[] = [
            'date' => $row['date'],
            'name' => $row['comment_name'],
            'comment' => $row['comment'],
            'likes' => $row['comment_likes']
        ];
    }
}

$stmt->close();
$conn->close();

// Сортируем комментарии в зависимости от параметра order
switch($order) {
    case 1:
        usort($comments, function($a, $b) { return $b['likes'] - $a['likes']; });
        break;
    case 2:
        usort($comments, function($a, $b) { return $a['likes'] - $b['likes']; });
        break;
    case 3:
        usort($comments, function($a, $b) { return strtotime($b['date']) - strtotime($a['date']); });
        break;
    case 4:
        usort($comments, function($a, $b) { return strtotime($a['date']) - strtotime($b['date']); });
        break;
}

$response = [
    'video' => $video,
    'comments' => $comments
];

// Очищаем буфер вывода
ob_clean();

// Возвращаем только JSON
header('Content-Length: ' . strlen(json_encode($response, JSON_UNESCAPED_UNICODE)));
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;