<?php
header('Content-Type: application/json; charset=utf-8');

// Подключаемся к базе данных
$conn = mysqli_connect("localhost", "root", "", "AsianAds");
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получаем данные из POST
$name = $_POST["comment-name"] ?? '';
$comment = $_POST["comment-text"] ?? '';
$video_id = $_POST["video_id"] ?? 0;

// Логируем полученные данные для отладки
file_put_contents('debug.log', "Received data: name=$name, comment=$comment, video_id=$video_id\n", FILE_APPEND);

if (empty($name) || empty($comment) || $video_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Не все поля заполнены']);
    exit;
}

// Проверяем структуру таблицы
$result = mysqli_query($conn, "DESCRIBE comments");
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => "Table structure error: " . mysqli_error($conn)]);
    exit;
}

// Вставляем новый комментарий
$sql = "INSERT INTO comments (videoId, name, comment, likes, date) 
        VALUES (?, ?, ?, 0, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => "Prepare failed: " . mysqli_error($conn)]);
    exit;
}

$stmt->bind_param("iss", $video_id, $name, $comment);

if ($stmt->execute()) {
    // Получаем ID только что добавленного комментария
    $comment_id = $conn->insert_id;
    
    // Возвращаем данные нового комментария
    $response = [
        'success' => true,
        'comment' => [
            'id' => $comment_id,
            'name' => $name,
            'comment' => $comment,
            'likes' => 0,
            'date' => date('Y-m-d H:i:s')
        ]
    ];
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['error' => "Insert failed: " . mysqli_error($conn)]);
}

$stmt->close();
$conn->close();
exit;
