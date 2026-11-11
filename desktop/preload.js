const { contextBridge, ipcRenderer } = require('electron');

// Expose safe native bridge to the renderer / webview
contextBridge.exposeInMainWorld('isNativeApp', true);
contextBridge.exposeInMainWorld('desktopAPI', {
    platform: 'windows',
    version: '1.0.0',
    changeStoreUrl: () => ipcRenderer.send('change-store-url'),
    printDirect: (options) => ipcRenderer.send('print-direct', options),
    reloadApp: () => ipcRenderer.send('reload-app'),
    saveStoreConfig: (url) => ipcRenderer.send('save-store-config', url),
    getSavedStoreConfig: () => ipcRenderer.invoke('get-saved-store-config')
});
