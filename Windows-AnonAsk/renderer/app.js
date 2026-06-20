// ============ DOM 引用 ============
const webview = document.getElementById('mainWebview');
const btnBack = document.getElementById('btnBack');
const btnForward = document.getElementById('btnForward');
const btnReload = document.getElementById('btnReload');
const btnSettings = document.getElementById('btnSettings');
const loadIndicator = document.getElementById('loadIndicator');
const pageTitle = document.getElementById('pageTitle');
const loadingOverlay = document.getElementById('loadingOverlay');

// ============ 应用主题 ============
function applyTheme(themeName) {
  const themes = {
    light: {
      '--bg-primary': '#f0f4ff',
      '--bg-secondary': '#ffffff',
      '--bg-nav': 'rgba(255,255,255,0.85)',
      '--bg-navbar': '#ffffff',
      '--bg-card': '#ffffff',
      '--bg-hover': 'rgba(74,124,255,0.08)',
      '--bg-settings': '#f0f4ff',
      '--text-primary': '#1a1a2e',
      '--text-secondary': '#5a5a7a',
      '--text-nav': '#1a1a2e',
      '--accent': '#4a7cff',
      '--accent-hover': '#3366ee',
      '--accent-glow': 'rgba(74,124,255,0.3)',
      '--border': 'rgba(74,124,255,0.15)',
      '--shadow': 'rgba(74,124,255,0.1)',
      '--nav-shadow': '0 2px 20px rgba(74,124,255,0.12)',
      '--card-shadow': '0 8px 32px rgba(74,124,255,0.08)',
      '--theme-btn-border': '#4a7cff',
      '--theme-btn-selected': 'rgba(74,124,255,0.2)',
      '--theme-btn-selected-border': '#4a7cff',
    },
    dark: {
      '--bg-primary': '#0a0a1a',
      '--bg-secondary': '#12122a',
      '--bg-nav': 'rgba(10,10,30,0.92)',
      '--bg-navbar': '#0d0d24',
      '--bg-card': '#16163a',
      '--bg-hover': 'rgba(100,140,255,0.12)',
      '--bg-settings': '#0a0a1a',
      '--text-primary': '#e8e8ff',
      '--text-secondary': '#8888bb',
      '--text-nav': '#e8e8ff',
      '--accent': '#6a8cff',
      '--accent-hover': '#5577ee',
      '--accent-glow': 'rgba(106,140,255,0.35)',
      '--border': 'rgba(106,140,255,0.2)',
      '--shadow': 'rgba(0,0,0,0.4)',
      '--nav-shadow': '0 2px 20px rgba(0,0,0,0.4)',
      '--card-shadow': '0 8px 32px rgba(0,0,0,0.3)',
      '--theme-btn-border': '#6a8cff',
      '--theme-btn-selected': 'rgba(106,140,255,0.25)',
      '--theme-btn-selected-border': '#6a8cff',
    },
    blue: {
      '--bg-primary': '#0a1628',
      '--bg-secondary': '#0f2240',
      '--bg-nav': 'rgba(10,22,48,0.92)',
      '--bg-navbar': '#0d1e3a',
      '--bg-card': '#122a50',
      '--bg-hover': 'rgba(50,150,255,0.12)',
      '--bg-settings': '#0a1628',
      '--text-primary': '#d0e8ff',
      '--text-secondary': '#7090c0',
      '--text-nav': '#d0e8ff',
      '--accent': '#3a9eff',
      '--accent-hover': '#2288ee',
      '--accent-glow': 'rgba(58,158,255,0.35)',
      '--border': 'rgba(58,158,255,0.2)',
      '--shadow': 'rgba(0,20,60,0.5)',
      '--nav-shadow': '0 2px 20px rgba(0,20,60,0.5)',
      '--card-shadow': '0 8px 32px rgba(0,20,60,0.4)',
      '--theme-btn-border': '#3a9eff',
      '--theme-btn-selected': 'rgba(58,158,255,0.25)',
      '--theme-btn-selected-border': '#3a9eff',
    },
    purple: {
      '--bg-primary': '#1a0a2e',
      '--bg-secondary': '#24104a',
      '--bg-nav': 'rgba(26,10,46,0.92)',
      '--bg-navbar': '#1e0e3e',
      '--bg-card': '#2e1860',
      '--bg-hover': 'rgba(180,80,255,0.12)',
      '--bg-settings': '#1a0a2e',
      '--text-primary': '#e8d0ff',
      '--text-secondary': '#a080c8',
      '--text-nav': '#e8d0ff',
      '--accent': '#a050ff',
      '--accent-hover': '#8844ee',
      '--accent-glow': 'rgba(160,80,255,0.35)',
      '--border': 'rgba(160,80,255,0.2)',
      '--shadow': 'rgba(30,0,60,0.5)',
      '--nav-shadow': '0 2px 20px rgba(30,0,60,0.5)',
      '--card-shadow': '0 8px 32px rgba(30,0,60,0.4)',
      '--theme-btn-border': '#a050ff',
      '--theme-btn-selected': 'rgba(160,80,255,0.25)',
      '--theme-btn-selected-border': '#a050ff',
    },
    pink: {
      '--bg-primary': '#2a0a1a',
      '--bg-secondary': '#3a1028',
      '--bg-nav': 'rgba(42,10,26,0.92)',
      '--bg-navbar': '#320e22',
      '--bg-card': '#4a1a38',
      '--bg-hover': 'rgba(255,80,160,0.12)',
      '--bg-settings': '#2a0a1a',
      '--text-primary': '#ffd0e8',
      '--text-secondary': '#c080a8',
      '--text-nav': '#ffd0e8',
      '--accent': '#ff5090',
      '--accent-hover': '#ee4480',
      '--accent-glow': 'rgba(255,80,144,0.35)',
      '--border': 'rgba(255,80,144,0.2)',
      '--shadow': 'rgba(40,0,20,0.5)',
      '--nav-shadow': '0 2px 20px rgba(40,0,20,0.5)',
      '--card-shadow': '0 8px 32px rgba(40,0,20,0.4)',
      '--theme-btn-border': '#ff5090',
      '--theme-btn-selected': 'rgba(255,80,144,0.25)',
      '--theme-btn-selected-border': '#ff5090',
    }
  };

  const vars = themes[themeName] || themes.light;
  const root = document.documentElement;
  Object.entries(vars).forEach(([key, val]) => {
    root.style.setProperty(key, val);
  });

  // 更新 body 背景
  document.body.style.backgroundColor = vars['--bg-primary'];
}

// ============ 更新导航按钮状态 ============
function updateNavButtons() {
  try {
    btnBack.disabled = !webview.canGoBack();
    btnForward.disabled = !webview.canGoForward();
  } catch (e) {
    // webview 还未加载完成
  }
}

// ============ 加载状态管理 ============
let loadTimeout = null;

function showLoading() {
  loadIndicator.classList.add('active');
  clearTimeout(loadTimeout);
  loadTimeout = setTimeout(() => {
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.opacity = '1';
  }, 3000); // 3秒还没加载完显示遮罩
}

function hideLoading() {
  loadIndicator.classList.remove('active');
  clearTimeout(loadTimeout);
  loadingOverlay.style.display = 'none';
  loadingOverlay.style.opacity = '0';
}

// ============ Webview 事件绑定 ============
webview.addEventListener('did-start-loading', () => {
  showLoading();
  updateNavButtons();
});

webview.addEventListener('did-stop-loading', () => {
  hideLoading();
  updateNavButtons();
});

webview.addEventListener('page-title-updated', (e) => {
  pageTitle.textContent = e.title || 'banqiuxy.top';
});

webview.addEventListener('did-navigate', () => {
  updateNavButtons();
});

webview.addEventListener('did-navigate-in-page', () => {
  updateNavButtons();
});

webview.addEventListener('new-window', (e) => {
  // 拦截新窗口, 在当前 webview 打开
  e.preventDefault();
  try {
    webview.loadURL(e.url);
  } catch (err) {
    console.warn('无法加载:', e.url);
  }
});

// ============ 导航按钮事件 ============
btnBack.addEventListener('click', () => {
  if (webview.canGoBack()) webview.goBack();
});

btnForward.addEventListener('click', () => {
  if (webview.canGoForward()) webview.goForward();
});

btnReload.addEventListener('click', () => {
  webview.reload();
});

// ============ 设置按钮 ============
btnSettings.addEventListener('click', async () => {
  const btn = btnSettings;
  btn.style.transform = 'scale(0.9)';
  setTimeout(() => { btn.style.transform = ''; }, 200);

  try {
    await window.electronAPI.openSettings();
  } catch (e) {
    console.error('打开设置失败:', e);
  }
});

// ============ 快捷键 ============
document.addEventListener('keydown', (e) => {
  // Ctrl/Cmd + [ = 后退
  if ((e.ctrlKey || e.metaKey) && e.key === '[') {
    e.preventDefault();
    if (webview.canGoBack()) webview.goBack();
  }
  // Ctrl/Cmd + ] = 前进
  if ((e.ctrlKey || e.metaKey) && e.key === ']') {
    e.preventDefault();
    if (webview.canGoForward()) webview.goForward();
  }
  // Ctrl/Cmd + R = 刷新 (阻止 webview 自己的刷新触发两次)
  if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
    e.preventDefault();
    webview.reload();
  }
  // Escape = 如果设置窗口开着则关掉
  if (e.key === 'Escape') {
    window.electronAPI.closeSettings().catch(() => {});
  }
  // , = 打开设置 (Cmd/Ctrl + ,)
  if ((e.ctrlKey || e.metaKey) && e.key === ',') {
    e.preventDefault();
    window.electronAPI.openSettings().catch(() => {});
  }
});

// ============ 初始化 ============
async function init() {
  try {
    const settings = await window.electronAPI.getSettings();
    if (settings.theme) {
      applyTheme(settings.theme);
    }
  } catch (e) {
    console.error('初始化设置失败:', e);
  }

  // 监听设置变更（来自设置窗口的保存）
  window.electronAPI.onSettingsChanged((newSettings) => {
    if (newSettings.theme) {
      applyTheme(newSettings.theme);
    }
  });

  // webview 初始加载
  updateNavButtons();
}

// 等待 webview 元素加载完成
if (webview) {
  // Electron 的 webview 在 DOM 中就绪
  init();
} else {
  document.addEventListener('DOMContentLoaded', init);
}
