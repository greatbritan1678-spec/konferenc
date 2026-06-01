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

// === Фильтры и сортировка ===
// Статус: кликабельные карточки в блоке статистики → ?status=«Новая» / …
// Тип помещения и пользователь: выбор в select-ах
// Сортировка: по дате / типу / статусу / пользователю (по умолчанию — дата ↓)
$valid_venues = ['Аудитория', 'Коворкинг', 'Кинозал'];
$sort_columns = [
    'date'   => 'request.date',
    'venue'  => 'request.curses',
    'status' => 'request.status',
    'user'   => 'users.login',
    'id'     => 'request.id',
];

$f_status = $_GET['status'] ?? '';
if (!in_array($f_status, $valid_statuses, true)) $f_status = '';

$f_venue = $_GET['venue'] ?? '';
if (!in_array($f_venue, $valid_venues, true)) $f_venue = '';

$f_user = (int)($_GET['user_id'] ?? 0);

$sort = $_GET['sort'] ?? 'date';
if (!isset($sort_columns[$sort])) $sort = 'date';
$dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

// Список пользователей для фильтра (без админов, только те, у кого есть брони)
$users_query = $con->query("
    SELECT DISTINCT users.id, users.login, users.fullname
    FROM users
    INNER JOIN request ON request.user_id = users.id
    WHERE users.is_admin = 0
    ORDER BY users.login
");
$users_list = $users_query ? $users_query->fetch_all(MYSQLI_ASSOC) : [];

// Сборка WHERE с prepared-биндами
$where = [];
$bind_types = '';
$bind_vals  = [];
if ($f_status !== '') { $where[] = 'request.status = ?'; $bind_types .= 's'; $bind_vals[] = $f_status; }
if ($f_venue  !== '') { $where[] = 'request.curses = ?'; $bind_types .= 's'; $bind_vals[] = $f_venue;  }
if ($f_user   >   0)  { $where[] = 'request.user_id = ?'; $bind_types .= 'i'; $bind_vals[] = $f_user;  }
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Подсчёт отфильтрованных заявок (для пагинации)
$count_sql = "SELECT COUNT(*) as cnt FROM request INNER JOIN users ON request.user_id = users.id $where_sql";
$count_stmt = $con->prepare($count_sql);
if ($bind_types !== '') $count_stmt->bind_param($bind_types, ...$bind_vals);
$count_stmt->execute();
$filtered_total = (int)$count_stmt->get_result()->fetch_assoc()['cnt'];

// Пагинация
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Основной SELECT (сортировка из вайт-листа — безопасно подставляется строкой)
$sort_sql = $sort_columns[$sort] . ' ' . $dir;
$list_sql = "
    SELECT request.*, users.login, users.fullname
    FROM request
    INNER JOIN users ON request.user_id = users.id
    $where_sql
    ORDER BY $sort_sql, request.id DESC
    LIMIT ? OFFSET ?
";
$list_stmt = $con->prepare($list_sql);
$list_types = $bind_types . 'ii';
$list_vals  = array_merge($bind_vals, [$limit, $offset]);
$list_stmt->bind_param($list_types, ...$list_vals);
$list_stmt->execute();
$query = $list_stmt->get_result();
if (!$query) die('Ошибка запроса: ' . $con->error);

// Подсчёт статистики (по всем заявкам, независимо от фильтра)
$stats_query = $con->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_requests,
        SUM(CASE WHEN status = 'Мероприятие назначено' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'Мероприятие завершено' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();

// Помощник: сборка URL с сохранением текущих фильтров
function admin_url(array $overrides = []): string {
    $params = array_merge($_GET, $overrides);
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) unset($params[$k]);
    }
    // При смене фильтра/сортировки сбрасываем page; при пагинации page приходит в $overrides.
    if (!array_key_exists('page', $overrides)) unset($params['page']);
    return '?' . http_build_query($params);
}

// Помощник: ссылка на заголовок-сортировки (переключает направление, если уже выбрано)
function sort_link(string $col): string {
    global $sort, $dir;
    $next_dir = ($sort === $col && $dir === 'ASC') ? 'desc' : 'asc';
    return admin_url(['sort' => $col, 'dir' => $next_dir]);
}
function sort_caret(string $col): string {
    global $sort, $dir;
    if ($sort !== $col) return '<i class="fas fa-sort" style="opacity:.35"></i>';
    return $dir === 'ASC' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}
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

        <!-- Статистика — карточки кликабельны и работают как фильтры по статусу -->
        <div class="stats-grid">
            <a href="<?= admin_url(['status' => '']) ?>" class="stat-card<?= $f_status === '' ? ' active' : '' ?>">
                <div class="stat-number" style="color: var(--green);"><?= $stats['total'] ?></div>
                <div class="stat-label">Всего бронирований</div>
            </a>
            <a href="<?= admin_url(['status' => 'Новая']) ?>" class="stat-card<?= $f_status === 'Новая' ? ' active' : '' ?>">
                <div class="stat-number" style="color: #856404;"><?= $stats['new_requests'] ?></div>
                <div class="stat-label">🆕 Новые</div>
            </a>
            <a href="<?= admin_url(['status' => 'Мероприятие назначено']) ?>" class="stat-card<?= $f_status === 'Мероприятие назначено' ? ' active' : '' ?>">
                <div class="stat-number" style="color: #0c5460;"><?= $stats['assigned'] ?></div>
                <div class="stat-label">📅 Мероприятие назначено</div>
            </a>
            <a href="<?= admin_url(['status' => 'Мероприятие завершено']) ?>" class="stat-card<?= $f_status === 'Мероприятие завершено' ? ' active' : '' ?>">
                <div class="stat-number" style="color: #155724;"><?= $stats['completed'] ?></div>
                <div class="stat-label">✅ Мероприятие завершено</div>
            </a>
        </div>

        <!-- Дополнительные фильтры: тип помещения, пользователь + сброс -->
        <form method="GET" class="filters-bar">
            <?php if ($f_status !== ''): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($f_status) ?>">
            <?php endif; ?>
            <div class="filter-group">
                <label for="f-venue"><i class="fas fa-building"></i> Тип помещения</label>
                <select id="f-venue" name="venue" onchange="this.form.submit()">
                    <option value="">Все типы</option>
                    <?php foreach ($valid_venues as $v): ?>
                        <option value="<?= htmlspecialchars($v) ?>" <?= $f_venue === $v ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="f-user"><i class="fas fa-user"></i> Пользователь</label>
                <select id="f-user" name="user_id" onchange="this.form.submit()">
                    <option value="0">Все пользователи</option>
                    <?php foreach ($users_list as $u): ?>
                        <option value="<?= (int)$u['id'] ?>" <?= $f_user === (int)$u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['login']) ?> — <?= htmlspecialchars($u['fullname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($f_status !== '' || $f_venue !== '' || $f_user > 0): ?>
                <a href="admin.php" class="btn btn-outline filter-reset">
                    <i class="fas fa-xmark"></i> Сбросить
                </a>
            <?php endif; ?>
            <div class="filter-summary">
                Показано: <b><?= (int)$filtered_total ?></b> / <?= (int)$stats['total'] ?>
            </div>
        </form>

        <!-- Сортируемые колонки -->
        <div class="sort-bar">
            <span class="sort-bar-label"><i class="fas fa-arrow-down-wide-short"></i> Сортировка:</span>
            <a href="<?= sort_link('date')   ?>" class="sort-btn<?= $sort === 'date'   ? ' active' : '' ?>">Дата <?= sort_caret('date')   ?></a>
            <a href="<?= sort_link('venue')  ?>" class="sort-btn<?= $sort === 'venue'  ? ' active' : '' ?>">Тип <?= sort_caret('venue')  ?></a>
            <a href="<?= sort_link('status') ?>" class="sort-btn<?= $sort === 'status' ? ' active' : '' ?>">Статус <?= sort_caret('status') ?></a>
            <a href="<?= sort_link('user')   ?>" class="sort-btn<?= $sort === 'user'   ? ' active' : '' ?>">Пользователь <?= sort_caret('user')   ?></a>
            <a href="<?= sort_link('id')     ?>" class="sort-btn<?= $sort === 'id'     ? ' active' : '' ?>">№ <?= sort_caret('id')     ?></a>
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
                            <div class="detail-label"><i class="fas fa-comment"></i> Доп. информация от клиента</div>
                            <div class="detail-value"><?= !empty($request['comment']) ? htmlspecialchars($request['comment']) : '—' ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-star"></i> Отзыв клиента</div>
                            <div class="detail-value"><?= !empty($request['review']) ? htmlspecialchars($request['review']) : '— (ещё не оставлен)' ?></div>
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

        <!-- Пагинация (по отфильтрованным результатам) -->
        <?php if ($filtered_total > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = (int)ceil($filtered_total / $limit);
                for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="<?= admin_url(['page' => $i]) ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
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