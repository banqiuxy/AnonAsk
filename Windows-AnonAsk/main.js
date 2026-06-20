const { app, BrowserWindow, ipcMain, session, Menu } = require('electron');
const path = require('path');
const fs = require('fs');

const SETTINGS_PATH = path.join(app.getPath('userData'), 'settings.json');

let mainWindow = null;
let settingsWindow = null;

// 默认设置
let settings = {
  theme: 'light',
  language: 'zh'
};

// 加载保存的设置
function loadSettings() {
  try {
    if (fs.existsSync(SETTINGS_PATH)) {
      const data = fs.readFileSync(SETTINGS_PATH, 'utf-8');
      settings = { ...settings, ...JSON.parse(data) };
    }
  } catch (e) {
    console.error('加载设置失败:', e);
  }
}

// 保存设置
function saveSettings(newSettings) {
  settings = { ...settings, ...newSettings };
  try {
    fs.writeFileSync(SETTINGS_PATH, JSON.stringify(settings, null, 2), 'utf-8');
  } catch (e) {
    console.error('保存设置失败:', e);
  }
}

function createMainWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    minWidth: 800,
    minHeight: 600,
    frame: true,
    titleBarStyle: 'default',
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false,
      webviewTag: true
    },
    backgroundColor: '#f0f4ff',
    show: false,
    icon: path.join(__dirname, 'assets', 'icon.png')
  });

  mainWindow.loadFile(path.join(__dirname, 'renderer', 'index.html'));

  mainWindow.once('ready-to-show', () => {
    mainWindow.show();
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
    if (settingsWindow) {
      settingsWindow.close();
      settingsWindow = null;
    }
  });
}

function createSettingsWindow() {
  if (settingsWindow) {
    settingsWindow.focus();
    return;
  }

  settingsWindow = new BrowserWindow({
    width: 520,
    height: 620,
    parent: mainWindow,
    modal: false,
    resizable: false,
    frame: false,
    transparent: false,
    backgroundColor: settings.theme === 'light' ? '#f0f4ff' : '#0a0a1a',
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false
    },
    show: false
  });

  settingsWindow.loadFile(path.join(__dirname, 'renderer', 'settings.html'));

  settingsWindow.once('ready-to-show', () => {
    settingsWindow.show();
  });

  settingsWindow.on('closed', () => {
    settingsWindow = null;
  });
}

// IPC 处理
ipcMain.handle('get-settings', () => {
  return settings;
});

ipcMain.handle('save-settings', (event, newSettings) => {
  saveSettings(newSettings);
  // 通知所有窗口设置已变更
  const allWindows = BrowserWindow.getAllWindows();
  allWindows.forEach(win => {
    if (!win.isDestroyed()) {
      win.webContents.send('settings-changed', settings);
    }
  });
  return settings;
});

ipcMain.handle('open-settings', () => {
  createSettingsWindow();
  return true;
});

ipcMain.handle('close-settings', () => {
  if (settingsWindow && !settingsWindow.isDestroyed()) {
    settingsWindow.close();
  }
  return true;
});

ipcMain.handle('navigate-back', () => {
  return { canGoBack: false, canGoForward: false };
});

ipcMain.handle('navigate-forward', () => {
  return { canGoBack: false, canGoForward: false };
});

app.whenReady().then(() => {
  loadSettings();
  // 移除默认菜单栏 (File/Edit/View/Window)
  Menu.setApplicationMenu(null);
  createMainWindow();

  // CSP 策略允许加载外部资源
  session.defaultSession.webRequest.onHeadersReceived((details, callback) => {
    callback({
      responseHeaders: {
        ...details.responseHeaders,
        'Content-Security-Policy': [
          "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: http: data: blob:; " +
          "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: http:; " +
          "style-src 'self' 'unsafe-inline' https: http:; " +
          "img-src 'self' data: https: http: blob:; " +
          "font-src 'self' data: https: http:; " +
          "frame-src 'self' https: http:;"
        ]
      }
    });
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createMainWindow();
  }
});
