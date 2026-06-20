<?php
/**
 * AnonAsk 用户收件箱 — 看到别人向我提的问题
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$uid = getLoginUid();
if (!$uid) {
    header('Location: /pages/login.php');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare('SELECT uid, contact_type, created_at FROM users WHERE uid=? LIMIT 1');
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
} catch (Exception $e) { $user = null; }

$page_title = '收件箱 · AnonAsk';
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
        .user-bar{display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap}
        .user-bar .info{flex:1}
        .user-bar .info h2{font-size:1.1rem}
        .user-bar .info p{font-size:0.85rem;color:#888}

        .filter-tabs{display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap}
        .filter-tab{padding:0.3rem 1rem;border-radius:40px;border:1px solid #ddd;cursor:pointer;font-size:0.85rem;background:#fff;transition:0.2s}
        .filter-tab.active{border-color:var(--md-primary);background:var(--md-primary-container);color:var(--md-primary)}

        .inbox-item{background:#fff;border-radius:20px;padding:1.2rem 1.5rem;margin-bottom:0.8rem;box-shadow:var(--md-elevation-1);border:1px solid rgba(124,77,255,0.08)}
        .inbox-item .q-text{font-size:1rem;line-height:1.6}
        .inbox-item .q-meta{font-size:0.8rem;color:#aaa;margin-top:0.3rem;display:flex;gap:0.5rem;align-items:center}
        .inbox-item .answer-area{margin-top:0.8rem;padding-top:0.8rem;border-top:1px solid #f0f0f0}
        .inbox-item .answer-area textarea{width:100%;border:1px solid #ddd;border-radius:12px;padding:0.6rem;font-size:0.9rem;font-family:inherit;resize:vertical;min-height:60px}
        .pending-tag{color:#e67e22;font-size:0.8rem}
        .answered-tag{color:#27ae60;font-size:0.8rem}

        .empty-state{text-align:center;padding:3rem;color:#888}
        .empty-state .material-symbols-rounded{font-size:3rem;color:#ddd}
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-inner">
        <a href="/" class="nav-logo" style="text-decoration:none">AnonAsk</a>
        <div class="nav-links">
            <a href="/u.php?uid=<?= $uid ?>" class="nav-link" target="_blank">🔗 我的链接</a>
            <a href="#" class="nav-link" id="logoutBtn" style="color:#e74c3c">退出</a>
        </div>
    </div>
</nav>

<div class="page-wrap page-wide">

    <div class="user-bar">
        <div class="info">
            <h2>📋 我的收件箱</h2>
            <p>UID: <?= h((string)$uid) ?> · 别人向你提的问题都在这里</p>
        </div>
        <button class="btn btn-primary btn-sm" id="copyLinkBtn">🔗 复制我的链接</button>
    </div>

    <div class="filter-tabs">
        <span class="filter-tab active" data-filter="all">全部</span>
        <span class="filter-tab" data-filter="pending">待回答</span>
        <span class="filter-tab" data-filter="answered">已回答</span>
    </div>

    <div id="questionList">
        <div class="empty-state">
            <div class="material-symbols-rounded">inbox</div>
            <p>加载中…</p>
        </div>
    </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
(function(){
    let currentFilter = 'all';
    const listEl = document.getElementById('questionList');

    // 筛选切换
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            loadQuestions(1);
        });
    });

    async function loadQuestions(page) {
        try {
            const result = await AnonAsk.api('GET',
                '/api/question.php?action=list-for-me&page=' + page + '&limit=20&filter=' + currentFilter
            );
            if (result.code === 0) {
                render(result.data);
            } else {
                listEl.innerHTML = '<div class="alert alert-error">加载失败</div>';
            }
        } catch(e) {
            listEl.innerHTML = '<div class="alert alert-error">网络错误</div>';
        }
    }

    // 正在回答哪个问题（记录 answer textarea 展开）
    let answeringId = null;

    function render(data) {
        const { items, total, page, has_more } = data;

        if (items.length === 0 && page === 1) {
            listEl.innerHTML = `
                <div class="empty-state">
                    <div class="material-symbols-rounded">inbox</div>
                    <p>还没有收到任何问题</p>
                    <p style="font-size:0.85rem;color:#aaa;margin-top:0.3rem">把你的链接分享到朋友圈等待提问吧</p>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach(q => {
            html += `
                <div class="inbox-item" data-id="${q.id}">
                    <div class="q-text">${q.content}</div>
                    <div class="q-meta">
                        <span>${AnonAsk.formatDate(q.created_at)}</span>
                        ${q.status === 1
                            ? '<span class="pending-tag">⏳ 待回答</span>'
                            : '<span class="answered-tag">✅ 已回答</span>'
                        }
                        ${q.status === 1
                            ? `<button class="btn btn-primary btn-sm answer-trigger" data-id="${q.id}" style="margin-left:auto">✏️ 回答</button>`
                            : ''
                        }
                        ${q.status === 0
                            ? `<button class="btn btn-outline btn-sm delete-trigger" data-id="${q.id}" style="margin-left:auto;color:#e74c3c;border-color:#e74c3c">🗑️ 删除</button>`
                            : ''
                        }
                    </div>
                    ${q.status === 0 && q.answer_content
                        ? `<div class="answer-area"><div style="font-size:0.85rem;color:#27ae60;margin-bottom:0.3rem">💡 我的回答</div><div style="line-height:1.6">${q.answer_content}</div><div style="font-size:0.78rem;color:#bbb;margin-top:0.3rem">${AnonAsk.formatDate(q.answer_time)}</div></div>`
                        : ''
                    }
                    <div class="answer-area answer-form" id="answerForm_${q.id}" style="display:none">
                        <textarea rows="3" placeholder="输入你的回答…" class="answer-textarea"></textarea>
                        <div style="display:flex;gap:0.5rem;justify-content:flex-end;margin-top:0.5rem">
                            <button class="btn btn-outline btn-sm cancel-answer" data-id="${q.id}">取消</button>
                            <button class="btn btn-primary btn-sm submit-answer" data-id="${q.id}">📤 提交回答</button>
                        </div>
                    </div>
                </div>
            `;
        });

        if (has_more) {
            html += `<div style="text-align:center;margin-top:1rem"><button class="btn btn-outline btn-sm" id="loadMore">加载更多</button></div>`;
        }

        listEl.innerHTML = html;

        // 绑定「回答」按钮
        document.querySelectorAll('.answer-trigger').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                // 隐藏其他展开的表单
                document.querySelectorAll('.answer-form').forEach(f => f.style.display = 'none');
                document.getElementById('answerForm_' + id).style.display = 'block';
                answeringId = parseInt(id);
            });
        });

        // 取消回答
        document.querySelectorAll('.cancel-answer').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('answerForm_' + this.dataset.id).style.display = 'none';
                answeringId = null;
            });
        });

        // 提交回答
        document.querySelectorAll('.submit-answer').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = parseInt(this.dataset.id);
                const textarea = this.closest('.answer-form').querySelector('.answer-textarea');
                const content = textarea.value.trim();
                if (!content) { alert('请输入回答内容'); return; }
                if (content.length > 5000) { alert('回答不能超过5000字'); return; }

                this.disabled = true;
                this.textContent = '提交中...';

                const result = await AnonAsk.api('POST', '/api/answer.php?action=create', {
                    question_id: id,
                    content: content
                });

                if (result.code === 0) {
                    loadQuestions(1);
                } else {
                    alert(result.msg || '提交失败');
                    this.disabled = false;
                    this.textContent = '📤 提交回答';
                }
            });
        });

        // 删除
        document.querySelectorAll('.delete-trigger').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('确定删除此问答？')) return;
                const id = parseInt(this.dataset.id);
                const result = await AnonAsk.api('POST', '/api/answer.php?action=delete', {
                    question_id: id
                });
                if (result.code === 0) {
                    loadQuestions(1);
                } else {
                    alert(result.msg || '删除失败');
                }
            });
        });

        // 加载更多
        const loadMore = document.getElementById('loadMore');
        if (loadMore) {
            loadMore.addEventListener('click', () => loadQuestions(page + 1));
        }
    }

    document.addEventListener('DOMContentLoaded', () => loadQuestions(1));

    // 退出
    document.getElementById('logoutBtn').addEventListener('click', async (e) => {
        e.preventDefault();
        await AnonAsk.logout();
        window.location.href = '/';
    });

    // 复制我的链接
    document.getElementById('copyLinkBtn')?.addEventListener('click', async () => {
        const url = window.location.origin + '/u.php?uid=<?= $uid ?>';
        const ok = await AnonAsk.copyText(url);
        if (ok) {
            const btn = document.getElementById('copyLinkBtn');
            btn.textContent = '✅ 已复制';
            setTimeout(() => { btn.textContent = '🔗 复制我的链接'; }, 2000);
        }
    });
})();
</script>
</body>
</html>
