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

function isMobileViewport() {
    return window.matchMedia('(max-width: 1023px)').matches || /android|iphone|ipad|ipod/i.test(navigator.userAgent);
}

const COOKIE_KEY = 'legiscola_lgpd_cookie_v1';

/** Evita empilhar o aviso PWA por cima do banner de cookies. */
function whenCookiesSettled(callback) {
    if (localStorage.getItem(COOKIE_KEY)) {
        callback();
        return;
    }

    const banner = document.getElementById('lgpd-cookie-banner');
    if (!banner || banner.classList.contains('hidden')) {
        // Banner ainda não montado ou já oculto — libera após um tick.
        setTimeout(callback, 500);
        return;
    }

    const observer = new MutationObserver(() => {
        if (localStorage.getItem(COOKIE_KEY) || banner.classList.contains('hidden')) {
            observer.disconnect();
            callback();
        }
    });
    observer.observe(banner, { attributes: true, attributeFilter: ['class'] });
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
        const soft = root?.getAttribute('data-pwa-soft') === '1';
        const storageKey = `legiscola-pwa-install-dismissed:v2:${area}`;
        const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        const dismissed = localStorage.getItem(storageKey) === '1';
        const title = root?.getAttribute('data-pwa-title') || 'Instale o aplicativo';
        const hintDefault = root?.getAttribute('data-pwa-hint') || 'Abra pelo ícone na tela inicial.';
        const hintIos = root?.getAttribute('data-pwa-hint-ios') || hintDefault;

        return {
            deferredPrompt: null,
            canPrompt: false,
            visible: false,
            showGuide: false,
            isIos,
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
                    this.showGuide = false;
                });

                window.addEventListener('appinstalled', () => {
                    this.visible = false;
                    localStorage.setItem(storageKey, '1');
                });

                // Portal: mostra convite no celular mesmo sem beforeinstallprompt ainda.
                // iOS / outras áreas: mantém o atraso para a dica de instalação.
                const shouldSoftShow = soft ? isMobileViewport() : isIos;
                if (shouldSoftShow) {
                    const reveal = () => {
                        setTimeout(() => {
                            if (!isStandaloneDisplay() && localStorage.getItem(storageKey) !== '1') {
                                this.visible = true;
                            }
                        }, soft ? 800 : 1800);
                    };

                    if (soft) {
                        whenCookiesSettled(reveal);
                    } else {
                        reveal();
                    }
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
            async installOrGuide() {
                if (this.canPrompt && this.deferredPrompt) {
                    await this.install();
                    return;
                }
                this.showGuide = true;
            },
            dismiss() {
                this.visible = false;
                this.showGuide = false;
                localStorage.setItem(storageKey, '1');
            },
        };
    });
}
