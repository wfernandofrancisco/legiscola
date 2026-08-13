import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * Portal: Animate.css só corre quando ~10% da área do bloco entra no ecrã.
 * — Mantém `animate__animated` + `animate__*` no Blade; no 1º frame o JS remove-as temporariamente
 *   e aplica `.portal-animate-armed` (opacity 0). Ao intersectar ≥ threshold, volta a aplicar as classes
 *   (com reflow) para o efeito disparar.
 * — Alvos: secções directas (exceto a 1ª) + `.portal-animate-card` (evita duplicar se a secção-mãe já anima).
 */
function initPortalAnimateOnScroll() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const main = document.getElementById('portal-main');
    if (!main) {
        return;
    }

    if (main.dataset.portalAnimateInit === '1') {
        return;
    }
    main.dataset.portalAnimateInit = '1';

    const portalOpts =
        typeof window !== 'undefined' && window.__portalAnimate && typeof window.__portalAnimate === 'object'
            ? window.__portalAnimate
            : {};

    const threshold =
        typeof portalOpts.revealThreshold === 'number' &&
        Number.isFinite(portalOpts.revealThreshold) &&
        portalOpts.revealThreshold > 0 &&
        portalOpts.revealThreshold <= 1
            ? portalOpts.revealThreshold
            : 0.1;

    const rootMargin =
        typeof portalOpts.rootMargin === 'string' && portalOpts.rootMargin.trim() !== ''
            ? portalOpts.rootMargin.trim()
            : '0px';

    /** @param {HTMLElement} el */
    function collectAnimateClasses(el) {
        return Array.from(el.classList).filter(
            (c) => c === 'animate__animated' || (c.startsWith('animate__') && c !== 'animate__animated'),
        );
    }

    /** Área visível / área do elemento (0–1), aprox. igual ao ratio do IntersectionObserver com threshold */
    function visibleAreaRatio(el) {
        const r = el.getBoundingClientRect();
        const elArea = Math.max(1, r.width * r.height);
        const vh = window.innerHeight || 0;
        const vw = window.innerWidth || 0;
        const interH = Math.max(0, Math.min(r.bottom, vh) - Math.max(r.top, 0));
        const interW = Math.max(0, Math.min(r.right, vw) - Math.max(r.left, 0));

        return (interH * interW) / elArea;
    }

    /**
     * @param {HTMLElement} el
     * @returns {boolean} false = não precisa de scroll-reveal
     */
    function arm(el) {
        const fromClasses = collectAnimateClasses(el);
        if (fromClasses.length > 0) {
            el.dataset.portalAnimateClasses = JSON.stringify(fromClasses);
            fromClasses.forEach((c) => el.classList.remove(c));
            el.classList.add('portal-animate-armed');
            return true;
        }
        if (el.hasAttribute('data-animate')) {
            el.classList.add('portal-animate-armed');
            return true;
        }

        return false;
    }

    /** @param {HTMLElement} el */
    function disarm(el) {
        if (el.dataset.portalAnimateDone === '1') {
            return;
        }
        el.dataset.portalAnimateDone = '1';
        el.classList.remove('portal-animate-armed');

        const raw = el.dataset.portalAnimateClasses;
        if (raw) {
            let names = [];
            try {
                names = JSON.parse(raw);
            } catch {
                names = [];
            }
            delete el.dataset.portalAnimateClasses;
            void el.offsetWidth;
            names.forEach((c) => {
                if (typeof c === 'string' && c.length > 0) {
                    el.classList.add(c);
                }
            });
            return;
        }

        let name = el.getAttribute('data-animate') || 'fadeInUp';
        name = String(name).replace(/^animate__/, '').trim();
        if (!/^[\w-]+$/.test(name)) {
            name = 'fadeInUp';
        }
        void el.offsetWidth;
        el.classList.add('animate__animated', `animate__${name}`);
    }

    const sections = Array.from(
        main.querySelectorAll(':scope > section:not(:first-of-type):not(.no-portal-animate)'),
    );
    const sectionSet = new Set(sections);
    const cards = Array.from(main.querySelectorAll(':scope .portal-animate-card:not(.no-portal-animate)'));

    /** @type {HTMLElement[]} */
    const targets = [];
    sections.forEach((s) => targets.push(/** @type {HTMLElement} */ (s)));
    cards.forEach((c) => {
        const el = /** @type {HTMLElement} */ (c);
        const parentSection = el.closest('section');
        if (parentSection && sectionSet.has(parentSection)) {
            if (
                collectAnimateClasses(parentSection).length > 0 ||
                parentSection.classList.contains('animate__animated') ||
                parentSection.hasAttribute('data-animate')
            ) {
                return;
            }
        }
        targets.push(el);
    });

    const unique = [...new Set(targets)];

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                if (entry.intersectionRatio < threshold) {
                    return;
                }
                const el = /** @type {HTMLElement} */ (entry.target);
                disarm(el);
                io.unobserve(el);
            });
        },
        {
            threshold: [0, 0.05, 0.1, 0.15, 0.2, 0.25, 0.35, 0.5, 0.75, 1],
            rootMargin,
        },
    );

    unique.forEach((el) => {
        if (!arm(el)) {
            return;
        }
        if (visibleAreaRatio(el) >= threshold) {
            disarm(el);
            return;
        }
        io.observe(el);
    });
}

function schedulePortalAnimate() {
    requestAnimationFrame(() => {
        requestAnimationFrame(initPortalAnimateOnScroll);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', schedulePortalAnimate);
} else {
    schedulePortalAnimate();
}
