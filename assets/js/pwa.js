(function () {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', function () {
        const root = window.APP_ROOT || '';
        navigator.serviceWorker.register(root + '/sw.js').catch(function () {
            // Service worker registration failed silently on unsupported setups.
        });
    });

    let deferredPrompt = null;
    const installBanner = document.getElementById('pwaInstallBanner');
    const installButton = document.getElementById('pwaInstallButton');
    const dismissButton = document.getElementById('pwaInstallDismiss');

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;

        if (installBanner) {
            installBanner.classList.add('show');
        }
    });

    if (installButton) {
        installButton.addEventListener('click', function () {
            if (!deferredPrompt) {
                return;
            }

            deferredPrompt.prompt();
            deferredPrompt.userChoice.finally(function () {
                deferredPrompt = null;
                if (installBanner) {
                    installBanner.classList.remove('show');
                }
            });
        });
    }

    if (dismissButton && installBanner) {
        dismissButton.addEventListener('click', function () {
            installBanner.classList.remove('show');
        });
    }

    window.addEventListener('appinstalled', function () {
        if (installBanner) {
            installBanner.classList.remove('show');
        }
        deferredPrompt = null;
    });
})();
