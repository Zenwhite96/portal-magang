/* ==========================================================================
   VIEWS — DASHBOARD DOSEN
   Ringkasan · Data Pemagang · Jadwal Magang · Pengajuan · Laporan & Validasi
   ========================================================================== */
(() => {
    const { esc, icon } = UI;
    const D = Views.DOSEN;

    const RE_NIM = /^[0-9]{6,15}$/;
    const RE_NAMA = /^[\p{L}][\p{L} .,'’-]{1,59}$/u;
    const cleanName = (s) => String(s || '').replace(/\s+/g, ' ').trim();

    /* ------------------------------------------------------------------ */
    /* RINGKASAN                                                          */
    /* ------------------------------------------------------------------ */
    D.dash = {
        title: 'Ringkasan',
        subtitle: () => `Selamat datang, ${App.user.key}`,
        render() {
            const d = App.data;
            const pjPending = d.pengajuan.filter((p) => p.status === 'Diajukan');
            const lapPending = d.laporan.filter((l) => l.status === 'Menunggu');
            const aktif = d.jadwal.filter((j) => j.status === 'Buka' && j.tanggal >= UI.today());
            const jamValid = App.hours().valid;

            const pjList = pjPending.length
                ? `<div class="table-wrap"><table class="table"><tbody>${pjPending.slice(0, 5).map((p) => `
                    <tr><td>${V.person(p.nama, p.nim)}</td><td>${V.jadwalCell(App.idx.jadwal.get(p.jadwalId))}</td>
                    <td><div class="actions" style="justify-content:flex-end">
                        <button class="btn btn-sm btn-success" data-act="pj-decide" data-id="${esc(p.id)}" data-v="Disetujui">Setujui</button>
                        <button class="btn btn-sm btn-danger" data-act="pj-decide" data-id="${esc(p.id)}" data-v="Ditolak">Tolak</button></div></td></tr>`).join('')}</tbody></table></div>`
                : UI.empty('Tidak ada pengajuan menunggu', 'Pengajuan pemagang akan muncul di sini.', 'check');

            const lapList = lapPending.length
                ? `<div class="table-wrap"><table class="table"><tbody>${lapPending.slice(0, 5).map((l) => `
                    <tr><td>${V.person(l.nama, l.nim)}</td>
                    <td><div class="cell-title">${UI.fmtDate(l.tanggal)}</div><div class="cell-sub">${esc(l.jamMulai)}–${esc(l.jamSelesai)} · ${UI.durasiText(l.durasiJam)}</div></td>
                    <td style="text-align:right"><button class="btn btn-sm btn-outline" data-act="lap-open" data-id="${esc(l.id)}">Tinjau</button></td></tr>`).join('')}</tbody></table></div>`
                : UI.empty('Semua laporan sudah ditinjau', '', 'check');

            return `<div class="stack">
                <div class="stats">
                    ${V.stat('Pemagang terdaftar', d.pemagang.length, 'users')}
                    ${V.stat('Jadwal terbuka', aktif.length, 'calendar', 'Mendatang & belum ditutup')}
                    ${V.stat('Pengajuan menunggu', pjPending.length, 'list')}
                    ${V.stat('Laporan menunggu', lapPending.length, 'file', `${UI.jam(jamValid)} jam sudah tervalidasi`)}
                </div>
                <div class="two-col">
                    ${V.card('Pengajuan menunggu persetujuan', 'Pemagang yang memilih jadwal Anda', pjList,
                        pjPending.length > 5 ? `<button class="btn btn-ghost btn-sm" data-act="nav" data-tab="pengajuan">Lihat semua</button>` : '')}
                    ${V.card('Laporan menunggu validasi', 'Periksa kegiatan & bukti foto', lapList,
                        lapPending.length > 5 ? `<button class="btn btn-ghost btn-sm" data-act="nav" data-tab="laporan">Lihat semua</button>` : '')}
                </div>
                ${d.pemagang.length === 0 ? `<div class="alert alert-info">${icon('info', 'w-5 h-5')}<div><b>Langkah pertama:</b> daftarkan nama &amp; NIM pemagang di menu
                    <a href="#" data-act="nav" data-tab="pemagang">Data Pemagang</a>. Hanya pemagang terdaftar yang bisa masuk.</div></div>` : ''}
            </div>`;
        },
    };

    /* ------------------------------------------------------------------ */
    /* DATA PEMAGANG                                                      */
    /* ------------------------------------------------------------------ */
    D.pemagang = {
        title: 'Data Pemagang',
        subtitle: 'Daftarkan nama & NIM pemagang bimbingan Anda',
        render() {
            const rows = App.data.pemagang;
            const tbody = rows.map((p) => {
                const h = App.hours(p.nim);
                const pct = (h.valid / CONFIG.TARGET_JAM) * 100;
                return `<tr>
                    <td>${V.person(p.nama, p.nim)}</td>
                    <td>${esc(p.prodi) || '<span class="cell-sub">—</span>'}</td>
                    <td style="min-width:170px"><div class="cell-title">${UI.jam(h.valid)} <span class="cell-sub">/ ${CONFIG.TARGET_JAM} jam</span></div>${V.progress(pct)}</td>
                    <td class="num">${h.count}</td>
                    <td><button class="btn btn-sm btn-danger" data-act="pm-del" data-id="${esc(p.id)}" ${h.count ? 'disabled title="Sudah punya laporan, tidak dapat dihapus"' : ''}>${icon('trash')}Hapus</button></td>
                </tr>`;
            }).join('');

            return `<div class="stack">
                <section class="card">
                    <div class="card-head"><div><h3>Tambah pemagang</h3><p>NIM yang Anda daftarkan di sini menjadi syarat login pemagang.</p></div></div>
                    <div class="card-body">
                        <div class="form-grid">
                            ${UI.field({ id: 'pmNama', label: 'Nama lengkap', required: true, placeholder: 'Mis. Budi Santoso', attrs: 'maxlength="60"' })}
                            ${UI.field({ id: 'pmNim', label: 'NIM', required: true, placeholder: 'Mis. 2023010001', attrs: 'inputmode="numeric" maxlength="15"', hint: 'Angka 6–15 digit.' })}
                            ${UI.field({ id: 'pmProdi', label: 'Program studi', placeholder: 'Opsional', attrs: 'maxlength="60"', cls: 'span-2' })}
                        </div>
                        <button class="btn btn-primary" data-act="pm-add">${icon('plus')}Tambah pemagang</button>

                        <details class="disclosure" style="margin-top:20px">
                            <summary>Tambah banyak sekaligus</summary>
                            <p class="hint" style="margin:0 0 8px">Satu baris satu pemagang. Format: <code>NIM, Nama, Prodi(opsional)</code></p>
                            <textarea class="input" id="pmBulk" rows="5" placeholder="2023010001, Budi Santoso, Informatika&#10;2023010002, Siti Aminah"></textarea>
                            <p class="field-error" data-err="pmBulk" role="alert"></p>
                            <button class="btn btn-outline" style="margin-top:10px" data-act="pm-bulk">${icon('users')}Tambahkan semua</button>
                        </details>
                    </div>
                </section>

                ${V.card('Daftar pemagang', `${rows.length} pemagang`,
                    rows.length ? `<div class="table-wrap"><table class="table"><thead><tr>
                        <th>Pemagang</th><th>Program studi</th><th>Jam tervalidasi</th><th class="num">Laporan</th><th></th></tr></thead>
                        <tbody>${tbody}</tbody></table></div>`
                        : UI.empty('Belum ada pemagang', 'Tambahkan pemagang pertama Anda pada formulir di atas.', 'users'))}
            </div>`;
        },
        bind(host) {
            const nim = UI.$('#pmNim', host);
            if (nim) nim.addEventListener('input', () => { nim.value = nim.value.replace(/\D/g, ''); });
        },
    };

    function reportAdd(res) {
        const skipped = res.dilewati || [];
        UI.toast(`${res.ditambahkan} pemagang ditambahkan.`, 'ok');
        if (skipped.length) {
            UI.toast(`${skipped.length} dilewati: ${skipped.slice(0, 2).map((s) => `${s.nim || '?'} (${s.alasan})`).join('; ')}${skipped.length > 2 ? '…' : ''}`, 'warn', 7000);
        }
    }

    Actions['pm-add'] = (btn) => {
        UI.clearErrs();
        const nama = cleanName(UI.val('pmNama')), nim = UI.val('pmNim'), prodi = cleanName(UI.val('pmProdi'));
        const errs = {};
        if (!RE_NAMA.test(nama)) errs.pmNama = 'Nama 2–60 karakter, hanya huruf dan tanda baca nama.';
        if (!RE_NIM.test(nim)) errs.pmNim = 'NIM harus berupa angka 6–15 digit.';
        else if (App.data.pemagang.some((p) => p.nim === nim)) errs.pmNim = 'NIM ini sudah terdaftar.';
        if (prodi.length > 60) errs.pmProdi = 'Program studi maksimal 60 karakter.';
        if (!UI.showErrs(errs)) return;
        App.run(btn, async () => reportAdd(await App.mutate('tambahPemagang', { items: [{ nim, nama, prodi }] })));
    };

    Actions['pm-bulk'] = (btn) => {
        UI.clearErrs();
        const lines = UI.val('pmBulk').split(/\r?\n/).map((l) => l.trim()).filter(Boolean);
        if (!lines.length) return void UI.showErrs({ pmBulk: 'Isi minimal satu baris.' });
        if (lines.length > 100) return void UI.showErrs({ pmBulk: 'Maksimal 100 baris sekali input.' });

        const items = [], bad = [], seen = new Set(App.data.pemagang.map((p) => p.nim));
        lines.forEach((line, i) => {
            const [nim = '', nama = '', prodi = ''] = line.split(/[,;\t]/).map((s) => cleanName(s));
            if (!RE_NIM.test(nim)) bad.push(`baris ${i + 1}: NIM tidak valid`);
            else if (!RE_NAMA.test(nama)) bad.push(`baris ${i + 1}: nama tidak valid`);
            else if (seen.has(nim)) bad.push(`baris ${i + 1}: NIM ${nim} dobel/sudah ada`);
            else { seen.add(nim); items.push({ nim, nama, prodi }); }
        });
        if (!items.length) return void UI.showErrs({ pmBulk: bad[0] || 'Tidak ada baris yang valid.' });
        if (bad.length) UI.toast(`${bad.length} baris dilewati — ${bad.slice(0, 2).join('; ')}`, 'warn', 7000);
        App.run(btn, async () => reportAdd(await App.mutate('tambahPemagang', { items })));
    };

    Actions['pm-del'] = async (btn) => {
        const p = App.data.pemagang.find((x) => x.id === btn.dataset.id);
        if (!p) return;
        const ok = await UI.confirmDialog({ title: 'Hapus pemagang?', message: `${p.nama} (${p.nim}) tidak akan bisa masuk lagi. Pengajuannya ikut dihapus.`, confirmText: 'Hapus', danger: true });
        if (ok) App.run(btn, () => App.mutate('hapusPemagang', { id: p.id }, 'Pemagang dihapus.'));
    };

    /* ------------------------------------------------------------------ */
    /* JADWAL MAGANG                                                      */
    /* ------------------------------------------------------------------ */
    D.jadwal = {
        title: 'Jadwal Magang',
        subtitle: `Atur tanggal & jam di ${CONFIG.RUANG}; pemagang memilih dari jadwal ini`,
        render() {
            const rows = App.data.jadwal;
            const tbody = rows.map((j) => {
                const lewat = j.tanggal < UI.today();
                return `<tr ${lewat ? 'style="opacity:.65"' : ''}>
                    <td><div class="cell-title">${esc(j.judul)}</div><div class="cell-sub clamp-2">${esc(j.tugas)}</div></td>
                    <td class="nowrap"><div class="cell-title">${UI.fmtDate(j.tanggal)}</div><div class="cell-sub">${esc(j.jamMulai)}–${esc(j.jamSelesai)}</div></td>
                    <td class="nowrap">${j.terisi} / ${j.kuota}</td>
                    <td>${lewat && j.status === 'Buka' ? '<span class="badge badge-gray">Selesai</span>' : UI.badge(j.status)}</td>
                    <td><div class="actions">
                        <button class="btn btn-sm btn-outline" data-act="jd-toggle" data-id="${esc(j.id)}" data-v="${j.status === 'Buka' ? 'Tutup' : 'Buka'}">${j.status === 'Buka' ? 'Tutup' : 'Buka lagi'}</button>
                        <button class="btn btn-sm btn-danger" data-act="jd-del" data-id="${esc(j.id)}">${icon('trash')}</button></div></td></tr>`;
            }).join('');

            return `<div class="stack">
                <section class="card">
                    <div class="card-head"><div><h3>Buat jadwal baru</h3><p>Pemagang akan melihat jadwal ini dan mengajukan diri.</p></div></div>
                    <div class="card-body">
                        <div class="form-grid">
                            ${UI.field({ id: 'jdJudul', label: 'Judul jadwal', required: true, placeholder: 'Mis. Pengarsipan Dokumen Akademik', attrs: 'maxlength="80"', cls: 'span-2' })}
                            ${UI.field({ id: 'jdTanggal', label: 'Tanggal', type: 'date', required: true, attrs: `min="${UI.today()}"` })}
                            ${UI.field({ id: 'jdKuota', label: 'Kuota pemagang', type: 'number', required: true, value: '2', attrs: 'min="1" max="50" step="1" inputmode="numeric"' })}
                            ${UI.field({ id: 'jdMulai', label: 'Jam mulai', type: 'time', required: true, value: '08:00' })}
                            ${UI.field({ id: 'jdSelesai', label: 'Jam selesai', type: 'time', required: true, value: '12:00' })}
                            ${UI.field({ id: 'jdRuang', label: 'Ruang magang', value: CONFIG.RUANG, attrs: 'readonly', hint: 'Ruang magang hanya di Wakil Rektor 1.', cls: 'span-2' })}
                            ${UI.textarea({ id: 'jdTugas', label: 'Tugas yang dikerjakan', required: true, rows: 4, max: 1000, placeholder: 'Jelaskan tugas pemagang pada jadwal ini…', cls: 'span-2' })}
                        </div>
                        <button class="btn btn-primary" data-act="jd-create">${icon('plus')}Simpan jadwal</button>
                    </div>
                </section>
                ${V.card('Jadwal Anda', `${rows.length} jadwal`,
                    rows.length ? `<div class="table-wrap"><table class="table"><thead><tr>
                        <th>Jadwal &amp; tugas</th><th>Waktu</th><th>Terisi</th><th>Status</th><th></th></tr></thead><tbody>${tbody}</tbody></table></div>`
                        : UI.empty('Belum ada jadwal', 'Buat jadwal pertama menggunakan formulir di atas.', 'calendar'))}
            </div>`;
        },
    };

    Actions['jd-create'] = (btn) => {
        UI.clearErrs();
        const judul = UI.val('jdJudul'), tanggal = UI.val('jdTanggal'), mulai = UI.val('jdMulai'), selesai = UI.val('jdSelesai');
        const kuotaRaw = UI.val('jdKuota'), kuota = Number(kuotaRaw), tugas = UI.val('jdTugas');
        const errs = {};
        if (judul.length < 3 || judul.length > 80) errs.jdJudul = 'Judul 3–80 karakter.';
        if (!tanggal) errs.jdTanggal = 'Tanggal wajib diisi.';
        else if (tanggal < UI.today()) errs.jdTanggal = 'Tanggal tidak boleh di masa lalu.';
        if (!UI.isTime(mulai)) errs.jdMulai = 'Jam mulai wajib diisi.';
        if (!UI.isTime(selesai)) errs.jdSelesai = 'Jam selesai wajib diisi.';
        if (!errs.jdMulai && !errs.jdSelesai) {
            const s = UI.toMin(mulai), e = UI.toMin(selesai);
            if (e <= s) errs.jdSelesai = 'Jam selesai harus setelah jam mulai.';
            else if (e - s < 30) errs.jdSelesai = 'Durasi minimal 30 menit.';
            else if (tanggal === UI.today() && e <= UI.nowMin()) errs.jdSelesai = 'Jam ini sudah lewat untuk hari ini.';
        }
        if (!Number.isInteger(kuota) || kuota < 1 || kuota > 50) errs.jdKuota = 'Kuota harus bilangan bulat 1–50.';
        if (tugas.length < 10) errs.jdTugas = 'Deskripsi tugas minimal 10 karakter.';
        if (!UI.showErrs(errs)) return;
        App.run(btn, () => App.mutate('buatJadwal', { judul, tanggal, jamMulai: mulai, jamSelesai: selesai, kuota, tugas }, 'Jadwal berhasil dibuat.'));
    };

    Actions['jd-toggle'] = (btn) => App.run(btn, () =>
        App.mutate('ubahStatusJadwal', { id: btn.dataset.id, status: btn.dataset.v }, btn.dataset.v === 'Tutup' ? 'Jadwal ditutup.' : 'Jadwal dibuka kembali.'));

    Actions['jd-del'] = async (btn) => {
        const j = App.idx.jadwal.get(btn.dataset.id);
        if (!j) return;
        const ok = await UI.confirmDialog({ title: 'Hapus jadwal?', message: `"${j.judul}" (${UI.fmtDate(j.tanggal)}) akan dihapus beserta pengajuan yang belum diputuskan.`, confirmText: 'Hapus', danger: true });
        if (ok) App.run(btn, () => App.mutate('hapusJadwal', { id: j.id }, 'Jadwal dihapus.'));
    };

    /* ------------------------------------------------------------------ */
    /* PENGAJUAN                                                          */
    /* ------------------------------------------------------------------ */
    D.pengajuan = {
        title: 'Pengajuan Jadwal',
        subtitle: 'Setujui atau tolak pemagang yang memilih jadwal Anda',
        render() {
            const order = { Diajukan: 0, Disetujui: 1, Ditolak: 2, Dibatalkan: 3 };
            const rows = [...App.data.pengajuan].sort((a, b) => (order[a.status] - order[b.status]) || b.timestamp.localeCompare(a.timestamp));
            const tbody = rows.map((p) => {
                const j = App.idx.jadwal.get(p.jadwalId);
                return `<tr><td>${V.person(p.nama, p.nim)}</td><td>${V.jadwalCell(j)}</td>
                    <td><div class="clamp-2">${esc(p.catatan) || '<span class="cell-sub">—</span>'}</div></td>
                    <td class="nowrap cell-sub">${UI.fmtStamp(p.timestamp)}</td><td>${UI.badge(p.status)}</td>
                    <td>${p.status === 'Diajukan' ? `<div class="actions">
                        <button class="btn btn-sm btn-success" data-act="pj-decide" data-id="${esc(p.id)}" data-v="Disetujui">Setujui</button>
                        <button class="btn btn-sm btn-danger" data-act="pj-decide" data-id="${esc(p.id)}" data-v="Ditolak">Tolak</button></div>` : ''}</td></tr>`;
            }).join('');
            return V.card('Semua pengajuan', `${rows.length} pengajuan`,
                rows.length ? `<div class="table-wrap"><table class="table"><thead><tr>
                    <th>Pemagang</th><th>Jadwal</th><th>Catatan</th><th>Diajukan</th><th>Status</th><th></th></tr></thead><tbody>${tbody}</tbody></table></div>`
                    : UI.empty('Belum ada pengajuan', 'Pengajuan akan muncul setelah pemagang memilih jadwal.', 'list'));
        },
    };

    Actions['pj-decide'] = async (btn) => {
        const p = App.data.pengajuan.find((x) => x.id === btn.dataset.id);
        if (!p) return;
        const status = btn.dataset.v;
        if (status === 'Ditolak') {
            const ok = await UI.confirmDialog({ title: 'Tolak pengajuan?', message: `Pengajuan ${p.nama} tidak akan disetujui dan tidak dapat diajukan ulang untuk jadwal ini.`, confirmText: 'Tolak', danger: true });
            if (!ok) return;
        }
        App.run(btn, () => App.mutate('putusPengajuan', { id: p.id, status }, status === 'Disetujui' ? `${p.nama} disetujui.` : 'Pengajuan ditolak.'));
    };

    /* ------------------------------------------------------------------ */
    /* LAPORAN & VALIDASI                                                 */
    /* ------------------------------------------------------------------ */
    function laporanTable() {
        const rows = V.filterLaporan(App.data.laporan, App.ui.lap);
        if (!rows.length) return UI.empty('Tidak ada laporan', 'Ubah filter atau kata kunci pencarian.', 'file');
        return `<div class="table-wrap"><table class="table"><thead><tr>
            <th>Tanggal &amp; waktu</th><th>Pemagang</th><th>Kegiatan</th><th>Foto</th><th>Status</th><th></th></tr></thead><tbody>
            ${rows.map((l) => `<tr>
                <td class="nowrap"><div class="cell-title">${UI.fmtDate(l.tanggal)}</div><div class="cell-sub">${esc(l.jamMulai)}–${esc(l.jamSelesai)} · ${UI.durasiText(l.durasiJam)}</div></td>
                <td>${V.person(l.nama, l.nim)}</td>
                <td><div class="clamp-2">${esc(l.kegiatan)}</div></td>
                <td>${V.thumbs(l)}</td><td>${UI.badge(l.status)}</td>
                <td><button class="btn btn-sm ${l.status === 'Menunggu' ? 'btn-primary' : 'btn-outline'}" data-act="lap-open" data-id="${esc(l.id)}">${icon('eye')}${l.status === 'Menunggu' ? 'Tinjau' : 'Detail'}</button></td></tr>`).join('')}
            </tbody></table></div>`;
    }

    D.laporan = {
        title: 'Laporan & Validasi',
        subtitle: 'Tinjau kegiatan dan bukti foto pemagang',
        render() {
            const f = App.ui.lap;
            return V.card('Laporan pemagang', '', `
                <div class="toolbar">${V.chips(V.statusCounts(App.data.laporan), f.status, 'lap-filter')}${V.search('lapSearch', f.q, 'Cari nama, NIM, kegiatan…')}</div>
                <div id="lapTable">${laporanTable()}</div>`);
        },
        bind(host) {
            const s = UI.$('#lapSearch', host);
            if (s) s.addEventListener('input', () => { App.ui.lap.q = s.value; UI.$('#lapTable').innerHTML = laporanTable(); });
        },
    };

    Actions['lap-filter'] = (el) => { App.ui.lap.status = el.dataset.v; App.render(false, false); };
    Actions['lap-open'] = (el) => V.openLaporan(el.dataset.id, { canValidate: true });

    Actions['lap-decide'] = (btn) => {
        const id = btn.dataset.id, status = btn.dataset.v, catatan = UI.val('vCatatan');
        UI.setErr('vCatatan', '');
        if (status === 'Ditolak' && catatan.length < 5) {
            UI.setErr('vCatatan', 'Isi alasan penolakan (min. 5 karakter) agar pemagang dapat memperbaiki.');
            return UI.$('#vCatatan').focus();
        }
        App.run(btn, async () => {
            await App.mutate('validasiLaporan', { id, status, catatan }, status === 'Tervalidasi' ? 'Laporan divalidasi.' : 'Laporan ditolak dan dikembalikan ke pemagang.');
            UI.closeModal();
        });
    };
})();
