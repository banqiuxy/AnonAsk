<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (getLoginUid()) {
    header('Location: /pages/dashboard.php');
    exit;
}
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 · AnonAsk</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0,1" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .auth-wrap{max-width:420px;margin:3rem auto;padding:0 1rem}
        .auth-tabs{display:flex;gap:0;margin-bottom:1.5rem;background:#f0ebff;border-radius:40px;padding:4px}
        .auth-tab{flex:1;text-align:center;padding:0.5rem;border-radius:40px;cursor:pointer;font-weight:500;transition:0.2s;border:none;background:none;font-size:0.95rem}
        .auth-tab.active{background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.08);color:var(--md-primary)}
        .contact-options{display:flex;gap:0.5rem;margin-bottom:1rem}
        .contact-opt{flex:1;text-align:center;padding:0.6rem;border-radius:16px;border:1.5px solid #e0e0e0;cursor:pointer;background:#fff;transition:0.2s;font-size:0.9rem}
        .contact-opt.active{border-color:var(--md-primary);background:var(--md-primary-container)}
        .error-msg{color:#e74c3c;font-size:0.9rem;margin-bottom:0.8rem;display:none}
        .switch-link{text-align:center;margin-top:1rem;font-size:0.9rem;color:#666}
        .switch-link a{cursor:pointer;color:var(--md-primary)}
    </style>
</head>
<body>
<div class="auth-wrap">
    <div style="text-align:center;margin-bottom:2rem">
        <a href="/" style="font-size:1.6rem;font-weight:700;background:linear-gradient(125deg,#6a3de8,#b87aff);-webkit-background-clip:text;background-clip:text;color:transparent;text-decoration:none">AnonAsk</a>
        <p style="color:#888;margin-top:0.3rem">登录后即可提问和回答</p>
    </div>
    <div class="card">
        <div class="auth-tabs">
            <button class="auth-tab active" data-mode="login">登录</button>
            <button class="auth-tab" data-mode="register">注册</button>
        </div>
        <div class="error-msg" id="errorMsg"></div>
        <form id="authForm" autocomplete="off">
            <div class="form-group">
                <label>联系方式</label>
                <div class="contact-options">
                    <div class="contact-opt active" data-type="phone">📱 手机号</div>
                    <div class="contact-opt" data-type="qq">💬 QQ号</div>
                    <div class="contact-opt" data-type="wechat">✉️ 微信号</div>
                </div>
                <input type="text" id="contactValue" placeholder="请输入手机号/QQ号/微信号" maxlength="100" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" placeholder="6-20位小写字母+数字" maxlength="20" autocomplete="off">
                <div style="font-size:0.8rem;color:#888;margin-top:4px">6-20位，限小写字母和数字</div>
            </div>
            <button type="submit" class="btn btn-primary btn-full" id="submitBtn">登录</button>
        </form>
        <div class="switch-link"><span id="switchText">还没有账号？<a id="switchBtn">去注册</a></span></div>
    </div>
</div>
<script src="/assets/js/app.js"></script>
<script>
(function(){
    let mode='login', ctype='phone';
    const form=document.getElementById('authForm'), btn=document.getElementById('submitBtn');
    const err=document.getElementById('errorMsg'), cv=document.getElementById('contactValue'), pw=document.getElementById('password');

    document.querySelectorAll('.auth-tab').forEach(t=>{
        t.addEventListener('click',function(){
            document.querySelectorAll('.auth-tab').forEach(x=>x.classList.remove('active'));
            this.classList.add('active'); mode=this.dataset.mode; updateUI();
        });
    });
    document.querySelectorAll('.contact-opt').forEach(t=>{
        t.addEventListener('click',function(){
            document.querySelectorAll('.contact-opt').forEach(x=>x.classList.remove('active'));
            this.classList.add('active'); ctype=this.dataset.type;
            cv.placeholder={'phone':'手机号','qq':'QQ号','wechat':'微信号'}[ctype];
        });
    });
    document.getElementById('switchBtn').addEventListener('click',()=>{
        document.querySelectorAll('.auth-tab').forEach(t=>t.classList.toggle('active',t.dataset.mode!==mode));
        mode=mode==='login'?'register':'login'; updateUI();
    });
    function updateUI(){
        btn.textContent=mode==='login'?'登录':'注册';
        document.getElementById('switchText').innerHTML=mode==='login'
           ?'还没有账号？<a id="switchBtn">去注册</a>'
            :'已有账号？<a id="switchBtn">去登录</a>';
        document.getElementById('switchBtn').addEventListener('click',arguments.callee);
        err.style.display='none';
    }

    form.addEventListener('submit',async e=>{
        e.preventDefault();
        const val=cv.value.trim(), pwd=pw.value.trim();
        if(!val){showErr('请输入联系方式');return}
        if(!pwd){showErr('请输入密码');return}
        if(!/^[a-z0-9]+$/.test(pwd)||pwd.length<6||pwd.length>20){showErr('密码需6-20位小写字母+数字');return}
        btn.disabled=true; btn.textContent='处理中...'; err.style.display='none';
        try{
            const fn=mode==='login'?AnonAsk.login:AnonAsk.register;
            const r=await fn(ctype,val,pwd);
            if(r.code===0){
                // 如果URL有redirect参数则跳转，否则去dashboard
                const redir=new URLSearchParams(location.search).get('redirect')||'/pages/dashboard.php';
                window.location.href=redir;
            }else{showErr(r.msg||'操作失败');btn.disabled=false;btn.textContent=mode==='login'?'登录':'注册'}
        }catch(e){showErr('网络错误');btn.disabled=false;btn.textContent=mode==='login'?'登录':'注册'}
    });
    function showErr(m){err.textContent=m;err.style.display='block'}
})();
</script>
</body>
</html>
