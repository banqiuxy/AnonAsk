<?php
/**
 * AnonAsk 首页路由 / 入口
 * 
 * 处理：
 *   /          → 首页
 *   /dashboard → 收件箱
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// 首页
$uid = getLoginUid();
$isLoggedIn = ($uid !== null);

$userInfo = null;
if ($isLoggedIn) {
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT uid FROM users WHERE uid=? LIMIT 1');
        $stmt->execute([$uid]);
        $userInfo = $stmt->fetch();
    } catch (Exception $e) {
    }
}
?><!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnonAsk · 完全匿名的你问我答</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0,1"
        rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .hero {
            text-align: center;
            padding: 4rem 1rem 2rem
        }

        .hero h1 {
            font-size: 2.8rem;
            font-weight: 700;
            background: linear-gradient(125deg, #1F1A3A, #5A3EC8, #B77CFF);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.2
        }

        .hero p {
            font-size: 1.1rem;
            color: #555;
            max-width: 600px;
            margin: 1rem auto;
            line-height: 1.5
        }

        .hero-badge {
            display: inline-block;
            background: rgba(124, 77, 255, 0.12);
            padding: 0.3rem 1rem;
            border-radius: 40px;
            font-size: 0.85rem;
            color: #5f3dc9;
            margin-bottom: 1rem
        }

        .steps {
            max-width: 800px;
            margin: 0 auto 3rem;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem
        }

        .step-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
            border-radius: 32px;
            padding: 1.5rem;
            box-shadow: var(--md-elevation-1);
            border: 1px solid rgba(124, 77, 255, 0.2);
            text-align: center
        }

        .step-card .num {
            display: inline-block;
            width: 36px;
            height: 36px;
            border-radius: 18px;
            background: var(--md-primary);
            color: #fff;
            font-weight: 700;
            line-height: 36px;
            margin-bottom: 0.5rem
        }

        .step-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem
        }

        .step-card p {
            color: #666;
            font-size: 0.9rem
        }

        .footer {
            text-align: center;
            padding: 2rem 1rem;
            color: #aaa;
            font-size: 0.85rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05)
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-inner">
            <a href="/" class="nav-logo" style="text-decoration:none">AnonAsk</a>
            <div class="nav-links">
                <a href="/pages/download.php" class="nav-link">📥 下载客户端</a>
                <?php if ($isLoggedIn): ?>
                    <a href="/u.php?uid=<?= $uid ?>" class="nav-link" target="_blank">🔗 我的链接</a>
                    <a href="/pages/dashboard.php" class="nav-link">📋 收件箱</a>
                    <a href="#" class="nav-link" id="logoutBtn" style="color:#e74c3c">退出</a>
                <?php else: ?>
                    <a href="/pages/login.php" class="btn btn-primary btn-sm">登录 / 注册</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="hero">
        <div class="hero-badge">🔒 AnonAsk</div>
        <h1>向我提问，<br>我来回答。</h1>
        <p>注册后获得个人链接，分享到朋友圈。<br>别人登录后向你提问，你在收件箱里回答。前端完全匿名。</p>
        <div style="margin-top:1.5rem">
            <?php if ($isLoggedIn): ?>
                <a href="/u.php?uid=<?= $uid ?>" class="btn btn-primary" target="_blank">🔗 查看我的链接</a>
                <a href="/pages/dashboard.php" class="btn btn-outline" style="margin-left:0.5rem">📋 收件箱</a>
            <?php else: ?>
                <a href="/pages/login.php" class="btn btn-primary">注册 / 登录</a>
            <?php endif; ?>
            <a href="/pages/download.php" class="btn btn-outline" style="margin-left:0.5rem">📥 下载客户端</a>
        </div>
    </div>

    <div class="steps">
        <div class="step-card">
            <div class="num">1</div>
            <h3>注册账号</h3>
            <p>手机号/QQ号/微信号 + 密码，即可注册</p>
        </div>
        <div class="step-card">
            <div class="num">2</div>
            <h3>分享链接</h3>
            <p>获得个人链接 /u.php?uid={uid}，发到朋友圈</p>
        </div>
        <div class="step-card">
            <div class="num">3</div>
            <h3>收件回答</h3>
            <p>别人匿名提问，你在收件箱一一回答</p>
        </div>
    </div>

    <div class="footer copyright">AnonAsk · 完全匿名的你问我答</div>

    <script src="/assets/js/app.js"></script>
    <script>
        <?php if ($isLoggedIn): ?>
            document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
                e.preventDefault();
                await AnonAsk.logout();
                location.reload();
            });
        <?php endif; ?>
    </script>
</body>

</html>