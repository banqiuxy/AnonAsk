-- ============================================================
-- AnonAsk v3 数据库建表脚本
-- 「向我提问」模式：每个用户一个链接，前端完全匿名
-- ============================================================

CREATE DATABASE IF NOT EXISTS `banqiuxy`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `banqiuxy`;

DROP TABLE IF EXISTS `rate_limits`;
DROP TABLE IF EXISTS `answers`;
DROP TABLE IF EXISTS `questions`;
DROP TABLE IF EXISTS `users`;

-- ============================================================
-- 1. 用户表
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `uid`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contact_type`  ENUM('phone','qq','wechat') NOT NULL,
    `contact_value` VARCHAR(100)   NOT NULL,
    `password_hash` VARCHAR(255)   NOT NULL,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`uid`),
    UNIQUE KEY `uk_contact` (`contact_type`, `contact_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=202400000001
  COMMENT='用户表';

-- ============================================================
-- 2. 问题表 — A 向 B 提问
-- ============================================================
CREATE TABLE IF NOT EXISTS `questions` (
    `id`            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `target_uid`    BIGINT UNSIGNED NOT NULL COMMENT '问题发给谁（链接主人）',
    `author_uid`    BIGINT UNSIGNED NOT NULL COMMENT '谁问的（前端不展示）',
    `content`       TEXT            NOT NULL COMMENT '问题内容',
    `status`        TINYINT         NOT NULL DEFAULT 1 COMMENT '1=待回答, 0=已回答, -1=已删除',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY `idx_target` (`target_uid`, `status`, `created_at`),
    KEY `idx_author` (`author_uid`),
    CONSTRAINT `fk_q_target` FOREIGN KEY (`target_uid`) REFERENCES `users`(`uid`) ON DELETE CASCADE,
    CONSTRAINT `fk_q_author` FOREIGN KEY (`author_uid`) REFERENCES `users`(`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='问题表（A向B提问）';

-- ============================================================
-- 3. 回答表 — 链接主人回答问题
-- ============================================================
CREATE TABLE IF NOT EXISTS `answers` (
    `id`            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `question_id`   BIGINT UNSIGNED NOT NULL COMMENT '对应的问题',
    `author_uid`    BIGINT UNSIGNED NOT NULL COMMENT '回答者（链接主人，前端不展示）',
    `content`       TEXT            NOT NULL COMMENT '回答内容',
    `ip_address`    VARCHAR(45)     NOT NULL DEFAULT '',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_question` (`question_id`),
    KEY `idx_author` (`author_uid`),
    CONSTRAINT `fk_a_question` FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_a_author`   FOREIGN KEY (`author_uid`)  REFERENCES `users`(`uid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='回答表（链接主人回答）';

-- ============================================================
-- 4. 频率限制
-- ============================================================
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `bucket`      VARCHAR(64)     NOT NULL,
    `action`      VARCHAR(32)     NOT NULL,
    `hit_at`      DATETIME        NOT NULL,

    KEY `idx_bucket_action` (`bucket`, `action`, `hit_at`),
    KEY `idx_cleanup` (`hit_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='频率限制';
