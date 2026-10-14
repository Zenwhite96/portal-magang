/* ==========================================================================
   MAIN — inisialisasi & event global
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    // Satu listener untuk seluruh tombol berawalan data-act="..." (event delegation).
    document.addEventListener('click', (e) => {
        if (e.target.matches('[data-modal-backdrop]')) return UI.closeModal();
        const el = e.target.closest('[data-act]');
        if (!el) return;
        const handler = Actions[el.dataset.act];
        if (!handler) return;
        e.preventDefault();
        handler(el, e);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (UI.$('#modal-root').firstChild) UI.closeModal();
        else if (UI.$('#sidebar')) Actions['nav-close']();
    });

    Auth.init();
    const saved = Auth.restore();
    if (saved) { App.user = saved; App.start(); }
    else Auth.showLogin();
});
