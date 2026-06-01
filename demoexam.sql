-- ----------------------------------------------------------------------------
-- Конференции.РФ — начальная схема базы данных
-- ----------------------------------------------------------------------------
-- Использование (пересоздание БД с нуля):
--   mysql -uroot -p -e "DROP DATABASE IF EXISTS demoexam; CREATE DATABASE demoexam
--                       CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
--   mysql -uroot -p demoexam < demoexam.sql
--
-- Что внутри:
--   * Таблица `users`    — пользователи + флаг `is_admin`.
--   * Таблица `request`  — заявки на бронирование. Колонка `comment`
--                          хранит доп. информацию при бронировании,
--                          колонка `review` — отзыв клиента (пишется
--                          только при статусе «Мероприятие завершено»).
--   * Один пользователь: Admin26 / Demo20 (пароль захеширован bcrypt).
--   * Пустая таблица `request` — добавляй данные через регистрацию и
--     форму бронирования.
-- ----------------------------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

START TRANSACTION;

-- ----------------------------------------------------------------------------
-- Таблица `users`
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `request`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id`         int          NOT NULL AUTO_INCREMENT,
  `fullname`   text         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone`      varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email`      varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `login`      varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password`   varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin`   tinyint(1)   NOT NULL DEFAULT 0,
  `created_at` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Единственный изначальный пользователь — администратор.
-- Логин: Admin26, пароль: Demo20 (bcrypt-хэш сгенерирован password_hash).
INSERT INTO `users` (`id`, `fullname`, `phone`, `email`, `login`, `password`, `is_admin`, `created_at`) VALUES
(1, 'Администратор', '+7(000)000-00-00', 'admin@konferenc.local', 'Admin26',
 '$2y$12$FoElWYpny0S0/nnT5BITTOTubAIy1ioPYNkluIY6.RwRoAsvnZYCG', 1, CURRENT_TIMESTAMP);

-- ----------------------------------------------------------------------------
-- Таблица `request`
-- ----------------------------------------------------------------------------
-- `comment` — доп. информация при бронировании (заполняется в create.php).
-- `review`  — отзыв клиента (пишется в history.php только после того, как
--             администратор поставил статус «Мероприятие завершено»).
-- ----------------------------------------------------------------------------
CREATE TABLE `request` (
  `id`       int          NOT NULL AUTO_INCREMENT,
  `user_id`  int          NOT NULL,
  `date`     datetime     NOT NULL,
  `status`   varchar(50)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Новая',
  `curses`   varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment`  text         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment`  text         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `review`   text         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `request_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
