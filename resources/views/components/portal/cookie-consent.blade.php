@props([])

<div id="lgpd-cookie-banner" class="fixed inset-x-0 bottom-0 z-[100] hidden px-3 pb-3 sm:px-4" role="dialog" aria-labelledby="lgpd-cookie-title" aria-live="polite">
    <div class="mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-2xl backdrop-blur-md dark:border-slate-700 dark:bg-slate-900/95">
        <h2 id="lgpd-cookie-title" class="text-sm font-bold text-slate-900 dark:text-white">Cookies e dados (LGPD)</h2>
        <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
            Utilizamos cookies <span class="font-semibold">necessários</span> ao funcionamento seguro do site (sessão, preferências básicas, proteção CSRF).
            Com o seu consentimento, podemos usar cookies <span class="font-semibold">opcionais</span> para estatísticas de utilização.
            Ao continuar, você confirma que leu a nossa
            <a href="{{ route('portal.privacidade') }}" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary,#2563eb)">política de privacidade</a>.
            Base legal: execução de serviços e consentimento, conforme Lei 13.709/2018.
        </p>
        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-end">
            <button type="button" id="lgpd-cookie-necessary" class="rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800">
                Apenas necessários
            </button>
            <button type="button" id="lgpd-cookie-all" class="rounded-full px-4 py-2 text-xs font-semibold text-white shadow-md hover:opacity-95" style="background:linear-gradient(135deg,var(--portal-primary,#2563eb),var(--portal-secondary,#1e3a8a))">
                Aceitar todos
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var KEY = 'legiscola_lgpd_cookie_v1';
    var banner = document.getElementById('lgpd-cookie-banner');
    if (!banner) return;
    try {
        if (localStorage.getItem(KEY)) return;
    } catch (e) { return; }
    banner.classList.remove('hidden');

    function save(prefs) {
        try {
            localStorage.setItem(KEY, JSON.stringify(prefs));
        } catch (e) {}
        banner.classList.add('hidden');
    }

    document.getElementById('lgpd-cookie-necessary').addEventListener('click', function () {
        save({ necessary: true, analytics: false, at: new Date().toISOString() });
    });
    document.getElementById('lgpd-cookie-all').addEventListener('click', function () {
        save({ necessary: true, analytics: true, at: new Date().toISOString() });
    });
})();
</script>
