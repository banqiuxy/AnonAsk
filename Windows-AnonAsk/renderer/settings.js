// ============ 翻译文本 ============
const LANG = {
  zh: {
    title: '⚙ 设置',
    theme: '🎨 主题风格',
    lang: '🌐 语言 / Language',
    about: 'ℹ️ 关于',
    aboutApp: '应用',
    aboutVer: '版本',
    aboutUrl: '目标网站',
    appName: 'AnonAsk浏览器',
    appVer: '1.0.0',
    themeLabels: {
      light: '极光白',
      dark: '暗夜黑',
      blue: '星河蓝',
      purple: '幻境紫',
      pink: '樱花粉'
    }
  },
  en: {
    title: '⚙ Settings',
    theme: '🎨 Theme',
    lang: '🌐 Language',
    about: 'ℹ️ About',
    aboutApp: 'Application',
    aboutVer: 'Version',
    aboutUrl: 'Target URL',
    appName: 'AnonAsk',
    appVer: '1.0.0',
    themeLabels: {
      light: 'Aurora White',
      dark: 'Midnight Black',
      blue: 'Galaxy Blue',
      purple: 'Dream Purple',
      pink: 'Sakura Pink'
    }
  }
};

// ============ 主题 CSS 变量映射 ============
const THEMES = {
  light: {
    '--bg-primary': '#f0f4ff',
    '--bg-secondary': '#ffffff',
    '--bg-navbar': '#ffffff',
    '--bg-card': '#ffffff',
    '--bg-hover': 'rgba(74,124,255,0.08)',
    '--bg-settings': '#f0f4ff',
    '--text-primary': '#1a1a2e',
    '--text-secondary': '#5a5a7a',
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
    '--bg-navbar': '#0d0d24',
    '--bg-card': '#16163a',
    '--bg-hover': 'rgba(100,140,255,0.12)',
    '--bg-settings': '#0a0a1a',
    '--text-primary': '#e8e8ff',
    '--text-secondary': '#8888bb',
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
    '--bg-navbar': '#0d1e3a',
    '--bg-card': '#122a50',
    '--bg-hover': 'rgba(50,150,255,0.12)',
    '--bg-settings': '#0a1628',
    '--text-primary': '#d0e8ff',
    '--text-secondary': '#7090c0',
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
    '--bg-navbar': '#1e0e3e',
    '--bg-card': '#2e1860',
    '--bg-hover': 'rgba(180,80,255,0.12)',
    '--bg-settings': '#1a0a2e',
    '--text-primary': '#e8d0ff',
    '--text-secondary': '#a080c8',
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
    '--bg-navbar': '#320e22',
    '--bg-card': '#4a1a38',
    '--bg-hover': 'rgba(255,80,160,0.12)',
    '--bg-settings': '#2a0a1a',
    '--text-primary': '#ffd0e8',
    '--text-secondary': '#c080a8',
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

// ============ 状态 ============
let currentSettings = { theme: 'light', language: 'zh' };
let currentLang = 'zh';

// ============ DOM 引用 ============
const themeCards = document.querySelectorAll('.theme-card');
const langBtns = document.querySelectorAll('.lang-btn');
const btnClose = document.getElementById('btnClose');
const settingsTitle = document.getElementById('settingsTitle');

// ============ 应用主题到设置窗口 ============
function applyThemeToSettings(themeName) {
  const vars = THEMES[themeName] || THEMES.light;
  const root = document.documentElement;
  Object.entries(vars).forEach(([key, val]) => {
    root.style.setProperty(key, val);
  });
  document.body.style.backgroundColor = vars['--bg-settings'];
}

// ============ 应用语言 ============
function applyLanguage(lang) {
  currentLang = lang;
  const t = LANG[lang] || LANG.zh;

  // 标题
  settingsTitle.textContent = t.title;

  // Section 标题
  document.getElementById('sectionTheme').textContent = t.theme;
  document.getElementById('sectionLang').textContent = t.lang;
  document.getElementById('sectionAbout').textContent = t.about;

  // 主题标签
  document.getElementById('themeLabelLight').textContent = t.themeLabels.light;
  document.getElementById('themeLabelDark').textContent = t.themeLabels.dark;
  document.getElementById('themeLabelBlue').textContent = t.themeLabels.blue;
  document.getElementById('themeLabelPurple').textContent = t.themeLabels.purple;
  document.getElementById('themeLabelPink').textContent = t.themeLabels.pink;

  // 关于
  document.getElementById('aboutApp').textContent = t.aboutApp;
  document.getElementById('aboutVer').textContent = t.aboutVer;
  document.querySelectorAll('.about-value')[0].textContent = t.appName;
  document.querySelectorAll('.about-value')[1].textContent = t.appVer;
}

// ============ 更新 UI 选中状态 ============
function updateSelection(theme, lang) {
  // 主题
  themeCards.forEach(card => {
    const isSelected = card.dataset.theme === theme;
    card.classList.toggle('selected', isSelected);
  });

  // 语言
  langBtns.forEach(btn => {
    const isSelected = btn.dataset.lang === lang;
    btn.classList.toggle('selected', isSelected);
  });
}

// ============ 保存设置 ============
async function saveAndApply(newSettings) {
  try {
    // 合并设置
    currentSettings = { ...currentSettings, ...newSettings };
    // 保存到主进程
    const saved = await window.electronAPI.saveSettings(currentSettings);
    currentSettings = saved;

    // 本地应用
    if (newSettings.theme) {
      applyThemeToSettings(newSettings.theme);
    }
    if (newSettings.language) {
      applyLanguage(newSettings.language);
    }
    updateSelection(currentSettings.theme, currentSettings.language);
  } catch (e) {
    console.error('保存设置失败:', e);
  }
}

// ============ 事件绑定 ============

// 关闭按钮
btnClose.addEventListener('click', () => {
  window.electronAPI.closeSettings();
});

// 点击主题卡片
themeCards.forEach(card => {
  card.addEventListener('click', () => {
    const theme = card.dataset.theme;
    if (theme && theme !== currentSettings.theme) {
      saveAndApply({ theme });
    }
  });
});

// 点击语言按钮
langBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const lang = btn.dataset.lang;
    if (lang && lang !== currentSettings.language) {
      saveAndApply({ language: lang });
    }
  });
});

// ============ 键盘快捷键 ============
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    window.electronAPI.closeSettings();
  }
});

// ============ 监听外部设置变更（如其他窗口修改） ============
window.electronAPI.onSettingsChanged((newSettings) => {
  if (newSettings.theme && newSettings.theme !== currentSettings.theme) {
    applyThemeToSettings(newSettings.theme);
  }
  if (newSettings.language && newSettings.language !== currentSettings.language) {
    applyLanguage(newSettings.language);
  }
  currentSettings = newSettings;
  updateSelection(currentSettings.theme, currentSettings.language);
});

// ============ 初始化 ============
async function init() {
  try {
    const settings = await window.electronAPI.getSettings();
    currentSettings = settings;

    // 应用主题
    applyThemeToSettings(settings.theme || 'light');

    // 应用语言
    applyLanguage(settings.language || 'zh');

    // 更新选中状态
    updateSelection(settings.theme || 'light', settings.language || 'zh');
  } catch (e) {
    console.error('初始化设置页失败:', e);
    // 默认主题
    applyThemeToSettings('light');
  }
}

init();
