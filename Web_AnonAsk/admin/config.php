<?php
/**
 * AnonAsk 管理员配置
 * 
 * 管理员账号和密码硬编码在此文件。
 * 生产环境建议改为从环境变量或单独配置文件读取。
 */

define('ADMIN_USERNAME', 'banqiuxy');
define('ADMIN_PASSWORD', '123456'); // 可自行修改

/** 启动管理员会话 */
function adminStartSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 86400,
            'path' => '/admin',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/** 检查是否已登录管理后台 */
function isAdminLoggedIn(): bool
{
    adminStartSession();
    return !empty($_SESSION['admin_logged_in']);
}

/** 要求管理员登录，未登录则跳转 */
function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: /admin/index.php');
        exit;
    }
}

/** 管理员登录 */
function adminLogin(string $username, string $password): bool
{
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        adminStartSession();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        return true;
    }
    return false;
}

/** 管理员注销 */
function adminLogout(): void
{
    adminStartSession();
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_login_time']);
    session_destroy();
}
