// 5种主题 CSS 变量定义
const THEMES = {
  light: {
    name: { zh: '极光白', en: 'Aurora White' },
    colors: {
      '--bg-primary': '#f0f4ff',
      '--bg-secondary': '#ffffff',
      '--bg-nav': 'rgba(255,255,255,0.85)',
      '--bg-navbar': '#ffffff',
      '--bg-card': '#ffffff',
      '--bg-hover': 'rgba(100,140,255,0.08)',
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
    }
  },
  dark: {
    name: { zh: '暗夜黑', en: 'Midnight Black' },
    colors: {
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
    }
  },
  blue: {
    name: { zh: '星河蓝', en: 'Galaxy Blue' },
    colors: {
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
    }
  },
  purple: {
    name: { zh: '幻境紫', en: 'Dream Purple' },
    colors: {
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
    }
  },
  pink: {
    name: { zh: '樱花粉', en: 'Sakura Pink' },
    colors: {
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
  }
};

const LANGUAGES = {
  zh: {
    nav: { back: '后退', forward: '前进', reload: '刷新', settings: '设置' },
    settings: {
      title: '设置',
      theme: '主题风格',
      language: '语言',
      about: '关于',
      version: '版本',
      back: '返回',
      themeLabels: {
        light: '极光白',
        dark: '暗夜黑',
        blue: '星河蓝',
        purple: '幻境紫',
        pink: '樱花粉'
      }
    },
    tooltip: {
      back: '后退',
      forward: '前进',
      reload: '刷新页面',
      settings: '打开设置'
    }
  },
  en: {
    nav: { back: 'Back', forward: 'Forward', reload: 'Reload', settings: 'Settings' },
    settings: {
      title: 'Settings',
      theme: 'Theme',
      language: 'Language',
      about: 'About',
      version: 'Version',
      back: 'Back',
      themeLabels: {
        light: 'Aurora White',
        dark: 'Midnight Black',
        blue: 'Galaxy Blue',
        purple: 'Dream Purple',
        pink: 'Sakura Pink'
      }
    },
    tooltip: {
      back: 'Go Back',
      forward: 'Go Forward',
      reload: 'Reload Page',
      settings: 'Open Settings'
    }
  }
};

if (typeof module !== 'undefined' && module.exports) {
  module.exports = { THEMES, LANGUAGES };
}
