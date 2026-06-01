<?php
session_start();
if(!isset($_SESSION['user_id'])) die('Чтобы посмотреть историю бронирований, необходимо войти в аккаунт.');
include('db.php');

// Код изменения отзыва (дополнительная информация/отзыв о проведённой конференции)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $request_id = (int)$_POST['request_id'];
    $con->query("UPDATE request SET review='$review' WHERE id='$request_id' AND user_id='$user_id'");
    echo '<div class="success-message">✓ Отзыв о мероприятии успешно сохранён!</div>';
}

// Код истории бронирований
$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if(!$query) die('query error: ' . $con->error); 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои бронирования — Конференции.РФ</title>
    <!-- Roboto: современный гротеск, отличная читаемость -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page-history">
    <div class="container">
        <a href="index.php" class="btn-home">🏠 На главную</a>
        
        <h1>📋 Мои бронирования помещений</h1>
        
        <?php
        $i = 0;
        if($query->num_rows == 0) {
            echo '<div class="empty-state">🎤 У вас пока нет бронирований.<br><br>✍️ <a href="create.php">Забронировать аудиторию, коворкинг или кинозал</a></div>';
        }
        while($request = $query->fetch_assoc()) {
            $i++; 
            
            // Определяем класс статуса (статусы согласно ТЗ)
            $status_class = 'status-new';
            $status_text = htmlspecialchars($request['status']);
            if($status_text == 'Новая') $status_class = 'status-new';
            elseif($status_text == 'Мероприятие назначено') $status_class = 'status-processing';
            elseif($status_text == 'Мероприятие завершено') $status_class = 'status-completed';
            
            // Иконка для типа помещения (curses = venue)
            $venue = htmlspecialchars($request['curses']);
            $venue_icon = '';
            if(strpos($venue, 'Аудитория') !== false) $venue_icon = '🎓';
            elseif(strpos($venue, 'Коворкинг') !== false) $venue_icon = '💼';
            elseif(strpos($venue, 'Кинозал') !== false) $venue_icon = '🎬';
            else $venue_icon = '🏛️';
            
            echo '
            <div class="request">
                <h2>📄 Бронирование #' . $request['id'] . '</h2>
                <p><b>📅 Дата и время:</b> ' . htmlspecialchars($request['date']) . '</p>
                <p><b>' . $venue_icon . ' Тип помещения:</b> ' . $venue . '</p>
                <p><b>💳 Способ оплаты:</b> ' . htmlspecialchars($request['payment']) . '</p>
                <p><b>📊 Статус:</b> <span class="' . $status_class . '">' . $status_text . '</span></p>';

            // Комментарий, оставленный при бронировании (request.comment).
            if(!empty($request['comment'])) {
                echo '<div class="comment-text"><b>📝 Доп. информация:</b> ' . htmlspecialchars($request['comment']) . '</div>';
            }

            // Отзыв о проведённом мероприятии (request.review).
            if(!empty($request['review'])) {
                echo '<div class="review-text"><b>⭐ Ваш отзыв:</b> ' . htmlspecialchars($request['review']) . '</div>';
            }

            // Форма отзыва доступна только после того, как администратор
            // перевёл заявку в статус «Мероприятие завершено» (требование ТЗ).
            // Отзыв пишется в отдельную колонку review и не затирает исходный comment.
            if($request['status'] === 'Мероприятие завершено') {
                $review_placeholder = empty($request['review'])
                    ? '✍️ Оставьте отзыв о качестве организации конференции...'
                    : '✏️ Обновить отзыв';
                $btn_label = empty($request['review']) ? '⭐ Оставить отзыв' : '💾 Сохранить';
                echo '
                <div class="review-form">
                    <form action="" method="POST">
                        <input type="hidden" name="request_id" value="' . $request['id'] . '">
                        <input type="text" name="review" placeholder="' . $review_placeholder . '" value="' . htmlspecialchars($request['review'] ?? '') . '">
                        <button type="submit">' . $btn_label . '</button>
                    </form>
                </div>';
            }
            echo '</div>';
        }
        ?>
        
        <div class="create-button">
            <a href="create.php">🎤 Забронировать помещение</a>
        </div>
    </div>
</body>
</html>