<?php
session_start();
if (!isset($_SESSION['user_id'])) die('Чтобы забронировать помещение для конференции, необходимо войти в аккаунт.');

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Доп. информация при бронировании — пишется в request.comment.
    // Отзыв о проведённом мероприятии хранится отдельно в request.review
    // и заполняется в history.php после статуса «Мероприятие завершено».
    $comment = $_POST['comment'] ?? '';
    $date = $_POST['date'];            // дата и время
    $venue = $_POST['venue'];          // тип помещения
    $payment = $_POST['payment'];      // способ оплаты
    $status = 'Новая';                 // статус заявки

    include('db.php');

    $user_id = (int)$_SESSION['user_id'];
    $comment = $con->real_escape_string($comment);
    $venue   = $con->real_escape_string($venue);
    $payment = $con->real_escape_string($payment);
    $date    = $con->real_escape_string($date);

    $query = $con->query("INSERT INTO request (comment, date, curses, payment, user_id, status)
                          VALUES ('$comment', '$date', '$venue', '$payment', '$user_id', '$status')");

    if (!$query) {
        $error = true;
        $error_msg = 'Ошибка: ' . $con->error;
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование помещения — Конференции.РФ</title>
    <!-- Roboto: современный гротеск (ясность, читаемость) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page-create">
    <div class="container">
        <!-- Навигационные кнопки -->
        <div class="nav-buttons">
            <a href="index.php" class="btn-nav">🏠 Главная</a>
            <a href="history.php" class="btn-nav">📋 Мои бронирования</a>
        </div>
        
        <h1>🎤 Бронирование помещения<br>для конференции</h1>

        <?php if ($success): ?>
            <div class="success-message">
                ✅ Заявка на бронирование успешно отправлена!<br><br>
                <a href="history.php">📋 Перейти к моим бронированиям →</a>
                <br><br>
                Администратор свяжется с вами для подтверждения в ближайшее время.
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                ❌ Ошибка при бронировании: <?php echo htmlspecialchars($error_msg); ?><br>
                <a href="javascript:history.back()">◀ Попробовать снова</a>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="requestForm">
            
            <label for="venue">🏛️ Тип помещения</label>
            <select id="venue" name="venue" required>
                <option value="Аудитория">🎓 Аудитория (до 150 мест, проектор, доска)</option>
                <option value="Коворкинг">💼 Коворкинг (гибкое пространство, Wi-Fi, зоны воркшопов)</option>
                <option value="Кинозал">🎬 Кинозал (панорамный экран, звук, до 80 мест)</option>
            </select>

            <label for="date">📅 Дата и время начала</label>
            <input id="date" type="datetime-local" name="date" required>
            <span class="hint-text">Укажите желаемые дату и время проведения мероприятия</span>

            <label for="payment">💳 Способ оплаты</label>
            <select id="payment" name="payment" required>
                <option value="наличные">💵 Наличные в кассу</option>
                <option value="перевод">🏦 Безналичный перевод по счёту</option>
                <option value="карта">💳 Онлайн банковской картой</option>
            </select>

            <label for="comment">📝 Дополнительная информация</label>
            <textarea id="comment" name="comment" placeholder="Укажите количество участников, необходимость дополнительного оборудования (микрофоны, флипчарты, кейтеринг) и особые пожелания..."></textarea>
             
            <button type="submit" id="submitBtn">📋 Забронировать помещение</button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        // Анимация загрузки при отправке формы
        const form = document.getElementById('requestForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                submitBtn.classList.add('loading');
                submitBtn.textContent = 'Отправка заявки';
            });
        }

        // Визуальный эффект при фокусе
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transition = 'all 0.2s ease';
            });
        });

        // Минимальная дата — сегодняшняя (для datetime-local)
        const dateInput = document.getElementById('date');
        if (dateInput) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            dateInput.min = minDateTime;
        }
    </script>
</body>
</html>