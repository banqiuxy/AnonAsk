const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
  // 设置相关
  getSettings: () => ipcRenderer.invoke('get-settings'),
  saveSettings: (s) => ipcRenderer.invoke('save-settings', s),
  openSettings: () => ipcRenderer.invoke('open-settings'),
  closeSettings: () => ipcRenderer.invoke('close-settings'),

  // 监听设置变更（主窗口和设置窗口都监听）
  onSettingsChanged: (callback) => {
    ipcRenderer.on('settings-changed', (_e, s) => callback(s));
  }
});
