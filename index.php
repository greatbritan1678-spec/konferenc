<?php
session_start();

// Выход из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Проверяем, установлен ли ключ admin в сессии
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Конференции.РФ — бронирование помещений для конференций</title>
  <!-- Roboto: современный гротеск, отличная читаемость -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
<link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body class="page-home">

<header class="header">
  <div class="nav">
    <a href="index.php" class="logo">🎤 Конференции.РФ</a>
    <div class="nav-buttons">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="btn-login">Войти</a>
        <a href="register.php" class="btn-register">Регистрация</a>
      <?php elseif ($is_admin): ?>
        <a href="admin.php" class="btn-admin">Панель администратора</a>
        <a href="?logout=1" class="btn-exit">Выход</a>
      <?php elseif (isset($_SESSION['user_id'])): ?>
        <a href="history.php" class="btn-lk">Мои бронирования</a>
        <a href="create.php" class="btn-create">Новая заявка</a>
        <a href="?logout=1" class="btn-exit">Выход</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Слайдер с помещениями для конференций -->
<div class="slideshow-container">
  <div class="mySlides fade">
    <img src="https://i.pinimg.com/originals/d2/59/99/d2599989fc2679db0e7a386aba47452c.jpg" alt="Современная аудитория для конференций">
    <div class="slide-text">🎓 Аудитория на 150 мест — полное оснащение</div>
  </div>

  <div class="mySlides fade">
    <img src="https://avatars.mds.yandex.net/i?id=7f125da46537b85dcd60f22e55e795b6_l-9182206-images-thumbs&n=13" alt="Коворкинг-центр">
    <div class="slide-text">💼 Коворкинг: гибкое пространство для воркшопов</div>
  </div>

  <div class="mySlides fade">
    <img src="https://avatars.mds.yandex.net/i?id=9635a76000e41cd8b3d381529e379dae_l-4410363-images-thumbs&n=13" alt="Кинозал с проектором">
    <div class="slide-text">🎬 Кинозал премиум-класса с акустикой</div>
  </div>

  <div class="mySlides fade">
    <img src="https://www.komandirovka.ru/upload/save_file38/64c/64c9bca9ef1bbe1ba500da063d8ba4d2.webp" alt="Зал для пленарных заседаний">
    <div class="slide-text">🏛️ Пленарный зал — идеально для всероссийских конференций</div>
  </div>

  <a class="prev" onclick="plusSlides(-1)">❮</a>
  <a class="next" onclick="plusSlides(1)">❯</a>
</div>

<div class="dot-container">
  <span class="dot" onclick="currentSlide(1)"></span>
  <span class="dot" onclick="currentSlide(2)"></span>
  <span class="dot" onclick="currentSlide(3)"></span>
  <span class="dot" onclick="currentSlide(4)"></span>
</div>

<!-- Основной блок: типы помещений для бронирования -->
<section class="features-section">
  <div class="features-title">
    <h1>Выберите площадку для вашей конференции</h1>
    <p style="font-size: 18px; margin-top: 12px; color: #5a6268;">Актовые залы, современные коворкинги и кинозалы — бронируйте онлайн</p>
  </div>
  
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">🏛️</div>
      <h3>Аудитория / Конференц-зал</h3>
      <p>Вместимость до 200 человек. Проектор, акустика, трибуна, флипчарты. Идеально для пленарных заседаний и научных секций.</p>
      <a href="create.php" class="btn-demo">Забронировать</a>
    </div>
    
    <div class="feature-card">
      <div class="feature-icon">💻</div>
      <h3>Коворкинг</h3>
      <p>Гибкое пространство для воркшопов, круглых столов и нетворкинга. Wi-Fi, зоны отдыха, кухня. Модульная мебель.</p>
      <a href="create.php" class="btn-demo">Забронировать</a>
    </div>
    
    <div class="feature-card">
      <div class="feature-icon">🎥</div>
      <h3>Кинозал</h3>
      <p>Панорамный экран, многоканальный звук, удобные кресла. Для презентаций, кинопоказов и торжественных церемоний.</p>
      <a href="create.php" class="btn-demo">Забронировать</a>
    </div>
  </div>
</section>

<!-- Информационная панель -->
<div class="info-banner">
  <div>
    <p><strong>🎯 Система бронирования помещений</strong><br>Организуйте всероссийские конференции без хлопот: выбор зала, онлайн-заявка, подтверждение администратором.</p>
  </div>
  <div class="badge">Удобно • Быстро • Прозрачно</div>
</div>

<footer class="footer">
  © 2025 Конференции.РФ — информационная система бронирования помещений для проведения всероссийских конференций: аудитории, коворкинги, кинозалы.
</footer>

<script>
// Слайдер
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");

  if (n > slides.length) { slideIndex = 1; }
  if (n < 1) { slideIndex = slides.length; }

  for (let i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (let i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }

  if (slides[slideIndex-1]) slides[slideIndex-1].style.display = "block";
  if (dots[slideIndex-1]) dots[slideIndex-1].className += " active";
}

let slideInterval = setInterval(() => plusSlides(1), 4000);

const container = document.querySelector('.slideshow-container');
if (container) {
  container.addEventListener('mouseenter', () => clearInterval(slideInterval));
  container.addEventListener('mouseleave', () => {
    slideInterval = setInterval(() => plusSlides(1), 4000);
  });
}

document.addEventListener('DOMContentLoaded', function() {
  showSlides(slideIndex);
});
</script>
</body>
</html>