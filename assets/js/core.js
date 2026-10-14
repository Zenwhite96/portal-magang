/* ==========================================================================
   CORE — state aplikasi, navigasi per peran, pemuatan data, transisi halaman
   ========================================================================== */
const Views = { DOSEN: {}, MHS: {}, ADMIN: {} };   // diisi oleh views-*.js

const NAV = {
    DOSEN: {
        label: 'Dosen Pembimbing',
        tabs: [
            { id: 'dash', label: 'Ringkasan', icon: 'home' },
            { id: 'pemagang', label: 'Data Pemagang', icon: 'users' },
            { id: 'jadwal', label: 'Jadwal Magang', icon: 'calendar' },
            { id: 'pengajuan', label: 'Pengajuan', icon: 'list' },
            { id: 'laporan', label: 'Laporan & Validasi', icon: 'check' },
        ],
    },
    MHS: {
        label: 'Pemagang',
        tabs: [
            { id: 'dash', label: 'Beranda', icon: 'home' },
            { id: 'tersedia', label: 'Jadwal Tersedia', icon: 'calendar' },
            { id: 'saya', label: 'Jadwal Saya', icon: 'clock' },
            { id: 'lapor', label: 'Isi Laporan', icon: 'file' },
            { id: 'riwayat', label: 'Riwayat Laporan', icon: 'list' },
        ],
    },
    ADMIN: {
        label: 'Administrator',
        tabs: [
            { id: 'dash', label: 'Ringkasan', icon: 'home' },
            { id: 'rekap', label: 'Rekap Aktivitas', icon: 'chart' },
            { id: 'jadwal', label: 'Semua Jadwal', icon: 'calendar' },
            { id: 'pemagang', label: 'Data Pemagang', icon: 'users' },
            { id: 'koneksi', label: 'Koneksi & Sistem', icon: 'server' },
        ],
    },
};

const App = {
    user: null,
    tab: null,
    loaded: false,
    _nav: 0,
    data: { pemagang: [], jadwal: [], pengajuan: [], laporan: [] },
    idx: { jadwal: new Map(), pemagang: new Map() },
    ui: { lap: { status: 'Semua', q: '' }, rekap: { status: 'Semua', q: '' }, photos: [] },

    /* ---------- data ---------- */
    async fetchData() {
        const u = this.user;
        const res = await Api.get('bundle', { role: u.role, key: u.key, nim: u.nim });
        this.normalize(res.data);
        this.loaded = true;
    },

    normalize(d) {
        d.jadwal.forEach((j) => { j.kuota = +j.kuota || 0; j.terisi = +j.terisi || 0; });
        d.laporan.forEach((l) => { l.durasiJam = +l.durasiJam || 0; l.fotos = String(l.fotoIds || '').split(',').filter(Boolean); });
        d.jadwal.sort((a, b) => (a.tanggal + a.jamMulai).localeCompare(b.tanggal + b.jamMulai));
        d.laporan.sort((a, b) => (b.tanggal + b.jamMulai).localeCompare(a.tanggal + a.jamMulai));
        d.pengajuan.sort((a, b) => b.timestamp.localeCompare(a.timestamp));
        d.pemagang.sort((a, b) => a.nama.localeCompare(b.nama));
        this.data = d;
        this.idx.jadwal = new Map(d.jadwal.map((j) => [j.id, j]));
        this.idx.pemagang = new Map(d.pemagang.map((p) => [p.nim, p]));
    },

    /** Total jam per pemagang (atau semua bila nim kosong). */
    hours(nim) {
        let valid = 0, pending = 0, count = 0;
        this.data.laporan.forEach((l) => {
            if (nim && l.nim !== nim) return;
            count++;
            if (l.status === 'Tervalidasi') valid += l.durasiJam;
            else if (l.status === 'Menunggu') pending += l.durasiJam;
        });
        return { valid, pending, count };
    },

    /** Pengajuan aktif (menunggu / disetujui) milik pemagang untuk jadwal tertentu. */
    pengajuanAktif(jadwalId, nim) {
        return this.data.pengajuan.find((p) => p.jadwalId === jadwalId && p.nim === nim && (p.status === 'Diajukan' || p.status === 'Disetujui'));
    },

    /* ---------- shell ---------- */
    buildShell() {
        const { role, key, nama } = this.user;
        const def = NAV[role];
        UI.$('#sideRole').textContent = def.label;
        UI.$('#sideName').textContent = nama || key;
        UI.$('#sideKey').textContent = role === 'MHS' ? `NIM ${this.user.nim}` : key;
        UI.$('#nav').innerHTML = def.tabs.map((t) =>
            `<button class="nav-item" id="nav-${t.id}" data-act="nav" data-tab="${t.id}">${UI.icon(t.icon, 'w-[18px] h-[18px]')}<span>${t.label}</span><span class="pill hidden" data-pill="${t.id}"></span></button>`).join('');
    },

    pills() {
        const d = this.data, c = {};
        if (this.user.role === 'DOSEN') {
            c.pengajuan = d.pengajuan.filter((p) => p.status === 'Diajukan').length;
            c.laporan = d.laporan.filter((l) => l.status === 'Menunggu').length;
        } else if (this.user.role === 'MHS') {
            c.riwayat = d.laporan.filter((l) => l.status === 'Ditolak').length;
        }
        UI.$$('[data-pill]').forEach((el) => { const n = c[el.dataset.pill] || 0; el.textContent = n; el.classList.toggle('hidden', !n); });
    },

    show(screen) {
        const login = UI.$('#login-screen'), app = UI.$('#app-screen');
        login.hidden = screen !== 'login';
        app.hidden = screen !== 'app';
        document.title = screen === 'app' ? 'Portal Magang — UNAS PASIM' : 'Masuk · Portal Magang — UNAS PASIM';
    },

    async start() {
        this.loaded = false;
        this.ui.photos = [];
        this.buildShell();
        this.show('app');
        await this.go(NAV[this.user.role].tabs[0].id, { force: true });
    },

    /* ---------- navigasi & render ---------- */
    async go(tab, { force = false } = {}) {
        const role = this.user.role;
        if (!Views[role][tab]) tab = NAV[role].tabs[0].id;
        const token = ++this._nav;
        this.tab = tab;
        UI.$$('.nav-item').forEach((b) => b.classList.toggle('active', b.dataset.tab === tab));
        Actions['nav-close']();

        const view = Views[role][tab];
        const resolve = (v) => (typeof v === 'function' ? v() : v);
        UI.$('#pageTitle').textContent = resolve(view.title) || '';
        UI.$('#pageSub').textContent = resolve(view.subtitle) || '';

        const host = UI.$('#view');
        if (!this.loaded || force) {
            host.innerHTML = UI.skeleton();
            try { await this.fetchData(); }
            catch (e) {
                if (token !== this._nav) return;
                if (role === 'MHS' && /tidak terdaftar/i.test(e.message)) {
                    UI.toast('Data Anda sudah tidak terdaftar. Silakan hubungi dosen pembimbing.', 'err');
                    return this.logout();
                }
                host.innerHTML = UI.errorState(e);
                return;
            }
            if (token !== this._nav) return;
        }
        this.render(true, true);
    },

    render(scrollTop = false, animate = false) {
        const view = Views[this.user.role][this.tab];
        const host = UI.$('#view');
        host.innerHTML = `<div class="${animate ? 'view-enter' : ''}">${view.render()}</div>`;
        if (view.bind) view.bind(host);
        UI.bindCounters(host);
        this.pills();
        if (scrollTop) window.scrollTo({ top: 0 });
    },

    async refresh() { await this.fetchData(); this.render(false, false); },

    /** POST ke Apps Script + muat ulang data. key/nim pengguna otomatis disertakan. */
    async mutate(action, payload = {}, successMsg = '') {
        const u = this.user;
        const res = await Api.post(action, { key: u.key, nim: u.nim, ...payload });
        if (successMsg) UI.toast(successMsg, 'ok');
        try { await this.refresh(); }
        catch (e) { UI.toast('Data tersimpan, tetapi gagal memuat ulang. Tekan "Muat ulang".', 'warn'); }
        return res;
    },

    /** Jalankan aksi async pada sebuah tombol; error apa pun menjadi toast. */
    run(btn, fn) {
        return UI.busy(btn, async () => {
            try { return await fn(); }
            catch (e) { UI.toast(e.message || 'Terjadi kesalahan.', 'err'); }
        });
    },

    logout() {
        Auth.clearSession();
        this.user = null; this.tab = null; this.loaded = false;
        this.data = { pemagang: [], jadwal: [], pengajuan: [], laporan: [] };
        this.ui = { lap: { status: 'Semua', q: '' }, rekap: { status: 'Semua', q: '' }, photos: [] };
        UI.closeModal();
        Auth.showLogin();
    },
};

/* ---------- aksi global ---------- */
Object.assign(Actions, {
    nav: (el) => App.go(el.dataset.tab),
    'nav-open': () => { UI.$('#sidebar').classList.add('open'); UI.$('#navBackdrop').classList.add('open'); },
    'nav-close': () => { UI.$('#sidebar').classList.remove('open'); UI.$('#navBackdrop').classList.remove('open'); },
    refresh: (el) => App.run(el, () => App.go(App.tab, { force: true })),
    retry: () => App.go(App.tab, { force: true }),
    logout: () => App.logout(),
});

/* ---------- helper tampilan yang dipakai banyak view ---------- */
const V = {
    stat(label, value, ic, sub = '') {
        return `<div class="card stat"><div class="stat-top"><span>${UI.esc(label)}</span><span class="stat-ico">${UI.icon(ic, 'w-[18px] h-[18px]')}</span></div>
            <div class="stat-value">${UI.esc(value)}</div>${sub ? `<div class="stat-sub">${UI.esc(sub)}</div>` : ''}</div>`;
    },
    person(nama, nim) {
        return `<div class="cell-title">${UI.esc(nama || '-')}</div><div class="cell-sub">${UI.esc(nim || '')}</div>`;
    },
    jadwalCell(j) {
        if (!j) return '<span class="cell-sub">—</span>';
        return `<div class="cell-title">${UI.esc(j.judul)}</div><div class="cell-sub">${UI.fmtDate(j.tanggal)} · ${UI.esc(j.jamMulai)}–${UI.esc(j.jamSelesai)}</div>`;
    },
    progress(pct) {
        return `<div class="progress" role="progressbar" aria-valuenow="${Math.round(pct)}" aria-valuemin="0" aria-valuemax="100"><span style="width:${Math.min(100, Math.max(0, pct))}%"></span></div>`;
    },
    card(title, sub, body, headRight = '') {
        return `<section class="card"><div class="card-head"><div><h3>${UI.esc(title)}</h3>${sub ? `<p>${UI.esc(sub)}</p>` : ''}</div>${headRight}</div>${body}</section>`;
    },
    chips(items, active, act) {
        return `<div class="chips" role="group">${items.map((i) =>
            `<button class="chip ${i.v === active ? 'active' : ''}" data-act="${act}" data-v="${UI.esc(i.v)}">${UI.esc(i.l)}<span class="count">${i.n}</span></button>`).join('')}</div>`;
    },
    search(id, value, placeholder) {
        return `<label class="search" for="${id}">${UI.icon('search', 'w-4 h-4')}<input class="input" id="${id}" type="search" placeholder="${UI.esc(placeholder)}" value="${UI.esc(value)}" autocomplete="off"></label>`;
    },
    thumbs(l) {
        if (!l.fotos.length) return '<span class="cell-sub">—</span>';
        return `<div class="flex gap-1.5">${l.fotos.slice(0, 3).map((id) =>
            `<img class="photo-thumb" loading="lazy" referrerpolicy="no-referrer" src="${UI.photoUrl(id, 160)}" alt="Foto bukti ${UI.esc(l.nama)}">`).join('')}</div>`;
    },
    /** Filter laporan: status + kata kunci (nama, NIM, kegiatan, judul jadwal). */
    filterLaporan(list, f) {
        const q = f.q.trim().toLowerCase();
        return list.filter((l) => {
            if (f.status !== 'Semua' && l.status !== f.status) return false;
            if (!q) return true;
            const j = App.idx.jadwal.get(l.jadwalId);
            return [l.nama, l.nim, l.kegiatan, j && j.judul].some((s) => String(s || '').toLowerCase().includes(q));
        });
    },
    statusCounts(list) {
        const c = { Semua: list.length, Menunggu: 0, Tervalidasi: 0, Ditolak: 0 };
        list.forEach((l) => { c[l.status] = (c[l.status] || 0) + 1; });
        return [
            { v: 'Semua', l: 'Semua', n: c.Semua }, { v: 'Menunggu', l: 'Menunggu', n: c.Menunggu },
            { v: 'Tervalidasi', l: 'Tervalidasi', n: c.Tervalidasi }, { v: 'Ditolak', l: 'Ditolak', n: c.Ditolak },
        ];
    },

    /** Modal detail laporan (dipakai dosen, pemagang, admin). */
    openLaporan(id, { canValidate = false } = {}) {
        const l = App.data.laporan.find((x) => x.id === id);
        if (!l) return;
        const j = App.idx.jadwal.get(l.jadwalId);
        const photos = l.fotos.length
            ? `<div class="photo-grid">${l.fotos.map((fid) => `<a class="photo" href="${UI.photoLink(fid)}" target="_blank" rel="noopener"><img loading="lazy" referrerpolicy="no-referrer" src="${UI.photoUrl(fid, 700)}" alt="Bukti foto"></a>`).join('')}</div>`
            : '<p class="cell-sub">Tidak ada foto.</p>';
        const review = canValidate ? `
            <div class="mt-5 pt-5" style="border-top:1px solid var(--line)">
              <div class="field"><label class="label" for="vCatatan">Catatan untuk pemagang <span class="cell-sub">(wajib bila menolak)</span></label>
                <textarea class="input" id="vCatatan" rows="3" maxlength="500" placeholder="Mis. Foto kurang jelas, mohon unggah ulang.">${UI.esc(l.catatanDosen)}</textarea>
                <p class="field-error" data-err="vCatatan" role="alert"></p></div>
            </div>` : '';
        const footer = canValidate
            ? `<button class="btn btn-danger" data-act="lap-decide" data-id="${UI.esc(l.id)}" data-v="Ditolak">${UI.icon('x')}Tolak</button>
               <button class="btn btn-success" data-act="lap-decide" data-id="${UI.esc(l.id)}" data-v="Tervalidasi">${UI.icon('check')}Validasi</button>`
            : `<button class="btn btn-outline" data-act="modal-close">Tutup</button>`;
        UI.openModal({
            title: 'Detail laporan', size: 'lg', footer,
            body: `<dl class="kv">
                <dt>Pemagang</dt><dd>${UI.esc(l.nama)} <span class="cell-sub">· ${UI.esc(l.nim)}</span></dd>
                <dt>Tanggal</dt><dd>${UI.fmtDateLong(l.tanggal)}</dd>
                <dt>Waktu</dt><dd>${UI.esc(l.jamMulai)} – ${UI.esc(l.jamSelesai)} <span class="cell-sub">(${UI.durasiText(l.durasiJam)})</span></dd>
                <dt>Ruang</dt><dd>${UI.esc(l.ruang)}</dd>
                <dt>Jadwal terkait</dt><dd>${j ? `${UI.esc(j.judul)} <span class="cell-sub">· ${UI.fmtDate(j.tanggal)}</span>` : '<span class="cell-sub">Tidak terikat jadwal tertentu</span>'}</dd>
                <dt>Status</dt><dd>${UI.badge(l.status)}</dd>
                <dt>Dikirim</dt><dd>${UI.fmtStamp(l.timestamp)}</dd>
              </dl>
              ${j ? `<div class="mt-4"><div class="note-label">Tugas dari dosen</div><div class="note">${UI.esc(j.tugas)}</div></div>` : ''}
              <div class="mt-4"><div class="note-label">Deskripsi kegiatan</div><div class="note">${UI.esc(l.kegiatan)}</div></div>
              ${l.kendala ? `<div class="mt-4"><div class="note-label">Kendala</div><div class="note">${UI.esc(l.kendala)}</div></div>` : ''}
              ${l.catatanDosen && !canValidate ? `<div class="mt-4"><div class="note-label">Catatan dosen</div><div class="note">${UI.esc(l.catatanDosen)}</div></div>` : ''}
              <div class="mt-4"><div class="note-label">Bukti foto (${l.fotos.length})</div>${photos}</div>
              ${review}`,
        });
    },
};
