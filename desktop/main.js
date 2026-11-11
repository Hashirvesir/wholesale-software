const { app, BrowserWindow, ipcMain, Menu, shell, dialog } = require('electron');
const path = require('path');
const fs = require('fs');

let mainWindow = null;
const configFile = path.join(app.getPath('userData'), 'invenco-store-config.json');

function getSavedStoreUrl() {
    try {
        if (fs.existsSync(configFile)) {
            const data = JSON.parse(fs.readFileSync(configFile, 'utf8'));
            return data.storeUrl || null;
        }
    } catch (e) {
        console.error('Error reading config file:', e);
    }
    return null;
}

function saveStoreUrl(url) {
    try {
        fs.writeFileSync(configFile, JSON.stringify({ storeUrl: url }, null, 2), 'utf8');
    } catch (e) {
        console.error('Error saving config file:', e);
    }
}

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1366,
        height: 768,
        minWidth: 1024,
        minHeight: 600,
        title: 'Invenco Wholesaler Software',
        icon: path.join(__dirname, 'icon.png'),
        autoHideMenuBar: true,
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            nodeIntegration: false,
            contextIsolation: true,
            spellcheck: false,
            enableRemoteModule: false
        }
    });

    const savedUrl = getSavedStoreUrl();
    if (savedUrl) {
        loadStore(savedUrl);
    } else {
        mainWindow.loadFile(path.join(__dirname, 'setup.html'));
    }

    createAppMenu();

    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        // Open external links in default browser
        if (url.startsWith('https://wa.me') || url.startsWith('http://wa.me')) {
            shell.openExternal(url);
            return { action: 'deny' };
        }
        return { action: 'allow' };
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

function loadStore(url) {
    mainWindow.loadURL(url).catch((err) => {
        console.error('Failed to load store URL:', err);
        dialog.showMessageBox(mainWindow, {
            type: 'error',
            title: 'Connection Error',
            message: `Could not reach ${url}.\nPlease check your internet connection or store domain.`,
            buttons: ['Try Again', 'Change Store URL']
        }).then(({ response }) => {
            if (response === 0) {
                loadStore(url);
            } else {
                mainWindow.loadFile(path.join(__dirname, 'setup.html'));
            }
        });
    });
}

function createAppMenu() {
    const template = [
        {
            label: 'File',
            submenu: [
                {
                    label: 'Switch Store / Change Domain',
                    accelerator: 'CmdOrCtrl+Shift+S',
                    click: () => {
                        if (mainWindow) {
                            mainWindow.loadFile(path.join(__dirname, 'setup.html'));
                        }
                    }
                },
                { type: 'separator' },
                {
                    label: 'Reload Page',
                    accelerator: 'CmdOrCtrl+R',
                    click: () => {
                        if (mainWindow) mainWindow.reload();
                    }
                },
                {
                    label: 'Hard Reload (Clear Cache)',
                    accelerator: 'CmdOrCtrl+Shift+R',
                    click: () => {
                        if (mainWindow) mainWindow.webContents.reloadIgnoringCache();
                    }
                },
                { type: 'separator' },
                { role: 'quit' }
            ]
        },
        {
            label: 'View',
            submenu: [
                { role: 'resetZoom' },
                { role: 'zoomIn' },
                { role: 'zoomOut' },
                { type: 'separator' },
                { role: 'togglefullscreen' },
                {
                    label: 'Toggle Developer Tools',
                    accelerator: 'F12',
                    click: () => {
                        if (mainWindow) mainWindow.webContents.toggleDevTools();
                    }
                }
            ]
        }
    ];

    const menu = Menu.buildFromTemplate(template);
    Menu.setApplicationMenu(menu);
}

// IPC Handlers
ipcMain.handle('get-saved-store-config', () => {
    return getSavedStoreUrl();
});

ipcMain.on('save-store-config', (event, url) => {
    saveStoreUrl(url);
    if (mainWindow) {
        loadStore(url);
    }
});

ipcMain.on('change-store-url', () => {
    if (mainWindow) {
        mainWindow.loadFile(path.join(__dirname, 'setup.html'));
    }
});

ipcMain.on('reload-app', () => {
    if (mainWindow) {
        mainWindow.reload();
    }
});

ipcMain.on('print-direct', (event, options) => {
    if (mainWindow) {
        mainWindow.webContents.print(options || { silent: true, printBackground: true });
    }
});

app.whenReady().then(() => {
    createWindow();

    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) {
            createWindow();
        }
    });
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});
