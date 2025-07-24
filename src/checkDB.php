<?php
$conn = mysqli_connect("localhost", "root", "", "AsianAds");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Проверяем структуру таблицы
$result = mysqli_query($conn, "DESCRIBE comments");
if ($result) {
    echo "Структура таблицы comments:<br>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Field: " . $row['Field'] . ", Type: " . $row['Type'] . "<br>";
    }
} else {
    echo "Таблица comments не найдена или ошибка при описании: " . mysqli_error($conn);
}

$conn->close();
?>
