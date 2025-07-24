<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Получаем данные из POST запроса
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Отладочный вывод
file_put_contents('debug.txt', print_r($data, true));

// Проверяем наличие данных
if (!$data || empty($data['comment'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Отсутствуют данные комментария']);
    exit;
}

// Используем имя из данных, если оно есть
$name = isset($data['name']) && !empty(trim($data['name'])) ? trim($data['name']) : 'Анонимный пользователь';

// Примерные данные для тестирования
$comment = [
    'id' => time(),
    'name' => $name,
    'comment' => $data['comment'],
    'date' => date('d.m.Y H:i'),
    'likes' => 0
];

// Возвращаем успешный ответ
http_response_code(200);
echo json_encode([
    'success' => true,
    'comment' => $comment
]);
