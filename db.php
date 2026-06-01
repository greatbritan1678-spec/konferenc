<?php
// PHP 8.1+ по умолчанию кидает mysqli_sql_exception при ошибках mysqli
// (вместо тихих warning'ов). Поэтому проверка `if(!$con) die(...)` без
// try/catch никогда не отработает — необработанное исключение даёт HTTP 500
// без полезного сообщения. Обернём коннект, чтобы пользователь увидел причину.
//
// Чтобы прежний код (без try/catch вокруг каждого prepare/execute) продолжал
// работать в режиме "if false → die", переводим mysqli в тихий режим warning'ов
// после успешного соединения.
try {
    $con = mysqli_connect('MySQL-8.4', 'root', '', 'demoexam');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('Ошибка подключения к базе данных: ' . htmlspecialchars($e->getMessage()));
}

if (!$con) {
    http_response_code(500);
    die('Ошибка подключения к базе данных: ' . mysqli_connect_error());
}

mysqli_set_charset($con, 'utf8mb4');

// Возвращаем тихое поведение для последующих запросов:
// prepare/execute вернут false при ошибке, существующий код это уже учитывает.
mysqli_report(MYSQLI_REPORT_OFF);
