-- Миграция: разделение колонки request.review на comment + review.
-- До: review хранила И комментарий, оставленный при бронировании, И отзыв
-- после мероприятия (форма отзыва на самом деле никогда не показывалась
-- из-за бага со статусом, поэтому все текущие значения review — это
-- комментарии при бронировании).
-- После: comment — дополнительная информация при создании заявки,
--        review  — отзыв клиента, оставляется только после статуса
--                  «Мероприятие завершено».
--
-- Применение: mysql demoexam < migrations/2026_06_01_split_review.sql

ALTER TABLE `request`
    ADD COLUMN `comment` TEXT
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        AFTER `payment`;

UPDATE `request`
   SET `comment` = `review`,
       `review`  = NULL
 WHERE `comment` IS NULL
   AND `review`  IS NOT NULL;
