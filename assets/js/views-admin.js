/* ==========================================================================
   VIEWS — DASHBOARD ADMIN
   Ringkasan · Rekap Aktivitas · Semua Jadwal · Data Pemagang · Koneksi & Sistem
   ========================================================================== */
(() => {
    const { esc, icon } = UI;
    const A = Views.ADMIN;

    const dosenList = () => {
        const s = new Set();
        App.data.pemagang.forEach((p) => s.add(p.dosenKey));
        App.data.jadwal.forEach((j) => s.add(j.dosenKey));
        return s;
    };

    /* ------------------------------------------------------------------ */
    /* RINGKASAN                                                          */
    /* ------------------------------------------------------------------ */
    A.dash = {
        title: 'Ringkasan Sistem',
        subtitle: 'Pantauan seluruh aktivitas pemagangan',
        render() {
            const d = App.data, all = App.hours();
            const per = d.pemagang.map((p) => ({ p, h: App.hours(p.nim) })).sort((a, b) => b.h.valid - a.h.valid);
            const maxJam = Math.max(CONFIG.TARGET_JAM, ...per.map((x) => x.h.valid));
            const st = { Menunggu: 0, Tervalidasi: 0, Ditolak: 0 };
            d.laporan.forEach((l) => { st[l.status] = (st[l.status] || 0) + 1; });
            const total = d.laporan.length || 1;

            const bars = per.length
                ? `<div class="card-body stack" style="gap:14px">${per.slice(0, 10).map(({ p, h }) => `
                    <div><div class="flex justify-between gap-3 mb-1"><span class="cell-title">${esc(p.nama)} <span class="cell-sub">· ${esc(p.nim)}</span></span><span class="cell-sub">${UI.jam(h.valid)} jam</span></div>
                    ${V.progress((h.valid / maxJam) * 100)}</div>`).join('')}</div>`
                : UI.empty('Belum ada pemagang', '', 'users');

            const stBars = `<div class="card-body stack" style="gap:14px">${['Menunggu', 'Tervalidasi', 'Ditolak'].map((s) => `
                <div><div class="flex justify-between mb-1"><span>${UI.badge(s)}</span><span class="cell-sub">${st[s]} laporan</span></div>${V.progress((st[s] / total) * 100)}</div>`).join('')}</div>`;

            return `<div class="stack">
                <div class="stats">
                    ${V.stat('Pemagang', d.pemagang.length, 'users')}
                    ${V.stat('Dosen pembimbing', dosenList().size, 'user')}
                    ${V.stat('Jadwal', d.jadwal.length, 'calendar')}
                    ${V.stat('Laporan', d.laporan.length, 'file', `${st.Menunggu} menunggu validasi`)}
                    ${V.stat('Jam tervalidasi', UI.jam(all.valid), 'clock', 'seluruh pemagang')}
                </div>
                <div class="two-col">
                    ${V.card('Jam tervalidasi per pemagang', `Top ${Math.min(10, per.length)} dari ${per.length}`, bars)}
                    ${V.card('Status laporan', 'Sebaran status validasi', d.laporan.length ? stBars : UI.empty('Belum ada laporan', '', 'file'))}
                </div>
            </div>`;
        },
    };

    /* ------------------------------------------------------------------ */
    /* REKAP AKTIVITAS (Jadwal · Pemagang · Ruang · Tugas · Status)       */
    /* ------------------------------------------------------------------ */
    function rekapTable() {
        const rows = V.filterLaporan(App.data.laporan, App.ui.rekap);
        if (!rows.length) return UI.empty('Tidak ada data', 'Ubah filter atau kata kunci pencarian.', 'chart');
        return `<div class="table-wrap"><table class="table"><thead><tr>
            <th>Tanggal &amp; waktu</th><th>Jadwal</th><th>Pemagang</th><th>Ruang</th><th>Tugas / kegiatan</th><th>Status validasi</th><th></th></tr></thead><tbody>
            ${rows.map((l) => {
                const j = App.idx.jadwal.get(l.jadwalId);
                return `<tr>
                    <td class="nowrap"><div class="cell-title">${UI.fmtDate(l.tanggal)}</div><div class="cell-sub">${esc(l.jamMulai)}–${esc(l.jamSelesai)} · ${UI.durasiText(l.durasiJam)}</div></td>
                    <td>${j ? `<div class="cell-title">${esc(j.judul)}</div><div class="cell-sub">${UI.fmtDate(j.tanggal)}</div>` : '<span class="cell-sub">Tidak terikat jadwal</span>'}</td>
                    <td>${V.person(l.nama, l.nim)}</td><td class="nowrap">${esc(l.ruang)}</td>
                    <td><div class="clamp-2">${esc(l.kegiatan)}</div>${j ? `<div class="cell-sub clamp-2" style="margin-top:2px">Tugas dosen: ${esc(j.tugas)}</div>` : ''}</td>
                    <td>${UI.badge(l.status)}</td>
                    <td><button class="btn btn-sm btn-outline" data-act="lap-view" data-id="${esc(l.id)}">${icon('eye')}Detail</button></td></tr>`;
            }).join('')}</tbody></table></div>`;
    }

    A.rekap = {
        title: 'Rekap Aktivitas',
        subtitle: 'Jadwal, pemagang, ruang, tugas, dan status validasi',
        render() {
            const f = App.ui.rekap;
            return V.card('Rekapitulasi', '', `
                <div class="toolbar"><div class="flex gap-3 flex-wrap items-center">${V.chips(V.statusCounts(App.data.laporan), f.status, 'rekap-filter')}</div>
                    <div class="flex gap-2 flex-wrap items-center">${V.search('rekapSearch', f.q, 'Cari nama, NIM, kegiatan…')}
                    <button class="btn btn-outline btn-sm" data-act="rekap-export">${icon('download')}Ekspor CSV</button>
                    <button class="btn btn-outline btn-sm hide-sm" onclick="window.print()">Cetak</button></div></div>
                <div id="rekapTable">${rekapTable()}</div>`);
        },
        bind(host) {
            const s = UI.$('#rekapSearch', host);
            if (s) s.addEventListener('input', () => { App.ui.rekap.q = s.value; UI.$('#rekapTable').innerHTML = rekapTable(); });
        },
    };

    Actions['rekap-filter'] = (el) => { App.ui.rekap.status = el.dataset.v; App.render(false, false); };

    Actions['rekap-export'] = () => {
        const rows = V.filterLaporan(App.data.laporan, App.ui.rekap);
        if (!rows.length) return void UI.toast('Tidak ada data untuk diekspor.', 'warn');
        const head = ['Tanggal', 'Jam mulai', 'Jam selesai', 'Durasi (jam)', 'Jadwal', 'Nama', 'NIM', 'Ruang', 'Tugas dosen', 'Kegiatan', 'Kendala', 'Status', 'Catatan dosen', 'Link foto'];
        const body = rows.map((l) => {
            const j = App.idx.jadwal.get(l.jadwalId);
            return [l.tanggal, l.jamMulai, l.jamSelesai, l.durasiJam, j ? j.judul : '', l.nama, l.nim, l.ruang, j ? j.tugas : '', l.kegiatan, l.kendala, l.status, l.catatanDosen,
                l.fotos.map((id) => UI.photoLink(id)).join(' ')];
        });
        UI.downloadCSV(`rekap-magang-${UI.today()}.csv`, [head, ...body]);
        UI.toast(`${rows.length} baris diekspor.`, 'ok');
    };

    /* ------------------------------------------------------------------ */
    /* SEMUA JADWAL                                                       */
    /* ------------------------------------------------------------------ */
    A.jadwal = {
        title: 'Semua Jadwal',
        subtitle: 'Jadwal dari seluruh dosen dan peserta yang disetujui',
        render() {
            const rows = App.data.jadwal;
            if (!rows.length) return V.card('Jadwal', '', UI.empty('Belum ada jadwal', 'Jadwal dibuat oleh dosen pembimbing.', 'calendar'));
            const body = rows.map((j) => {
                const peserta = App.data.pengajuan.filter((p) => p.jadwalId === j.id && p.status === 'Disetujui').map((p) => p.nama);
                return `<tr><td><div class="cell-title">${esc(j.judul)}</div><div class="cell-sub clamp-2">${esc(j.tugas)}</div></td>
                    <td class="nowrap">${esc(j.dosenKey)}</td>
                    <td class="nowrap"><div class="cell-title">${UI.fmtDate(j.tanggal)}</div><div class="cell-sub">${esc(j.jamMulai)}–${esc(j.jamSelesai)}</div></td>
                    <td class="nowrap">${esc(j.ruang)}</td>
                    <td>${peserta.length ? esc(peserta.join(', ')) : '<span class="cell-sub">Belum ada</span>'}</td>
                    <td class="nowrap">${j.terisi} / ${j.kuota}</td><td>${UI.badge(j.status)}</td>
                    <td><button class="btn btn-sm btn-danger" data-act="jd-del" data-id="${esc(j.id)}">${icon('trash')}</button></td></tr>`;
            }).join('');
            return V.card('Daftar jadwal', `${rows.length} jadwal`, `<div class="table-wrap"><table class="table"><thead><tr>
                <th>Jadwal &amp; tugas</th><th>Dosen</th><th>Waktu</th><th>Ruang</th><th>Peserta disetujui</th><th>Terisi</th><th>Status</th><th></th></tr></thead><tbody>${body}</tbody></table></div>`);
        },
    };

    /* ------------------------------------------------------------------ */
    /* DATA PEMAGANG                                                      */
    /* ------------------------------------------------------------------ */
    A.pemagang = {
        title: 'Data Pemagang',
        subtitle: 'Seluruh pemagang terdaftar beserta capaian jam',
        render() {
            const rows = App.data.pemagang;
            if (!rows.length) return V.card('Pemagang', '', UI.empty('Belum ada pemagang', 'Pemagang didaftarkan oleh dosen pembimbing.', 'users'));
            const body = rows.map((p) => {
                const h = App.hours(p.nim);
                return `<tr><td>${V.person(p.nama, p.nim)}</td><td>${esc(p.prodi) || '<span class="cell-sub">—</span>'}</td><td class="nowrap">${esc(p.dosenKey)}</td>
                    <td style="min-width:170px"><div class="cell-title">${UI.jam(h.valid)} <span class="cell-sub">/ ${CONFIG.TARGET_JAM} jam</span></div>${V.progress((h.valid / CONFIG.TARGET_JAM) * 100)}</td>
                    <td class="num">${h.count}</td></tr>`;
            }).join('');
            return V.card('Daftar pemagang', `${rows.length} pemagang`, `<div class="table-wrap"><table class="table"><thead><tr>
                <th>Pemagang</th><th>Program studi</th><th>Dosen</th><th>Jam tervalidasi</th><th class="num">Laporan</th></tr></thead><tbody>${body}</tbody></table></div>`);
        },
    };

    /* ------------------------------------------------------------------ */
    /* KONEKSI & SISTEM                                                   */
    /* ------------------------------------------------------------------ */
    async function runPing() {
        const out = UI.$('#pingOut');
        if (!out) return;
        out.innerHTML = `<div class="flex items-center gap-2" style="color:var(--muted)"><span class="spinner"></span>Menguji koneksi…</div>`;
        const t0 = performance.now();
        try {
            const r = await Api.get('ping');
            if (!UI.$('#pingOut')) return;
            out.innerHTML = `<div class="alert alert-ok">${icon('check', 'w-5 h-5')}<div><b>Terhubung ke Google Sheets</b> (${Math.round(performance.now() - t0)} ms)</div></div>
                <dl class="kv" style="margin-top:14px"><dt>Spreadsheet</dt><dd>${esc(r.spreadsheet)}</dd><dt>Sheet</dt><dd>${(r.sheets || []).map((s) => `<code>${esc(s)}</code>`).join(' ')}</dd>
                <dt>Versi backend</dt><dd>${esc(r.versi)}</dd><dt>Ruang</dt><dd>${esc(r.ruang)}</dd><dt>Waktu server</dt><dd>${esc(r.waktu)}</dd></dl>`;
        } catch (e) {
            if (!UI.$('#pingOut')) return;
            out.innerHTML = `<div class="alert alert-err">${icon('alert', 'w-5 h-5')}<div><b>Tidak terhubung</b><div style="margin-top:4px">${esc(e.message)}</div></div></div>`;
        }
    }

    A.koneksi = {
        title: 'Koneksi & Sistem',
        subtitle: 'Status Google Apps Script & Google Sheets',
        render() {
            const url = CONFIG.SCRIPT_URL;
            const masked = url.length > 60 ? url.slice(0, 42) + '…' + url.slice(-12) : url;
            return `<div class="two-col">
                ${V.card('Status koneksi', 'Uji langsung ke Google Apps Script', `<div class="card-body"><div id="pingOut"></div>
                    <button class="btn btn-outline" style="margin-top:16px" data-act="sys-ping">${icon('refresh')}Uji ulang</button></div>`)}
                ${V.card('Konfigurasi', 'Dapat diubah di assets/js/config.js', `<div class="card-body"><dl class="kv">
                    <dt>SCRIPT_URL</dt><dd style="word-break:break-all"><code>${esc(masked)}</code></dd>
                    <dt>Ruang magang</dt><dd>${esc(CONFIG.RUANG)}</dd><dt>Target jam</dt><dd>${CONFIG.TARGET_JAM} jam / pemagang</dd><dt>Maks foto</dt><dd>${CONFIG.MAX_FOTO} per laporan</dd></dl>
                    <div class="alert alert-info" style="margin-top:16px">${icon('info', 'w-5 h-5')}<div>Setelah mengubah kode di Apps Script: <b>Terapkan → Kelola deployment → Versi baru</b>.
                    Foto bukti tersimpan di Google Drive pada folder <code>Portal Magang - Bukti Foto</code>.</div></div></div>`)}
            </div>`;
        },
        bind() { runPing(); },
    };

    Actions['sys-ping'] = () => runPing();
})();
