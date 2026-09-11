/**
 * PWA compartilhada: service worker + banner de instalação.
 * Ativa em qualquer página com [data-pwa] (aluno, diretor, professor, portal).
 */

function isPwaPage() {
    return Boolean(document.querySelector('[data-pwa]'));
}

function isStandaloneDisplay() {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true
    );
}

function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Sem SW o site continua normal.
        });
    });
}

/**
 * @param {import('alpinejs').Alpine} Alpine
 */
export function registerAlunoPwa(Alpine) {
    // Nome legado exportado em app.js; registra a PWA em qualquer área marcada.
    registerLegiscolaPwa(Alpine);
}

/**
 * @param {import('alpinejs').Alpine} Alpine
 */
export function registerLegiscolaPwa(Alpine) {
    if (!isPwaPage()) {
        return;
    }

    registerServiceWorker();

    Alpine.data('legiscolaPwaInstall', () => {
        const root = document.querySelector('[data-pwa]');
        const area = root?.getAttribute('data-pwa-area') || 'aluno';
        const storageKey = `legiscola-pwa-install-dismissed:${area}`;
        const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        const dismissed = localStorage.getItem(storageKey) === '1';
        const title = root?.getAttribute('data-pwa-title') || 'Instale o aplicativo';
        const hintDefault = root?.getAttribute('data-pwa-hint') || 'Abra pelo ícone na tela inicial.';
        const hintIos = root?.getAttribute('data-pwa-hint-ios') || hintDefault;

        return {
            deferredPrompt: null,
            canPrompt: false,
            visible: false,
            title,
            hint: isIos ? hintIos : hintDefault,
            init() {
                if (isStandaloneDisplay() || dismissed) {
                    return;
                }

                window.addEventListener('beforeinstallprompt', (event) => {
                    event.preventDefault();
                    this.deferredPrompt = event;
                    this.canPrompt = true;
                    this.visible = true;
                });

                if (isIos) {
                    setTimeout(() => {
                        if (!isStandaloneDisplay() && localStorage.getItem(storageKey) !== '1') {
                            this.visible = true;
                        }
                    }, 1800);
                }
            },
            async install() {
                if (!this.deferredPrompt) {
                    return;
                }
                this.deferredPrompt.prompt();
                await this.deferredPrompt.userChoice;
                this.deferredPrompt = null;
                this.visible = false;
                localStorage.setItem(storageKey, '1');
            },
            dismiss() {
                this.visible = false;
                localStorage.setItem(storageKey, '1');
            },
        };
    });
}
