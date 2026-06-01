<?php
session_start();
if (!isset($_SESSION['user_id'])) die('Чтобы забронировать помещение для конференции, необходимо войти в аккаунт.');

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = $_POST['review'];        // доп. информация
    $date = $_POST['date'];            // дата и время
    $venue = $_POST['venue'];          // тип помещения
    $payment = $_POST['payment'];      // способ оплаты
    $status = 'Новая';                 // статус заявки
    
    include('db.php');
    
    $user_id = (int)$_SESSION['user_id'];
    $review = $con->real_escape_string($review);
    $venue = $con->real_escape_string($venue);
    $payment = $con->real_escape_string($payment);
    
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) 
                          VALUES ('$review', '$date', '$venue', '$payment', '$user_id', '$status')");
    
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ===== ЦВЕТОВАЯ СХЕМА (согласно ТЗ) ===== */
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
            max-width: 580px;
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

        /* Кнопки навигации */
        .nav-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-nav {
            display: inline-block;
            padding: 10px 24px;
            background: var(--green);
            color: var(--white);
            text-decoration: none;
            border-radius: 40px;
            text-align: center;
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .btn-nav:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-nav:active {
            transform: translateY(0);
        }

        h1 {
            text-align: center;
            margin-bottom: 24px;
            color: var(--gray-dark);
            font-size: 28px;
            font-weight: 700;      /* Bold */
            letter-spacing: -0.2px;
        }

        /* Стили формы */
        form {
            animation: formFadeIn 0.4s ease-out 0.1s both;
        }

        @keyframes formFadeIn {
            from {
                opacity: 0;
                transform: scale(0.98);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;      /* Medium */
            color: var(--gray-dark);
            font-size: 14px;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 20px;
            border: 1.5px solid var(--gray-light);
            border-radius: 16px;
            box-sizing: border-box;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
            background: var(--white);
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.12);
        }

        form input:hover,
        form select:hover,
        form textarea:hover {
            border-color: var(--green);
        }

        form textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Кнопка отправки */
        form button {
            width: 100%;
            padding: 14px;
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            font-weight: 600;      /* Semi-Bold */
            transition: all 0.25s ease;
            margin-top: 8px;
        }

        form button:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(40, 167, 69, 0.3);
        }

        form button:active {
            transform: translateY(0);
        }

        /* Сообщения об успехе/ошибке */
        .success-message {
            background: #e3f5e8;
            color: #155724;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 24px;
            text-align: center;
            border-left: 4px solid var(--green);
            font-size: 15px;
            font-weight: 400;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 14px 18px;
            border-radius: 20px;
            margin-bottom: 24px;
            text-align: center;
            border-left: 4px solid #dc3545;
            font-size: 14px;
            font-weight: 400;
        }

        .success-message a,
        .error-message a {
            color: inherit;
            font-weight: 600;
            text-decoration: underline;
            transition: color 0.2s ease;
        }

        .success-message a:hover,
        .error-message a:hover {
            color: var(--green);
        }

        /* Эффект загрузки кнопки */
        form button.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        form button.loading::after {
            content: '';
            display: inline-block;
            width: 18px;
            height: 18px;
            margin-left: 10px;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Мелкий текст (подсказки) */
        .hint-text {
            font-size: 12px;
            font-weight: 300;      /* Light */
            color: var(--text-muted);
            margin-top: -12px;
            margin-bottom: 16px;
            display: block;
        }

        /* Адаптивность */
        @media (max-width: 600px) {
            .container {
                padding: 28px 20px;
                margin: 0 15px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .nav-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            form input,
            form select,
            form textarea {
                padding: 10px 14px;
                font-size: 14px;
            }
            
            form button {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
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

            <label for="review">📝 Дополнительная информация</label>
            <textarea id="review" name="review" placeholder="Укажите количество участников, необходимость дополнительного оборудования (микрофоны, флипчарты, кейтеринг) и особые пожелания..."></textarea>
             
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