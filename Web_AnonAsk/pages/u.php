<?php
/**
 * AnonAsk 公开用户页 — /u/{uid}
 * 
 * 显示该用户收到的所有问答（前端完全不显示任何用户信息）
 * 底部提供提问入口（需登录）
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$targetUid = (int)($_GET['uid'] ?? 0);
if ($targetUid <= 0) {
    http_response_code(400);
    die('参数错误');
}

// 查询用户名下收到的问答
try {
    $db = getDB();

    $stmt = $db->prepare('SELECT uid FROM users WHERE uid = ? LIMIT 1');
    $stmt->execute([$targetUid]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        die('用户不存在');
    }

    // 查所有问题（含已回答的）
    $stmt = $db->prepare(
        'SELECT q.id, q.content AS q_content, q.created_at AS q_time,
                a.content AS a_content, a.created_at AS a_time,
                q.status
         FROM questions q
         LEFT JOIN answers a ON a.question_id = q.id
         WHERE q.target_uid = ? AND q.status >= 0
         ORDER BY q.created_at DESC
         LIMIT 50'
    );
    $stmt->execute([$targetUid]);
    $questions = $stmt->fetchAll();

} catch (Exception $e) {
    http_response_code(500);
    die('服务器错误');
}

$currentUid = getLoginUid();
$isOwner = ($currentUid !== null && $currentUid === $targetUid);
$page_title = '向我提问 · AnonAsk';

?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0,1" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .header-area{text-align:center;padding:2rem 1rem}
        .header-area h1{font-size:1.8rem;font-weight:700;background:linear-gradient(125deg,#6a3de8,#b87aff);-webkit-background-clip:text;background-clip:text;color:transparent}
        .header-area p{color:#888;margin-top:0.3rem}

        .q-block{background:#fff;border-radius:20px;padding:1.2rem 1.5rem;margin-bottom:1rem;box-shadow:var(--md-elevation-1);border:1px solid rgba(124,77,255,0.08)}
        .q-content{font-size:1rem;line-height:1.6}
        .q-time{font-size:0.78rem;color:#bbb;margin-top:0.3rem}
        .a-block{margin-top:0.8rem;padding-top:0.8rem;border-top:1px solid #f0f0f0}
        .a-label{font-size:0.78rem;color:#999;margin-bottom:0.2rem}
        .a-content{font-size:0.95rem;line-height:1.6;color:#333}
        .a-time{font-size:0.78rem;color:#bbb;margin-top:0.3rem}

        .ask-area{background:#f8f6ff;border-radius:24px;padding:1.5rem;margin-top:2rem;margin-bottom:2rem}
        .ask-area textarea{width:100%;border:1.5px solid #e0e0e0;border-radius:16px;padding:0.8rem;font-size:1rem;font-family:inherit;resize:vertical;transition:0.2s}
        .ask-area textarea:focus{outline:none;border-color:var(--md-primary);box-shadow:0 0 0 3px rgba(124,77,255,0.1)}

        .empty-state{text-align:center;padding:2rem;color:#888}
        .empty-state .material-symbols-rounded{font-size:3rem;color:#ddd}
        .footer{text-align:center;padding:2rem 1rem;color:#aaa;font-size:0.85rem}
    </style>
</head>
<body>

<?php if ($currentUid): ?>
<nav class="navbar">
    <div class="nav-inner">
        <a href="/" class="nav-logo" style="text-decoration:none">AnonAsk</a>
        <div class="nav-links">
            <a href="/pages/dashboard.php" class="nav-link">📋 收件箱</a>
            <a href="#" class="nav-link" id="logoutBtn" style="color:#e74c3c">退出</a>
        </div>
    </div>
</nav>
<?php endif; ?>

<div class="page-wrap" style="max-width:700px">

    <div class="header-area">
        <h1>💬 向我提问</h1>
        <p>匿名向我提问，我会在这里回答</p>
    </div>

    <!-- 问答列表 -->
    <div id="qaList">
        <?php if (empty($questions)): ?>
            <div class="empty-state">
                <div class="material-symbols-rounded">sms</div>
                <p>还没有收到任何问题</p>
                <p style="font-size:0.85rem;margin-top:0.3rem">快来问第一个问题吧！</p>
            </div>
        <?php else: ?>
            <?php foreach ($questions as $q): ?>
                <div class="q-block">
                    <div class="q-content"><?= h($q['q_content']) ?></div>
                    <div class="q-time"><?= date('Y-m-d H:i', strtotime($q['q_time'])) ?></div>

                    <?php if ($q['a_content']): ?>
                    <div class="a-block">
                        <div class="a-label">💡 回答</div>
                        <div class="a-content"><?= h($q['a_content']) ?></div>
                        <div class="a-time"><?= date('Y-m-d H:i', strtotime($q['a_time'])) ?></div>
                    </div>
                    <?php else: ?>
                        <?php if ($isOwner): ?>
                        <div style="margin-top:0.8rem;text-align:right">
                            <span style="font-size:0.8rem;color:#e67e22">⏳ 待回答</span>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 提问入口 -->
    <div class="ask-area">
        <h4 style="margin-bottom:0.8rem;font-weight:600">✏️ 向 TA 提问</h4>
        <?php if ($currentUid): ?>
            <?php if ($isOwner): ?>
                <p style="color:#888;font-size:0.9rem;text-align:center">这是你自己的页面，不能向自己提问</p>
                <p style="text-align:center;margin-top:0.5rem">
                    <a href="/pages/dashboard.php" class="btn btn-primary btn-sm">📋 去收件箱查看问题</a>
                </p>
            <?php else: ?>
                <textarea id="questionInput" rows="3" placeholder="输入你想问的问题（2000字以内）" style="min-height:80px"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem">
                    <span id="charCount" style="font-size:0.85rem;color:#888">0/2000</span>
                    <button class="btn btn-primary" id="askBtn">📤 提交问题</button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p style="color:#888;margin-bottom:1rem;text-align:center">登录后即可匿名提问</p>
            <div style="text-align:center">
                <a href="/pages/login.php?redirect=<?= urlencode('/u.php?uid='.$targetUid) ?>" class="btn btn-primary">登录 / 注册</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">所有提问和回答完全匿名 · 仅通过链接访问</div>
</div>

<script src="/assets/js/app.js"></script>
<script>
(function(){
    const TARGET_UID = <?= $targetUid ?>;
    const IS_OWNER = <?= json_encode($isOwner) ?>;

    const askBtn = document.getElementById('askBtn');
    const input = document.getElementById('questionInput');
    const charCount = document.getElementById('charCount');

    if (input) {
        input.addEventListener('input', () => {
            const len = input.value.length;
            charCount.textContent = len + '/2000';
            charCount.style.color = len > 2000 ? '#e74c3c' : '#888';
        });
    }

    if (askBtn && input) {
        askBtn.addEventListener('click', async () => {
            const content = input.value.trim();
            if (!content) { alert('请输入问题'); return; }
            if (content.length > 2000) { alert('问题不能超过2000字'); return; }

            askBtn.disabled = true;
            askBtn.textContent = '提交中...';

            try {
                const result = await AnonAsk.api('POST', '/api/question.php?action=create', {
                    target_uid: TARGET_UID,
                    content: content
                });

                if (result.code === 0) {
                    alert('提问成功！等待对方回答。');
                    location.reload();
                } else {
                    alert(result.msg || '提交失败');
                    askBtn.disabled = false;
                    askBtn.textContent = '📤 提交问题';
                }
            } catch(e) {
                alert('网络错误');
                askBtn.disabled = false;
                askBtn.textContent = '📤 提交问题';
            }
        });
    }

    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            await AnonAsk.logout();
            location.reload();
        });
    }
})();
</script>
</body>
</html>
