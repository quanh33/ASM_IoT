const { app, BrowserWindow } = require("electron");

app.whenReady().then(() => {
    const win = new BrowserWindow({
        width: 800,
        height: 800,
        webPreferences: {
            nodeIntegration: true,
        },
    });

    win.loadURL("http://localhost:8000"); // Laravel server URL
});
