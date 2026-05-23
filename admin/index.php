<?php
/**
 * 管理员登录页
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/config.php';

// 如果已登录，直接跳转仪表盘
if (isAdminLoggedIn()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = '请输入账号和密码';
    } elseif (adminLogin($username, $password)) {
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = '账号或密码错误';
    }
}

$page_title = '管理员登录 · AnonAsk';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0,1" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .admin-wrap{max-width:380px;margin:4rem auto;padding:0 1rem}
        .admin-wrap .card{text-align:center}
        .admin-wrap .icon{font-size:3rem;color:var(--md-primary);margin-bottom:0.5rem}
        .admin-wrap h1{font-size:1.3rem;margin-bottom:0.3rem}
        .admin-wrap .sub{color:#888;font-size:0.9rem;margin-bottom:1.5rem}
        .error-msg{background:#ffeaea;color:#c0392b;padding:0.5rem;border-radius:12px;margin-bottom:1rem;font-size:0.85rem;display:<?= $error ? 'block' : 'none' ?>}
        .back-link{display:block;text-align:center;margin-top:1rem;font-size:0.85rem;color:#888}
    </style>
</head>
<body>
    <div class="admin-wrap">
        <div class="card">
            <div class="icon material-symbols-rounded">admin_panel_settings</div>
            <h1>AnonAsk 管理后台</h1>
            <p class="sub">仅管理员可登录</p>

            <div class="error-msg"><?= h($error) ?></div>

            <form method="post" autocomplete="off">
                <div class="form-group">
                    <input type="text" name="username" placeholder="管理员账号" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="密码" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">登录管理后台</button>
            </form>

            <a href="/" class="back-link">← 返回首页</a>
        </div>
    </div>
</body>
</html>
