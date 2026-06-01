<?php
session_start();

// Подключаем базу данных в начале
include('db.php');

// Если пользователь уже авторизован, перенаправляем
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = true;
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        // Используем подготовленные выражения для защиты от SQL инъекций
        $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = true;
            $error_message = 'Неверный логин или пароль';
        } else {
            $user = $result->fetch_assoc();
            
            // ПРОВЕРКА ПАРОЛЯ: поддерживает оба формата (хеш и открытый текст)
            $password_valid = false;
            
            // 1. Проверка на хешированный пароль (новый формат)
            if (password_verify($password, $user['password'])) {
                $password_valid = true;
            }
            // 2. Проверка на открытый текст (старый формат, для совместимости)
            elseif ($password === $user['password']) {
                $password_valid = true;
                // Если пароль в открытом виде, перехешируем его для безопасности
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed, $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            if (!$password_valid) {
                $error = true;
                $error_message = 'Неверный логин или пароль';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_fullname'] = $user['fullname'];
                
                // Проверка на администратора
                if ($user['login'] == 'Admin26') {
                    $_SESSION['admin'] = true;
                    header('Location: admin.php');
                } else {
                    header('Location: create.php');
                }
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Конференции.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page-login">
    <div class="wave-bg"></div>
    
    <div class="container">
        <div class="logo">
            <h1>🎤 Конференции.РФ</h1>
            <p>Бронирование помещений для всероссийских конференций</p>
        </div>

        <div class="form-header">
            <h2>Добро пожаловать!</h2>
            <p>Войдите, чтобы забронировать аудиторию, коворкинг или кинозал</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <span>⚠️</span>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="login">
                    <span>👤</span> Логин
                </label>
                <input type="text" id="login" name="login" 
                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                       placeholder="conf_organizer" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">
                    <span>🔒</span> Пароль
                </label>
                <input type="password" id="password" name="password" 
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                Войти в личный кабинет
            </button>
        </form>

        <div class="form-footer">
            <p>Нет аккаунта? <a href="register.php" class="register-link">Зарегистрироваться →</a></p>
            <a href="index.php" class="back-home">← Вернуться на главную</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const login = document.getElementById('login').value.trim();
                const password = document.getElementById('password').value;
                
                if (!login || !password) {
                    e.preventDefault();
                    showError('Пожалуйста, заполните все поля');
                    return;
                }
                
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '⏳ Вход...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }, 5000);
            });
        }
        
        function showError(message) {
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<span>⚠️</span> ${message}`;
            
            const formHeader = document.querySelector('.form-header');
            formHeader.insertAdjacentElement('afterend', errorDiv);
            
            const container = document.querySelector('.container');
            container.style.animation = 'shakeError 0.4s ease-in-out';
            setTimeout(() => {
                container.style.animation = '';
            }, 400);
        }
        
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateX(3px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateX(0)';
            });
        });
        
        const savedLogin = localStorage.getItem('savedLoginConference');
        if (savedLogin && !document.getElementById('login').value) {
            document.getElementById('login').value = savedLogin;
        }
        
        form.addEventListener('submit', function() {
            const login = document.getElementById('login').value;
            localStorage.setItem('savedLoginConference', login);
        });
    </script>
</body>
</html>