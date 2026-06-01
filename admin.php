<?php
include('db.php');
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Допустимые статусы для бронирований помещений (согласно ТЗ)
$valid_statuses = ['Новая', 'Мероприятие назначено', 'Мероприятие завершено'];
$status_updated = false;

// Обработка изменения статуса заявки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';

    // Валидация статуса
    if (!in_array($status, $valid_statuses, true)) {
        die('Недопустимый статус заявки');
    }

    // Использование подготовленных выражений
    $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $request_id);

    if (!$stmt->execute()) {
        die('Ошибка обновления: ' . $con->error);
    } else {
        $status_updated = true;
    }
}

// Получение заявок с пагинацией (10 заявок на страницу)
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$query = $con->query("
    SELECT request.*, users.login, users.fullname,
           COUNT(*) OVER() as total_count
    FROM request
    INNER JOIN users ON request.user_id = users.id
    ORDER BY request.date DESC
    LIMIT $limit OFFSET $offset
");

if (!$query) die('Ошибка запроса: ' . $con->error);

// Подсчёт статистики одним запросом
$stats_query = $con->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_requests,
        SUM(CASE WHEN status = 'Мероприятие назначено' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'Мероприятие завершено' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Конференции.РФ</title>
    <!-- Roboto: современный гротеск -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page-admin">
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chalkboard-user"></i> Панель администратора</h1>
            <p class="subtitle">Управление бронированием помещений для всероссийских конференций</p>
        </div>

        <div class="nav-bar">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Главная
            </a>
            <a href="?logout=1" class="btn btn-outline" onclick="return confirm('Выйти из аккаунта?')">
                <i class="fas fa-sign-out-alt"></i> Выход
            </a>
        </div>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" style="color: var(--green);"><?= $stats['total'] ?></div>
                <div class="stat-label">Всего бронирований</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #856404;"><?= $stats['new_requests'] ?></div>
                <div class="stat-label">🆕 Новые</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #0c5460;"><?= $stats['assigned'] ?></div>
                <div class="stat-label">📅 Мероприятие назначено</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #155724;"><?= $stats['completed'] ?></div>
                <div class="stat-label">✅ Мероприятие завершено</div>
            </div>
        </div>

        <!-- Список заявок -->
        <div class="requests-container">
            <?php
            if ($query->num_rows === 0) {
            ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-plus"></i>
                    <h3>Бронирований пока нет</h3>
                    <p>Когда пользователи забронируют помещения, они появятся здесь</p>
                </div>
            <?php } else {
                while ($request = $query->fetch_assoc()) {
                    // Определяем класс для статуса
                    $status_class = match($request['status']) {
                        'Новая' => 'status-new',
                        'Мероприятие назначено' => 'status-assigned',
                        'Мероприятие завершено' => 'status-completed',
                        default => 'status-new'
                    };
                    
                    // Иконка для типа помещения
                    $venue = $request['curses'] ?? '—';
                    $venue_icon = '';
                    if(strpos($venue, 'Аудитория') !== false) $venue_icon = '🎓';
                    elseif(strpos($venue, 'Коворкинг') !== false) $venue_icon = '💼';
                    elseif(strpos($venue, 'Кинозал') !== false) $venue_icon = '🎬';
                    else $venue_icon = '🏛️';
            ?>
                <div class="request-item">
                    <div class="request-header">
                        <div class="user-info">
                            <h3><i class="fas fa-user"></i> <?= htmlspecialchars($request['login']) ?></h3>
                            <p><?= htmlspecialchars($request['fullname']) ?></p>
                        </div>
                        <div>
                            <span class="request-id">Бронь №<?= htmlspecialchars($request['id']) ?></span>
                            <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span>
                        </div>
                    </div>

                    <div class="request-details">
                        <div class="detail-item">
                            <div class="detail-label"><i class="far fa-calendar-alt"></i> Дата и время</div>
                            <div class="detail-value"><?= htmlspecialchars($request['date']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-building"></i> Тип помещения</div>
                            <div class="detail-value"><?= $venue_icon ?> <?= htmlspecialchars($venue) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-credit-card"></i> Способ оплаты</div>
                            <div class="detail-value"><?= htmlspecialchars($request['payment'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-comment"></i> Доп. информация</div>
                            <div class="detail-value"><?= htmlspecialchars($request['review'] ?? '—') ?></div>
                        </div>
                    </div>

                    <!-- Форма изменения статуса -->
                    <div class="status-form">
                        <form method="POST" class="status-update-form">
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">

                            <div class="form-group">
                                <label class="form-label" for="status_<?= $request['id'] ?>">
                                    <i class="fas fa-tag"></i> Изменить статус бронирования:
                                </label>
                                <select name="status" id="status_<?= $request['id'] ?>" class="form-select">
                                    <option value="Новая" <?= $request['status'] == 'Новая' ? 'selected' : '' ?>>
                                        🆕 Новая
                                    </option>
                                    <option value="Мероприятие назначено" <?= $request['status'] == 'Мероприятие назначено' ? 'selected' : '' ?>>
                                        📅 Мероприятие назначено
                                    </option>
                                    <option value="Мероприятие завершено" <?= $request['status'] == 'Мероприятие завершено' ? 'selected' : '' ?>>
                                        ✅ Мероприятие завершено
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            <?php
                }
            }
            ?>
        </div>

        <!-- Пагинация -->
        <?php if ($stats['total'] > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($stats['total'] / $limit);
                for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Уведомление об успехе -->
    <?php if ($status_updated): ?>
        <div class="notification">
            <i class="fas fa-check-circle"></i> Статус бронирования успешно обновлён!
        </div>
    <?php endif; ?>

    <script>
        // Обработка отправки форм статуса
        document.querySelectorAll('.status-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.btn-save');
                const originalText = submitBtn.innerHTML;

                // Блокировка кнопки на время обработки
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';

                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 2000);
            });
        });

        // Плавная прокрутка к уведомлениям
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            setTimeout(() => {
                if (notification) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }
    </script>
</body>
</html>