/* ==========================================================================
   AUTH — login tanpa password (sistem key)
     DOSEN...  -> Dashboard Dosen
     ADMIN...  -> Dashboard Admin
     MHS...    -> Pemagang: wajib pilih nama + isi NIM yang cocok dengan
                  data yang didaftarkan dosen (divalidasi di server).
   ========================================================================== */
const Auth = (() => {
    const SESSION_KEY = 'pm.session.v2';
    let names = [];          // daftar nama pemagang (tanpa NIM) dari server

    const normKey = (v) => String(v || '').trim().toUpperCase();
    function roleOf(key) {
        if (/^ADMIN.+/.test(key)) return 'ADMIN';
        if (/^DOSEN.+/.test(key)) return 'DOSEN';
        if (/^MHS.+/.test(key)) return 'MHS';
        return null;
    }

    /* ---------- status koneksi ---------- */
    function setConn(state, text) {
        const el = UI.$('#conn');
        el.className = `conn ${state}`;
        UI.$('#connText').textContent = text;
        UI.$('#connRetry').classList.toggle('hidden', state !== 'err');
    }

    async function loadNames() {
        setConn('load', 'Menghubungkan ke server…');
        try {
            /* Coba koneksi nyata dulu */
            const res = await Api.get('pemagangPublic');
            names = res.data || [];
            setConn('ok', `Terhubung · ${names.length} pemagang terdaftar`);
        } catch (e) {
            /* Gagal -> aktifkan demo mode, coba lagi dengan data demo */
            Api.activateDemo();
            try {
                const res2 = await Api.get('pemagangPublic');
                names = res2.data || [];
                setConn('ok', `MODE DEMO · ${names.length} pemagang contoh`);
            } catch (e2) {
                names = [];
                setConn('err', e2.message);
            }
        }
        /* Tampilkan banner demo */
        if (Api.isDemo()) {
            const existing = UI.$('#demoBanner');
            if (!existing) {
                const banner = document.createElement('div');
                banner.id = 'demoBanner';
                banner.className = 'alert alert-info';
                banner.style.cssText = 'margin:0 0 12px;font-size:13px';
                banner.innerHTML = `${UI.icon('info', 'w-5 h-5')}<div><b>Mode Demo</b> — Backend belum terhubung. Data contoh di memori, perubahan tidak tersimpan.
                    <br>Untuk menghubungkan ke server: salin <code>backend/google-apps-script.js</code> ke Google Apps Script, jalankan <code>setup</code>, lalu deploy versi baru.</div>`;
                UI.$('.login-card').insertBefore(banner, UI.$('#loginForm'));
            }
        }
        fillNames();
    }

    function fillNames() {
        const sel = UI.$('#loginNama');
        const keep = sel.value;
        sel.innerHTML = '<option value="">— Pilih nama Anda —</option>' +
            names.map((n) => `<option value="${UI.esc(n.id)}">${UI.esc(n.nama)}${n.prodi ? ' · ' + UI.esc(n.prodi) : ''}</option>`).join('');
        if (keep) sel.value = keep;
        UI.$('#namaEmpty').classList.toggle('hidden', names.length > 0);
    }

    function syncFields() {
        const input = UI.$('#loginKey');
        const pos = input.selectionStart;
        input.value = input.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '');
        input.setSelectionRange(pos, pos);
        UI.$('#mhsFields').classList.toggle('hidden', roleOf(input.value) !== 'MHS');
    }

    /* ---------- submit ---------- */
    async function submit(ev) {
        ev.preventDefault();
        UI.clearErrs(UI.$('#loginForm'));
        const key = normKey(UI.val('loginKey'));
        const role = roleOf(key);
        const errs = {};

        if (!key) errs.loginKey = 'Key akses wajib diisi.';
        else if (!/^[A-Z0-9_-]{4,30}$/.test(key)) errs.loginKey = 'Key hanya boleh huruf, angka, _ atau - (4–30 karakter).';
        else if (!role) errs.loginKey = /^(DOSEN|MHS|ADMIN)$/.test(key)
            ? 'Tambahkan identitas setelah awalan, mis. DOSEN001.'
            : 'Key tidak dikenali. Awali dengan DOSEN, MHS, atau ADMIN.';

        const pid = UI.val('loginNama');
        const nim = UI.val('loginNim');
        if (role === 'MHS') {
            if (!pid) errs.loginNama = 'Pilih nama Anda dari daftar.';
            if (!nim) errs.loginNim = 'NIM wajib diisi.';
            else if (!/^[0-9]{6,15}$/.test(nim)) errs.loginNim = 'NIM harus berupa angka 6–15 digit.';
        }
        if (!UI.showErrs(errs)) return;

        const btn = UI.$('#loginBtn');
        await UI.busy(btn, async () => {
            try {
                if (role === 'MHS') {
                    const res = await Api.post('loginPemagang', { pid, nim });
                    const p = res.profile;
                    App.user = { role, key, nim: p.nim, nama: p.nama, prodi: p.prodi, dosenKey: p.dosenKey, pid: p.id };
                } else {
                    App.user = { role, key };
                }
                sessionStorage.setItem(SESSION_KEY, JSON.stringify(App.user));
                UI.$('#loginNim').value = '';
                UI.toast(`Selamat datang, ${App.user.nama || key}!`, 'ok');
                await App.start();
            } catch (e) {
                if (role === 'MHS' && /NIM/.test(e.message)) UI.setErr('loginNim', e.message);
                else if (role === 'MHS' && /nama/i.test(e.message)) UI.setErr('loginNama', e.message);
                UI.toast(e.message, 'err');
            }
        });
    }

    /* ---------- sesi ---------- */
    function restore() {
        try {
            const u = JSON.parse(sessionStorage.getItem(SESSION_KEY) || 'null');
            if (u && NAV[u.role] && u.key && (u.role !== 'MHS' || /^[0-9]{6,15}$/.test(u.nim))) return u;
        } catch (e) { /* abaikan */ }
        sessionStorage.removeItem(SESSION_KEY);
        return null;
    }
    const clearSession = () => sessionStorage.removeItem(SESSION_KEY);

    function showLogin() {
        App.show('login');
        UI.$('#loginKey').value = '';
        syncFields();
        UI.clearErrs(UI.$('#loginForm'));
        loadNames();
        UI.$('#loginKey').focus();
    }

    function init() {
        UI.$('#loginForm').addEventListener('submit', submit);
        UI.$('#loginKey').addEventListener('input', syncFields);
        UI.$('#loginNim').addEventListener('input', (e) => { e.target.value = e.target.value.replace(/\D/g, '').slice(0, 15); });
        UI.$('#connRetry').addEventListener('click', loadNames);
    }

    return { init, restore, clearSession, showLogin };
})();
