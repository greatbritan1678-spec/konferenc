<?php
session_start();

// Подключаем базу данных СРАЗУ в начале файла
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
$success = false;
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    $form_data = compact('login', 'fullname', 'phone', 'email');
    
    // Валидация данных
    $errors = [];
    
    if (empty($login)) {
        $errors[] = 'Логин обязателен для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors[] = 'Логин должен содержать только латиницу и цифры, минимум 6 символов';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов';
    }
    
    if (empty($fullname)) {
        $errors[] = 'ФИО обязательно для заполнения';
    } elseif (strlen($fullname) < 5) {
        $errors[] = 'Введите полное ФИО';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors[] = 'Телефон должен быть в формате +7(XXX)XXX-XX-XX';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($errors)) {
        // Проверка на существование логина
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = true;
            $error_message = 'Пользователь с таким логином уже существует';
            $stmt->close();
        } else {
            $stmt->close();
            
            // Проверка на существование email
            $stmt2 = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            if ($result2->num_rows > 0) {
                $error = true;
                $error_message = 'Пользователь с таким email уже существует';
                $stmt2->close();
            } else {
                $stmt2->close();
                
                // БЕЗ ХЕШИРОВАНИЯ — сохраняем пароль как есть (открытым текстом)
                $stmt3 = $con->prepare("INSERT INTO users (login, password, fullname, phone, email) VALUES (?, ?, ?, ?, ?)");
                $stmt3->bind_param("sssss", $login, $password, $fullname, $phone, $email);
                
                if ($stmt3->execute()) {
                    $success = true;
                    header('refresh:2;url=login.php');
                } else {
                    $error = true;
                    $error_message = 'Ошибка при регистрации: ' . $con->error;
                }
                $stmt3->close();
            }
        }
    } else {
        $error = true;
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Конференции.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="page-register">
    <div class="container">
        <div class="logo">
            <h1>🎤 Конференции.РФ</h1>
            <p>Бронирование помещений для всероссийских конференций</p>
        </div>

        <div class="form-header">
            <h2>Регистрация организатора</h2>
            <p>Создайте аккаунт, чтобы бронировать аудитории, коворкинги и кинозалы</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                ✅ Регистрация успешно завершена!<br>
                <small>Перенаправление на страницу входа...</small>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="fullname">
                    <span>👤</span> ФИО
                </label>
                <input type="text" id="fullname" name="fullname" 
                       value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>"
                       placeholder="Иванов Иван Иванович" required>
                <span class="hint">Полное имя организатора или ответственного лица</span>
            </div>

            <div class="form-group">
                <label for="phone">
                    <span>📱</span> Телефон
                </label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                       placeholder="+7(XXX)XXX-XX-XX" 
                       pattern="\+7\(\d{3}\)\d{3}-\d{2}-\d{2}" required>
                <span class="hint">Формат: +7(XXX)XXX-XX-XX (для экстренной связи)</span>
            </div>

            <div class="form-group">
                <label for="email">
                    <span>📧</span> Email
                </label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                       placeholder="organizer@conference.ru" required>
                <span class="hint">На этот адрес придёт подтверждение бронирования</span>
            </div>

            <div class="form-group">
                <label for="login">
                    <span>🔑</span> Логин
                </label>
                <input type="text" id="login" name="login" 
                       value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>"
                       placeholder="conf_organizer" 
                       pattern="[a-zA-Z0-9]{6,}" required>
                <span class="hint">Латиница и цифры, минимум 6 символов</span>
            </div>

            <div class="form-group">
                <label for="password">
                    <span>🔒</span> Пароль
                </label>
                <input type="password" id="password" name="password" 
                       placeholder="Минимум 8 символов" minlength="8" required>
                <span class="hint" id="passwordHint">Пароль должен содержать минимум 8 символов</span>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <span>✅</span> Подтверждение пароля
                </label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Повторите пароль" required>
                <span class="hint" id="confirmHint"></span>
            </div>

            <button type="submit" class="btn-register" id="submitBtn">
                📝 Зарегистрироваться и бронировать
            </button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <p>Уже зарегистрированы? <a href="login.php" class="login-link">Войти в личный кабинет →</a></p>
            <a href="index.php" class="back-home">← На главную (выбор помещения)</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const confirmHint = document.getElementById('confirmHint');
        const passwordHint = document.getElementById('passwordHint');
        const submitBtn = document.getElementById('submitBtn');
        
        if (password) {
            password.addEventListener('input', function() {
                const value = this.value;
                if (value.length >= 8) {
                    passwordHint.innerHTML = '✅ Пароль принят';
                    passwordHint.style.color = '#28A745';
                } else {
                    passwordHint.innerHTML = '⚠️ Минимум 8 символов';
                    passwordHint.style.color = '#dc3545';
                }
                
                if (confirmPassword.value) {
                    checkPasswordsMatch();
                }
            });
        }
        
        function checkPasswordsMatch() {
            if (password.value === confirmPassword.value && password.value.length >= 8) {
                confirmHint.innerHTML = '✅ Пароли совпадают';
                confirmHint.style.color = '#28A745';
                return true;
            } else if (confirmPassword.value.length > 0) {
                confirmHint.innerHTML = '❌ Пароли не совпадают';
                confirmHint.style.color = '#dc3545';
                return false;
            }
            return false;
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkPasswordsMatch);
        }
        
        const phone = document.getElementById('phone');
        if (phone) {
            phone.addEventListener('input', function(e) {
                let value = this.value;
                if (value.length === 1 && value !== '+') {
                    this.value = '+' + value;
                }
            });
        }
        
        if (form) {
            form.addEventListener('submit', function(e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    showInlineError('Пароли не совпадают');
                    confirmPassword.style.borderColor = '#dc3545';
                    return false;
                }
                
                if (password.value.length < 8) {
                    e.preventDefault();
                    showInlineError('Пароль должен быть не менее 8 символов');
                    password.style.borderColor = '#dc3545';
                    return false;
                }
                
                const phonePattern = /^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
                if (!phonePattern.test(phone.value)) {
                    e.preventDefault();
                    showInlineError('Укажите телефон в формате +7(XXX)XXX-XX-XX');
                    phone.style.borderColor = '#dc3545';
                    return false;
                }
                
                const loginPattern = /^[a-zA-Z0-9]{6,}$/;
                const login = document.getElementById('login');
                if (!loginPattern.test(login.value)) {
                    e.preventDefault();
                    showInlineError('Логин: только латиница и цифры, минимум 6 символов');
                    login.style.borderColor = '#dc3545';
                    return false;
                }
                
                submitBtn.innerHTML = '⏳ Обработка...';
                submitBtn.disabled = true;
            });
        }
        
        function showInlineError(message) {
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            const formHeader = document.querySelector('.form-header');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `⚠️ ${message}`;
            formHeader.insertAdjacentElement('afterend', errorDiv);
            
            setTimeout(() => {
                errorDiv.style.opacity = '0';
                setTimeout(() => errorDiv.remove(), 300);
            }, 3000);
        }
        
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#CED4DA';
            });
        });
        
        function createDecor() {
            for (let i = 0; i < 8; i++) {
                const decor = document.createElement('div');
                decor.className = 'bg-decor';
                const size = Math.random() * 80 + 40;
                decor.style.width = size + 'px';
                decor.style.height = size + 'px';
                decor.style.left = Math.random() * 100 + '%';
                decor.style.bottom = '-' + size + 'px';
                decor.style.animationDuration = Math.random() * 15 + 10 + 's';
                decor.style.animationDelay = Math.random() * 5 + 's';
                document.body.appendChild(decor);
            }
        }
        
        createDecor();
    </script>
</body>
</html>