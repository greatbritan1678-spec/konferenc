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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ===== ЦВЕТОВАЯ СХЕМА (ТЗ) ===== */
        :root {
            --gray-dark: #343A40;   /* основной серый */
            --gray-light: #CED4DA;  /* светло-серый */
            --green: #28A745;       /* акцентный зелёный */
            --white: #FFFFFF;       /* белоснежный */
            --gray-bg: #F4F6F8;     /* светлый фон */
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, var(--gray-bg) 0%, #eef2f5 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 950px;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            animation: slideInUp 0.5s ease-out;
            border: 1px solid var(--gray-light);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Кнопка на главную */
        .btn-home {
            display: inline-block;
            background: var(--green);
            color: var(--white);
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 40px;
            margin-bottom: 28px;
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .btn-home:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 32px;
            color: var(--gray-dark);
            font-size: 28px;
            font-weight: 700;      /* Bold */
            letter-spacing: -0.2px;
        }

        /* Сообщение об успехе */
        .success-message {
            background: #e3f5e8;
            color: #155724;
            padding: 14px 18px;
            border-radius: 20px;
            margin-bottom: 24px;
            text-align: center;
            border-left: 4px solid var(--green);
            font-size: 14px;
            font-weight: 400;
            animation: slideInRight 0.4s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Карточка бронирования */
        .request {
            border: 1px solid var(--gray-light);
            margin: 20px 0;
            padding: 24px;
            border-radius: 24px;
            background: var(--white);
            transition: all 0.25s ease;
        }

        .request:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
            border-color: var(--green);
        }

        .request h2 {
            margin-top: 0;
            color: var(--gray-dark);
            font-size: 20px;
            font-weight: 600;      /* Semi-Bold */
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-light);
        }

        .request b {
            color: var(--gray-dark);
            font-weight: 600;
        }

        .request p {
            margin: 10px 0;
            font-weight: 400;
            font-size: 15px;
        }

        /* Статусы бронирований */
        .status-new {
            color: #856404;
            font-weight: 600;
            background: #fff3cd;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
        }

        .status-processing {
            color: #0c5460;
            font-weight: 600;
            background: #d1ecf1;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
        }

        .status-completed {
            color: #155724;
            font-weight: 600;
            background: #e3f5e8;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
        }

        .status-cancelled {
            color: #721c24;
            font-weight: 600;
            background: #f8d7da;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
        }

        /* Форма отзыва (доп. информация) */
        .review-form {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px dashed var(--gray-light);
        }

        .review-form form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .review-form input[type="text"] {
            flex: 1;
            padding: 12px 16px;
            border: 1.5px solid var(--gray-light);
            border-radius: 40px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
        }

        .review-form input[type="text"]:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.12);
        }

        .review-form button {
            padding: 10px 24px;
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .review-form button:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        /* Отзыв/комментарий */
        .review-text {
            margin-top: 14px;
            padding: 12px 16px;
            background: var(--gray-bg);
            border-radius: 20px;
            color: var(--gray-dark);
            font-weight: 400;
            font-size: 14px;
            border-left: 3px solid var(--green);
        }

        .review-text b {
            color: var(--gray-dark);
            font-weight: 600;
        }

        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 400;
        }

        .empty-state a {
            color: var(--green);
            text-decoration: none;
            font-weight: 600;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }

        /* Кнопка создания бронирования */
        .create-button {
            text-align: center;
            margin-top: 36px;
        }

        .create-button a {
            background: var(--green);
            color: var(--white);
            padding: 12px 32px;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 600;
            display: inline-block;
            transition: all 0.25s ease;
        }

        .create-button a:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(40, 167, 69, 0.3);
        }

        /* Иконки для типов помещений */
        .venue-icon {
            display: inline-block;
            margin-right: 6px;
        }

        /* Адаптивность */
        @media (max-width: 650px) {
            .container {
                padding: 24px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .request h2 {
                font-size: 18px;
            }
            
            .review-form form {
                flex-direction: column;
            }
            
            .review-form input[type="text"] {
                width: 100%;
            }
            
            .review-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
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
            
            // Определяем класс статуса
            $status_class = 'status-new';
            $status_text = htmlspecialchars($request['status']);
            if($status_text == 'Новая') $status_class = 'status-new';
            elseif($status_text == 'В обработке') $status_class = 'status-processing';
            elseif($status_text == 'Завершено') $status_class = 'status-completed';
            elseif($status_text == 'Отменено') $status_class = 'status-cancelled';
            
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
            
            // Если есть отзыв/доп. комментарий, показываем его
            if(!empty($request['review'])) {
                echo '<div class="review-text"><b>⭐ Отзыв о проведении:</b> ' . htmlspecialchars($request['review']) . '</div>';
            }
            
            // Если статус "Завершено" - показываем форму для отзыва о мероприятии
            if($request['status'] === 'Завершено') {
                echo '
                <div class="review-form">
                    <form action="" method="POST">
                        <input type="hidden" name="request_id" value="' . $request['id'] . '">
                        <input type="text" name="review" placeholder="✍️ Оставьте отзыв о качестве организации конференции..." value="' . htmlspecialchars($request['review'] ?? '') . '">
                        <button type="submit">⭐ Оставить отзыв</button>
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