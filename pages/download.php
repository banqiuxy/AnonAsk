<?php
/**
 * AnonAsk 下载页 — 各平台客户端下载
 */
$platforms = [
    'android' => [
        'name' => 'Android',
        'icon' => '📱',
        'status' => '开发中',
        'desc' => 'Android APK 安装包',
        'coming_soon' => true,
    ],
    'windows' => [
        'name' => 'Windows',
        'icon' => '🪟',
        'status' => '开发中',
        'desc' => 'Windows 桌面版',
        'coming_soon' => true,
    ],
    'ios' => [
        'name' => 'iOS',
        'icon' => '🍎',
        'status' => '开发中',
        'desc' => 'iPhone / iPad 版本',
        'coming_soon' => true,
    ],
    'harmonyos' => [
        'name' => 'HarmonyOS',
        'icon' => '📲',
        'status' => '开发中',
        'desc' => '原生鸿蒙版本',
        'coming_soon' => true,
    ],
    'linux' => [
        'name' => 'Linux',
        'icon' => '🐧',
        'status' => '开发中',
        'desc' => 'Linux 桌面版（AppImage / deb）',
        'coming_soon' => true,
    ],
];

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
$uid = getLoginUid();
$isLoggedIn = ($uid !== null);
$page_title = '下载客户端 · AnonAsk';
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0,1"
        rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .dl-header {
            text-align: center;
            padding: 3rem 1rem 1rem;
        }

        .dl-header h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(125deg, #6a3de8, #b87aff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .dl-header p {
            color: #888;
            margin-top: 0.3rem;
        }

        .platform-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.2rem;
            padding: 1.5rem 1rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .platform-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
            border-radius: 28px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--md-elevation-1);
            border: 1px solid rgba(124, 77, 255, 0.15);
            transition: 0.2s;
        }

        .platform-card:hover {
            box-shadow: var(--md-elevation-2);
            transform: translateY(-2px);
        }

        .platform-card .icon {
            font-size: 2.8rem;
            margin-bottom: 0.5rem;
        }

        .platform-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .platform-card .desc {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.8rem;
        }

        .platform-card .badge {
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 40px;
            font-size: 0.8rem;
        }

        .badge-coming {
            background: #fff3cd;
            color: #856404;
        }

        .badge-ready {
            background: #d4edda;
            color: #155724;
        }

        .badge-btn {
            background: var(--md-primary);
            color: #fff;
            cursor: pointer;
        }

        .badge-btn:hover {
            opacity: 0.9;
        }

        .dl-note {
            text-align: center;
            padding: 2rem 1rem 3rem;
            color: #888;
            font-size: 0.9rem;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .web-access {
            text-align: center;
            padding: 1rem 1rem 2rem;
        }

        .web-access p {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .footer {
            text-align: center;
            padding: 2rem 1rem;
            color: #aaa;
            font-size: 0.85rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="nav-inner">
            <a href="/" class="nav-logo" style="text-decoration:none">AnonAsk</a>
            <div class="nav-links">
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

    <div class="dl-header">
        <h1>📥 下载客户端</h1>
        <p>多平台支持，随时随地收问答疑</p>
    </div>

    <div class="platform-grid">
        <?php foreach ($platforms as $key => $p): ?>
            <div class="platform-card">
                <div class="icon"><?= $p['icon'] ?></div>
                <h3><?= $p['name'] ?></h3>
                <div class="desc"><?= $p['desc'] ?></div>
                <?php if ($p['coming_soon']): ?>
                    <span class="badge badge-coming">⏳ 开发中</span>
                <?php else: ?>
                    <a href="<?= h($p['url']) ?>" class="badge badge-btn">⬇️ 下载</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="web-access">
        <h3>🌐 也可以直接用 Web 版</h3>
        <p>所有功能在浏览器中即可使用，无需安装任何软件</p>
        <div style="display:flex;gap:0.8rem;justify-content:center;flex-wrap:wrap;margin-top:0.5rem">
            <a href="/" class="btn btn-primary btn-sm">🏠 回到首页</a>
            <?php if (!$isLoggedIn): ?>
                <a href="/pages/login.php" class="btn btn-outline btn-sm">登录 / 注册</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dl-note">
        各平台客户端正在积极开发中，敬请期待。<br>
        如果你想第一时间获取下载通知，请关注项目动态。
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