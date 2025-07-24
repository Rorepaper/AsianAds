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

// Логируем полученные данные
file_put_contents('debug.log', "Received data: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Получаем данные из POST
$comment_id = isset($_POST["comment_id"]) ? intval($_POST["comment_id"]) : 0;
$likes = isset($_POST["likes"]) ? intval($_POST["likes"]) : 0;
$action = isset($_POST["action"]) ? $_POST["action"] : '';

if ($comment_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid comment ID']);
    exit;
}

// Обновляем количество лайков в зависимости от действия
$new_likes = $likes;
if ($action === 'like') {
    $new_likes++;
} elseif ($action === 'unlike') {
    $new_likes--;
}

// Проверяем существование комментария
$sql = "SELECT id FROM comments WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Comment not found']);
    exit;
}

// Обновляем данные в базе
$sql = "UPDATE comments SET likes = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $new_likes, $comment_id);

if ($stmt->execute()) {
    $response = [
        'success' => true,
        'likes' => $new_likes,
        'action' => $action
    ];
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['error' => "Update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
