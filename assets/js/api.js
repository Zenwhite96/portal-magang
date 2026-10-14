/* ==========================================================================
   API — pembungkus fetch() GET & POST ke Google Apps Script
   Semua error dinormalisasi menjadi pesan yang bisa dipahami pengguna.
   ========================================================================== */
class ApiError extends Error {
    constructor(message, code) { super(message); this.name = 'ApiError'; this.code = code || 'ERROR'; }
}

const Api = (() => {
    const isConfigured = () => /^https?:\/\//i.test(CONFIG.SCRIPT_URL) && !/GANTI|YOUR_|URL_ANDA/i.test(CONFIG.SCRIPT_URL);

    async function request(url, options) {
        if (!isConfigured()) {
            throw new ApiError('SCRIPT_URL belum diisi dengan benar di assets/js/config.js.', 'CONFIG');
        }
        const ctrl = new AbortController();
        const timer = setTimeout(() => ctrl.abort(), CONFIG.TIMEOUT_MS);
        let res;
        try {
            res = await fetch(url, { ...options, signal: ctrl.signal, redirect: 'follow' });
        } catch (e) {
            throw new ApiError(e.name === 'AbortError'
                ? 'Server terlalu lama merespons. Coba lagi sebentar.'
                : 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.', 'NETWORK');
        } finally {
            clearTimeout(timer);
        }

        const text = await res.text();
        let json;
        try { json = JSON.parse(text); }
        catch (e) {
            throw new ApiError('Respons server bukan JSON. Pastikan deployment Apps Script: ' +
                '"Jalankan sebagai: Saya", "Akses: Siapa saja", lalu Deploy sebagai Versi baru.', 'NOT_JSON');
        }
        if (json.status !== 'ok') {
            // Backend lama (v1) tidak mengenal aksi baru -> beri petunjuk yang jelas.
            if (/Aksi tidak dikenali|Action tidak dikenali|Action POST tidak dikenali/i.test(json.message || '')) {
                throw new ApiError('Kode Apps Script di Google masih versi lama. Salin ulang backend/google-apps-script.js, ' +
                    'jalankan fungsi setup, lalu Terapkan → Kelola deployment → Versi baru.', 'OLD_BACKEND');
            }
            throw new ApiError(json.message || 'Terjadi kesalahan pada server.', 'SERVER');
        }
        return json;
    }

    /** GET  -> ?action=...&param=... */
    function get(action, params = {}) {
        const u = new URL(CONFIG.SCRIPT_URL);
        u.searchParams.set('action', action);
        Object.entries(params).forEach(([k, v]) => { if (v !== undefined && v !== null && v !== '') u.searchParams.set(k, v); });
        return request(u.toString(), { method: 'GET' });
    }

    /** POST -> body JSON (text/plain agar tidak memicu preflight CORS) */
    function post(action, data = {}) {
        return request(CONFIG.SCRIPT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'text/plain;charset=utf-8' },
            body: JSON.stringify({ action, ...data }),
        });
    }

    return { get, post, isConfigured };
})();
