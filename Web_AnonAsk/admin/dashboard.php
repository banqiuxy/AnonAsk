<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/config.php';
requireAdminLogin();

$page_title = '管理后台 · AnonAsk';
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
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Roboto',system-ui,sans-serif;background:#f0f2f5;color:#1e1e2f;font-size:14px}
        .admin-header{background:#1c1b2e;color:#fff;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:52px;position:sticky;top:0;z-index:100}
        .admin-header .logo{font-weight:700;font-size:1.05rem}
        .admin-header .logo span{color:#b87aff}
        .admin-header .right{display:flex;gap:1rem;align-items:center;font-size:0.82rem}
        .admin-header .right a{color:#aaa;text-decoration:none}
        .admin-header .right a:hover{color:#fff}
        .admin-body{display:flex;min-height:calc(100vh - 52px)}
        .sidebar{width:185px;background:#fff;border-right:1px solid #e0e0e0;padding:0.8rem 0;flex-shrink:0}
        .sidebar .tab-btn{display:block;width:100%;text-align:left;padding:0.6rem 1rem;border:none;background:none;cursor:pointer;font-size:0.88rem;color:#333;transition:0.15s;border-left:3px solid transparent}
        .sidebar .tab-btn:hover{background:#f5f3fe}
        .sidebar .tab-btn.active{background:#f0ebff;border-left-color:#7c4dff;color:#7c4dff;font-weight:600}
        .main-content{flex:1;padding:1.2rem 1.5rem;overflow-y:auto;max-height:calc(100vh - 52px)}
        .tab-panel{display:none}
        .tab-panel.active{display:block}
        .tab-panel h2{font-size:1.2rem;margin-bottom:0.15rem}
        .tab-panel .sub{color:#888;font-size:0.82rem;margin-bottom:1rem}

        .stats-row{display:flex;gap:0.8rem;margin-bottom:1rem;flex-wrap:wrap}
        .stat-card{background:#fff;border-radius:14px;padding:0.8rem 1.2rem;box-shadow:0 1px 3px rgba(0,0,0,0.05);min-width:120px;flex:1}
        .stat-card .num{font-size:1.4rem;font-weight:700;color:#7c4dff}
        .stat-card .label{font-size:0.78rem;color:#888;margin-top:2px}

        .search-bar{display:flex;gap:0.5rem;margin-bottom:0.8rem;flex-wrap:wrap}
        .search-bar input{padding:0.4rem 0.7rem;border:1px solid #ddd;border-radius:10px;font-size:0.85rem;flex:1;min-width:160px}
        .search-bar input:focus{outline:none;border-color:#7c4dff}
        .search-bar select{padding:0.4rem 0.7rem;border:1px solid #ddd;border-radius:10px;font-size:0.85rem}

        table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:0.5rem}
        th{background:#f8f6ff;text-align:left;padding:0.55rem 0.8rem;font-size:0.78rem;color:#666;font-weight:600;white-space:nowrap}
        td{padding:0.45rem 0.8rem;font-size:0.85rem;border-top:1px solid #f0f0f0;word-break:break-all}
        tr:hover{background:#faf9ff}
        .badge{display:inline-block;padding:0.1rem 0.45rem;border-radius:10px;font-size:0.72rem;font-weight:500}
        .badge-ok{background:#e8f8e8;color:#27ae60}
        .badge-pending{background:#fff3cd;color:#856404}
        .badge-del{background:#ffeaea;color:#c0392b}

        .action-btn{background:none;border:1px solid #ddd;border-radius:8px;cursor:pointer;font-size:0.78rem;padding:0.2rem 0.5rem;transition:0.15s}
        .action-btn.danger{color:#e74c3c;border-color:#e74c3c}
        .action-btn.danger:hover{background:#ffeaea}
        .action-btn:hover{background:#f0ebff}

        .add-user-card{background:#fff;border-radius:14px;padding:1rem;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:1rem;display:flex;gap:0.6rem;align-items:flex-end;flex-wrap:wrap}
        .add-user-card .field{flex:1;min-width:120px}
        .add-user-card .field label{display:block;font-size:0.78rem;color:#888;margin-bottom:2px}
        .add-user-card .field input,.add-user-card .field select{width:100%;padding:0.4rem 0.6rem;border:1px solid #ddd;border-radius:10px;font-size:0.85rem}

        .group-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,0.05);margin-bottom:0.8rem;overflow:hidden}
        .group-header{padding:0.7rem 1rem;cursor:pointer;display:flex;justify-content:space-between;align-items:center;font-size:0.9rem;font-weight:600;background:#faf9ff;border-bottom:1px solid #f0f0f0;transition:0.15s}
        .group-header:hover{background:#f5f3fe}
        .group-header .meta{font-weight:400;font-size:0.78rem;color:#888}
        .group-body{display:none;padding:0}
        .group-body.open{display:block}

        .empty-state{text-align:center;padding:2.5rem;color:#888}
        .empty-state .material-symbols-rounded{font-size:2.5rem;color:#ddd}
        .msg-toast{position:fixed;bottom:2rem;right:2rem;background:#1c1b2e;color:#fff;padding:0.6rem 1.2rem;border-radius:12px;font-size:0.85rem;z-index:9999;opacity:0;transition:0.3s;pointer-events:none}
        .msg-toast.show{opacity:1}

        .table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin-bottom:0.5rem}
        .table-wrap table{min-width:600px}
        .table-wrap th:last-child,.table-wrap td:last-child{position:sticky;right:0;background:inherit;z-index:1}
        .table-wrap td:last-child{background:#fff}
        .table-wrap tr:hover td:last-child{background:#faf9ff}
        @media(max-width:768px){.admin-body{flex-direction:column}.sidebar{width:100%;display:flex;overflow-x:auto;padding:0}.sidebar .tab-btn{display:inline-block;width:auto;border-left:none;border-bottom:2px solid transparent;padding:0.5rem 0.8rem;font-size:0.82rem}.sidebar .tab-btn.active{border-bottom-color:#7c4dff;background:none}}
    </style>
</head>
<body>

<div class="admin-header">
    <div class="logo">AnonAsk <span>Admin</span></div>
    <div class="right">
        <a href="/" target="_blank">🌐 站点首页</a>
        <span id="adminTime"></span>
        <a href="/admin/logout.php">退出</a>
    </div>
</div>

<div class="admin-body">
    <!-- 侧栏 -->
    <div class="sidebar">
        <button class="tab-btn active" data-tab="users">👥 用户管理</button>
        <button class="tab-btn" data-tab="questions">❓ 问题管理</button>
        <button class="tab-btn" data-tab="answers">💬 回答管理</button>
    </div>

    <!-- 主内容 -->
    <div class="main-content">

        <!-- ======================== 用户管理 ======================== -->
        <div class="tab-panel active" id="tab-users">
            <h2>👥 用户管理</h2>
            <p class="sub">查看、添加、删除用户</p>
            <div class="stats-row" id="userStats"></div>

            <!-- 添加用户表单 -->
            <div class="add-user-card" id="addUserForm">
                <div class="field">
                    <label>联系方式类型</label>
                    <select id="addType"><option value="phone">📱 手机号</option><option value="qq">💬 QQ号</option><option value="wechat">✉️ 微信号</option></select>
                </div>
                <div class="field">
                    <label>联系方式值</label>
                    <input type="text" id="addValue" placeholder="手机号 / QQ / 微信号">
                </div>
                <div class="field">
                    <label>密码</label>
                    <input type="text" id="addPwd" placeholder="6-20位小写字母+数字" style="font-size:0.85rem">
                </div>
                <button class="btn btn-primary btn-sm" id="addUserBtn">➕ 添加用户</button>
            </div>

            <div class="search-bar">
                <input type="text" id="userSearch" placeholder="搜索 UID / 联系方式…" onkeyup="loadUsers(1)">
            </div>
            <div id="userTableWrap"><div class="empty-state">加载中…</div></div>
            <div class="pagination" id="userPagination" style="display:flex;gap:0.4rem;justify-content:center;margin-top:0.6rem"></div>
        </div>

        <!-- ======================== 问题管理 ======================== -->
        <div class="tab-panel" id="tab-questions">
            <h2>❓ 问题管理</h2>
            <p class="sub">按用户分组展示每个人收到的提问（含提问者信息）</p>
            <div class="stats-row" id="questionStats"></div>
            <div id="questionWrap"><div class="empty-state">加载中…</div></div>
        </div>

        <!-- ======================== 回答管理 ======================== -->
        <div class="tab-panel" id="tab-answers">
            <h2>💬 回答管理</h2>
            <p class="sub">按回答者分组展示每条回答的问题和提问者</p>
            <div class="stats-row" id="answerStats"></div>
            <div id="answerWrap"><div class="empty-state">加载中…</div></div>
        </div>

    </div>
</div>

<div class="msg-toast" id="msgToast"></div>

<script src="/assets/js/app.js"></script>
<script>
(function(){
    'use strict';
    const api = (url, body=null) => fetch(url, {method: body?'POST':'GET', headers:body?{'Content-Type':'application/json'}:{}, body:body?JSON.stringify(body):null}).then(r=>r.json());
    const toast = document.getElementById('msgToast');
    function showMsg(msg){toast.textContent=msg;toast.classList.add('show');setTimeout(()=>toast.classList.remove('show'),2500)}

    // 时间
    const t=document.getElementById('adminTime');
    function upd(){t.textContent=new Date().toLocaleString('zh-CN')}
    upd();setInterval(upd,60000);

    // Tab 切换
    document.querySelectorAll('.tab-btn').forEach(b=>b.addEventListener('click',function(){
        document.querySelectorAll('.tab-btn').forEach(x=>x.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(x=>x.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-'+this.dataset.tab).classList.add('active');
    }));

    // ================================================================
    // 用户管理
    // ================================================================
    window.loadUsers = async function(page) {
        const search = document.getElementById('userSearch').value.trim();
        const r = await api('/api/admin.php?action=list-users&page='+page+'&search='+encodeURIComponent(search));
        if(!r||r.code!==0){document.getElementById('userTableWrap').innerHTML='<div class="alert alert-error">加载失败</div>';return}
        renderUsers(r.data);
    };

    function renderUsers(d){
        const {items,total,page,limit,has_more}=d;
        document.getElementById('userStats').innerHTML=`<div class="stat-card"><div class="num">${total}</div><div class="label">总用户</div></div>`;

        if(!items.length){
            document.getElementById('userTableWrap').innerHTML='<div class="empty-state"><div class="material-symbols-rounded">people</div><p>暂无用户</p></div>';
            document.getElementById('userPagination').innerHTML='';
            return;
        }
        const cm={'phone':'📱','qq':'💬','wechat':'✉️'};
        let h='<div class="table-wrap"><table><thead><tr><th>UID</th><th>联系方式</th><th>注册时间</th><th>最后登录</th><th>操作</th></tr></thead><tbody>';
        items.forEach(u=>{
            h+=`<tr><td><strong>${u.uid}</strong></td><td>${cm[u.contact_type]||''} ${u.contact_type}<br><span style="color:#888;font-size:0.8rem">${u.contact_value}</span></td><td style="white-space:nowrap">${AnonAsk.formatDate(u.created_at)}</td><td style="white-space:nowrap">${AnonAsk.formatDate(u.last_login)}</td>
            <td><button class="action-btn danger" onclick="delUser(${u.uid},this)">🗑️ 删除</button></td></tr>`;
        });
        h+='</tbody></table></div>';
        document.getElementById('userTableWrap').innerHTML=h;
        pageLinks('userPagination',page,Math.ceil(total/limit),p=>loadUsers(p));
    }

    window.delUser = async function(uid,btn){
        if(!confirm('确定删除用户 '+uid+' 及其所有问题/回答？'))return;
        btn.disabled=true;btn.textContent='删除中…';
        const r=await api('/api/admin.php?action=delete-user',{uid});
        if(r.code===0){showMsg('用户已删除');loadUsers(1)}
        else{alert(r.msg||'删除失败');btn.disabled=false;btn.textContent='🗑️ 删除'}
    };

    // 添加用户
    document.getElementById('addUserBtn').addEventListener('click',async function(){
        const type=document.getElementById('addType').value;
        const val=document.getElementById('addValue').value.trim();
        const pwd=document.getElementById('addPwd').value.trim();
        if(!val||!pwd){alert('请填写完整');return}
        this.disabled=true;this.textContent='添加中…';
        const r=await api('/api/admin.php?action=add-user',{contact_type:type,contact_value:val,password:pwd});
        if(r.code===0){showMsg('用户添加成功 UID: '+r.data.uid);document.getElementById('addValue').value='';document.getElementById('addPwd').value='';loadUsers(1)}
        else{alert(r.msg||'添加失败')}
        this.disabled=false;this.textContent='➕ 添加用户';
    });

    // ================================================================
    // 问题管理 — 按被提问者分组
    // ================================================================
    window.loadQuestions = async function(){
        const r=await api('/api/admin.php?action=list-questions');
        if(!r||r.code!==0){document.getElementById('questionWrap').innerHTML='<div class="alert alert-error">加载失败</div>';return}
        renderQuestions(r.data);
    };

    function renderQuestions(d){
        const groups=d.groups||[];
        let totalQ=0,totalP=0;
        groups.forEach(g=>{totalQ+=g.question_count;totalP+=g.pending_count});
        document.getElementById('questionStats').innerHTML=
            `<div class="stat-card"><div class="num">${groups.length}</div><div class="label">有问题的用户</div></div>
             <div class="stat-card"><div class="num">${totalQ}</div><div class="label">总问题数</div></div>
             <div class="stat-card"><div class="num" style="color:#e67e22">${totalP}</div><div class="label">待回答</div></div>`;

        if(!groups.length){
            document.getElementById('questionWrap').innerHTML='<div class="empty-state"><div class="material-symbols-rounded">forum</div><p>暂无问题</p></div>';
            return;
        }

        let html='';
        groups.forEach(g=>{
            html+=`<div class="group-card">
                <div class="group-header" onclick="this.nextElementSibling.classList.toggle('open')">
                    <span>👤 被提问者 UID: <strong>${g.target_uid}</strong> <span class="meta">(${g.target_contact})</span></span>
                    <span class="meta">${g.question_count} 个问题 · ${g.pending_count} 个待回答 ${g.pending_count>0?'⏳':''} ▾</span>
                </div>
                <div class="group-body">`;

            if(g.questions.length){
                html+=`<div class="table-wrap"><table><thead><tr><th>ID</th><th>问题内容</th><th>提问者</th><th>状态</th><th>时间</th><th>操作</th></tr></thead><tbody>`;
                g.questions.forEach(q=>{
                    const st=q.status===1?'<span class="badge badge-pending">待回答</span>':q.status===0?'<span class="badge badge-ok">已回答</span>':'<span class="badge badge-del">已删除</span>';
                    html+=`<tr>
                        <td>${q.id}</td>
                        <td style="max-width:220px">${q.content}</td>
                        <td><code>${q.asker_uid}</code><br><span style="font-size:0.75rem;color:#888">${q.asker_type||''}: ${q.asker_value||'-'}</span></td>
                        <td>${st}</td>
                        <td style="white-space:nowrap;font-size:0.78rem">${AnonAsk.formatDate(q.question_time)}</td>
                        <td><button class="action-btn danger" onclick="delQuestion(${q.id},this)">🗑️</button></td>
                    </tr>`;
                });
                html+=`</tbody></table></div>`;
            }else{
                html+=`<div class="empty-state" style="padding:1rem"><p>暂无问题</p></div>`;
            }

            html+=`</div></div>`;
        });
        document.getElementById('questionWrap').innerHTML=html;
    }

    window.delQuestion = async function(qid,btn){
        if(!confirm('确定删除问题 #'+qid+' 及其回答？'))return;
        btn.disabled=true;btn.textContent='…';
        const r=await api('/api/admin.php?action=delete-question',{question_id:qid});
        if(r.code===0){showMsg('已删除');loadQuestions()}
        else{alert(r.msg||'失败');btn.disabled=false}
    };

    // ================================================================
    // 回答管理 — 按回答者分组
    // ================================================================
    window.loadAnswers = async function(){
        const r=await api('/api/admin.php?action=list-answers');
        if(!r||r.code!==0){document.getElementById('answerWrap').innerHTML='<div class="alert alert-error">加载失败</div>';return}
        renderAnswers(r.data);
    };

    function renderAnswers(d){
        const groups=d.groups||[];
        let totalA=0;
        groups.forEach(g=>totalA+=g.answer_count);
        document.getElementById('answerStats').innerHTML=
            `<div class="stat-card"><div class="num">${groups.length}</div><div class="label">有回答的用户</div></div>
             <div class="stat-card"><div class="num">${totalA}</div><div class="label">总回答数</div></div>`;

        if(!groups.length){
            document.getElementById('answerWrap').innerHTML='<div class="empty-state"><div class="material-symbols-rounded">sms</div><p>暂无回答</p></div>';
            return;
        }

        let html='';
        groups.forEach(g=>{
            html+=`<div class="group-card">
                <div class="group-header" onclick="this.nextElementSibling.classList.toggle('open')">
                    <span>💬 回答者 UID: <strong>${g.answerer_uid}</strong> <span class="meta">(${g.answerer_contact})</span></span>
                    <span class="meta">${g.answer_count} 个回答 ▾</span>
                </div>
                <div class="group-body">`;

            if(g.answers.length){
                html+=`<div class="table-wrap"><table><thead><tr><th>回答ID</th><th>回答内容</th><th>对应问题</th><th>提问者</th><th>IP</th><th>时间</th><th>操作</th></tr></thead><tbody>`;
                g.answers.forEach(a=>{
                    html+=`<tr>
                        <td>${a.answer_id}</td>
                        <td style="max-width:180px">${a.answer_content}</td>
                        <td style="max-width:180px">#${a.question_id} ${a.question_content}</td>
                        <td><code>${a.asker_uid}</code><br><span style="font-size:0.75rem;color:#888">${a.asker_type||''}: ${a.asker_value||'-'}</span></td>
                        <td style="font-size:0.78rem;color:#888">${a.ip_address||'-'}</td>
                        <td style="white-space:nowrap;font-size:0.78rem">${AnonAsk.formatDate(a.answer_time)}</td>
                        <td><button class="action-btn danger" onclick="delAnswer(${a.answer_id},this)">🗑️</button></td>
                    </tr>`;
                });
                html+=`</tbody></table></div>`;
            }else{
                html+=`<div class="empty-state" style="padding:1rem"><p>暂无回答</p></div>`;
            }

            html+=`</div></div>`;
        });
        document.getElementById('answerWrap').innerHTML=html;
    }

    window.delAnswer = async function(aid,btn){
        if(!confirm('确定删除回答 #'+aid+'？（问题状态将恢复为待回答）'))return;
        btn.disabled=true;btn.textContent='…';
        const r=await api('/api/admin.php?action=delete-answer',{answer_id:aid});
        if(r.code===0){showMsg('已回答删除');loadAnswers()}
        else{alert(r.msg||'失败');btn.disabled=false}
    };

    // ================================================================
    // 分页
    // ================================================================
    function pageLinks(id,cur,total,cb){
        const el=document.getElementById(id);
        if(total<=1){el.innerHTML='';return}
        let h='';
        h+=`<button ${cur<=1?'disabled':''} onclick="(${cb.toString()})(${cur-1})">←</button>`;
        h+=`<span style="font-size:0.82rem;color:#888">${cur}/${total}</span>`;
        h+=`<button ${cur>=total?'disabled':''} onclick="(${cb.toString()})(${cur+1})">→</button>`;
        el.innerHTML=h;
    }

    // 初始加载
    document.addEventListener('DOMContentLoaded',()=>{loadUsers(1);loadQuestions();loadAnswers()});
})();
</script>
</body>
</html>
