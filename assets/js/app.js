const AnonAsk = (() => {

    async function checkLogin() {
        try {
            const r = await fetch('/api/auth.php?action=check');
            const j = await r.json();
            return j.code === 0 && j.data && j.data.uid ? j.data.uid : null;
        } catch(e) { return null; }
    }

    async function login(contactType, contactValue, password) {
        const r = await fetch('/api/auth.php?action=login', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({contact_type:contactType, contact_value:contactValue, password})
        });
        return r.json();
    }

    async function register(contactType, contactValue, password) {
        const r = await fetch('/api/auth.php?action=register', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({contact_type:contactType, contact_value:contactValue, password})
        });
        return r.json();
    }

    async function logout() {
        const r = await fetch('/api/auth.php?action=logout', {method:'POST'});
        return r.json();
    }

    async function api(method, url, body=null) {
        const headers = {};
        if (body) headers['Content-Type'] = 'application/json';
        const r = await fetch(url, {method, headers, body: body ? JSON.stringify(body) : null});
        return r.json();
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    async function copyText(text) {
        try { await navigator.clipboard.writeText(text); return true; }
        catch(e) {
            const ta = document.createElement('textarea');
            ta.value = text; ta.style.position='fixed'; ta.style.left='-9999px';
            document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove();
            return true;
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        const p = n => String(n).padStart(2,'0');
        return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`;
    }

    return { checkLogin, login, register, logout, api, escapeHtml, copyText, formatDate };
})();

const date = new Date();
document.querySelector('.copyright').innerHTML += `<br> Copyright © ${date.getFullYear()} 半秋. All rights reserved.`;