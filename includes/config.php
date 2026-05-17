<?php
/**
 * AnonAsk v3 配置
 */
define('DB_HOST',    'localhost');
define('DB_PORT',    3306);
define('DB_NAME',    'banqiuxy');
define('DB_USER',    'banqiuxy');
define('DB_PASS',    '123456');
define('DB_CHARSET', 'utf8mb4');

define('RATE_LIMIT_ENABLED',        true);
define('RATE_LIMIT_REGISTER',       3);
define('RATE_LIMIT_CREATE_QUESTION', 10);
define('RATE_LIMIT_CREATE_ANSWER',  20);
define('RATE_LIMIT_WINDOW',         600);

define('PASSWORD_MIN_LENGTH', 6);
define('PASSWORD_MAX_LENGTH', 20);
define('SESSION_LIFETIME', 86400 * 7);
