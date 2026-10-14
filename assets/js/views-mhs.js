/* ==========================================================================
   VIEWS — DASHBOARD PEMAGANG
   Beranda · Jadwal Tersedia · Jadwal Saya · Isi Laporan · Riwayat Laporan
   ========================================================================== */
(() => {
    const { esc, icon } = UI;
    const M = Views.MHS;
    const MAX_HARI_LAMPAU = 180;   // sama dengan backend

    const nim = () => App.user.nim;
    const myJadwal = (p) => App.idx.jadwal.get(p.jadwalId);

    /* ------------------------------------------------------------------ */
    /* BERANDA                                                            */
    /* ------------------------------------------------------------------ */
    M.dash = {
        title: () => `Halo, ${App.user.nama}`,
        subtitle: () => `NIM ${App.user.nim}${App.user.prodi ? ' · ' + App.user.prodi : ''}`,
        render() {
            const h = App.hours(nim());
            const pct = (h.valid / CONFIG.TARGET_JAM) * 100;
            const ditolak = App.data.laporan.filter((l) => l.status === 'Ditolak').length;
            const menunggu = App.data.laporan.filter((l) => l.status === 'Menunggu').length;
            const upcoming = App.data.pengajuan
                .filter((p) => p.status === 'Disetujui' && myJadwal(p) && myJadwal(p).tanggal >= UI.today())
                .sort((a, b) => (myJadwal(a).tanggal + myJadwal(a).jamMulai).localeCompare(myJadwal(b).tanggal + myJadwal(b).jamMulai));

            const list = upcoming.length
                ? `<div class="table-wrap"><table class="table"><tbody>${upcoming.map((p) => {
                    const j = myJadwal(p);
                    return `<tr><td>${V.jadwalCell(j)}</td><td><div class="clamp-2">${esc(j.tugas)}</div></td><td>${UI.badge('Disetujui')}</td></tr>`;
                }).join('')}</tbody></table></div>`
                : UI.empty('Belum ada jadwal yang disetujui', 'Pilih jadwal di menu "Jadwal Tersedia".', 'calendar');

            return `<div class="stack">
                ${ditolak ? `<div class="alert alert-warn">${icon('alert', 'w-5 h-5')}<div><b>${ditolak} laporan perlu diperbaiki.</b>
                    Buka <a href="#" data-act="nav" data-tab="riwayat">Riwayat Laporan</a> untuk melihat catatan dosen, lalu kirim ulang.</div></div>` : ''}
                <div class="stats">
                    ${V.stat('Jam tervalidasi', UI.jam(h.valid), 'clock', `dari target ${CONFIG.TARGET_JAM} jam`)}
                    ${V.stat('Laporan terkirim', h.count, 'file')}
                    ${V.stat('Menunggu validasi', menunggu, 'list', `${UI.jam(h.pending)} jam`)}
                    ${V.stat('Perlu diperbaiki', ditolak, 'alert')}
                </div>
                <section class="card"><div class="card-body">
                    <div class="flex items-center justify-between gap-3 flex-wrap mb-3">
                        <div><h3 style="font-size:15px">Progres jam magang</h3>
                            <p class="cell-sub">${UI.jam(h.valid)} dari ${CONFIG.TARGET_JAM} jam tervalidasi (${Math.min(100, Math.round(pct))}%)</p></div>
                        <button class="btn btn-primary btn-sm" data-act="nav" data-tab="lapor">${icon('plus')}Isi laporan</button>
                    </div>${V.progress(pct)}</div></section>
                ${V.card('Jadwal saya yang akan datang', 'Jadwal yang sudah disetujui dosen', list,
                    `<button class="btn btn-ghost btn-sm" data-act="nav" data-tab="tersedia">Cari jadwal</button>`)}
            </div>`;
        },
    };

    /* ------------------------------------------------------------------ */
    /* JADWAL TERSEDIA                                                    */
    /* ------------------------------------------------------------------ */
    function availableJadwal() {
        const t = UI.today(), now = UI.nowMin();
        return App.data.jadwal.filter((j) => j.status === 'Buka' && (j.tanggal > t || (j.tanggal === t && UI.toMin(j.jamSelesai) > now)));
    }

    M.tersedia = {
        title: 'Jadwal Tersedia',
        subtitle: 'Jadwal dari dosen pembimbing Anda — ajukan yang sesuai dengan waktu kosong Anda',
        render() {
            const rows = availableJadwal();
            if (!rows.length) return V.card('Jadwal tersedia', '', UI.empty('Belum ada jadwal terbuka', 'Dosen belum membuat jadwal baru, atau semua jadwal sudah lewat/ditutup.', 'calendar'));
            return `<div class="grid-cards">${rows.map((j) => {
                const mine = App.pengajuanAktif(j.id, nim());
                const ditolak = App.data.pengajuan.find((p) => p.jadwalId === j.id && p.nim === nim() && p.status === 'Ditolak');
                const penuh = j.terisi >= j.kuota;
                let action;
                if (mine) action = `<div class="flex items-center justify-between gap-2">${UI.badge(mine.status)}
                    <button class="btn btn-sm btn-outline" data-act="mhs-batal" data-id="${esc(mine.id)}">Batalkan</button></div>`;
                else if (ditolak) action = `<div>${UI.badge('Ditolak')}<span class="cell-sub" style="margin-left:8px">Pengajuan ditolak dosen</span></div>`;
                else if (penuh) action = `<button class="btn btn-outline btn-block" disabled>Kuota penuh</button>`;
                else action = `<button class="btn btn-primary btn-block" data-act="mhs-ajukan" data-id="${esc(j.id)}">${icon('send')}Ajukan jadwal ini</button>`;
                return `<article class="card jadwal-card">
                    <div><h3 style="font-size:16px">${esc(j.judul)}</h3></div>
                    <div class="meta-row">
                        <span>${icon('calendar')}${UI.fmtDateLong(j.tanggal)}</span>
                        <span>${icon('clock')}${esc(j.jamMulai)}–${esc(j.jamSelesai)}</span>
                        <span>${icon('pin')}${esc(j.ruang)}</span>
                    </div>
                    <div><div class="note-label">Tugas dari dosen</div><div class="note">${esc(j.tugas)}</div></div>
                    <div><div class="flex justify-between cell-sub mb-1"><span>Terisi</span><span>${j.terisi} / ${j.kuota}</span></div>${V.progress((j.terisi / j.kuota) * 100)}</div>
                    ${action}</article>`;
            }).join('')}</div>`;
        },
    };

    Actions['mhs-ajukan'] = (el) => {
        const j = App.idx.jadwal.get(el.dataset.id);
        if (!j) return;
        UI.openModal({
            title: 'Ajukan jadwal magang',
            body: `<dl class="kv"><dt>Jadwal</dt><dd><b>${esc(j.judul)}</b></dd><dt>Waktu</dt><dd>${UI.fmtDateLong(j.tanggal)}, ${esc(j.jamMulai)}–${esc(j.jamSelesai)}</dd>
                   <dt>Ruang</dt><dd>${esc(j.ruang)}</dd></dl>
                <div class="field mt-4"><label class="label" for="ajCatatan">Catatan untuk dosen <span class="cell-sub">(opsional)</span></label>
                <textarea class="input" id="ajCatatan" rows="3" maxlength="300" data-counter="300" placeholder="Mis. Saya bisa hadir penuh pada jam tersebut."></textarea>
                <p class="counter" data-counter-for="ajCatatan">0 / 300</p></div>`,
            footer: `<button class="btn btn-outline" data-act="modal-close">Batal</button>
                     <button class="btn btn-primary" data-act="mhs-ajukan-send" data-id="${esc(j.id)}">${icon('send')}Kirim pengajuan</button>`,
        });
        UI.bindCounters(UI.$('#modal-root'));
    };
    Actions['mhs-ajukan-send'] = (btn) => App.run(btn, async () => {
        await App.mutate('ajukanJadwal', { jadwalId: btn.dataset.id, catatan: UI.val('ajCatatan') }, 'Pengajuan terkirim. Menunggu persetujuan dosen.');
        UI.closeModal();
    });
    Actions['mhs-batal'] = async (btn) => {
        const ok = await UI.confirmDialog({ title: 'Batalkan pengajuan?', message: 'Slot jadwal ini akan dibuka kembali untuk pemagang lain.', confirmText: 'Ya, batalkan', danger: true });
        if (ok) App.run(btn, () => App.mutate('batalkanPengajuan', { id: btn.dataset.id }, 'Pengajuan dibatalkan.'));
    };

    /* ------------------------------------------------------------------ */
    /* JADWAL SAYA                                                        */
    /* ------------------------------------------------------------------ */
    M.saya = {
        title: 'Jadwal Saya',
        subtitle: 'Jadwal yang Anda ajukan beserta status persetujuannya',
        render() {
            const rows = [...App.data.pengajuan].sort((a, b) => ((myJadwal(b) || {}).tanggal || '').localeCompare((myJadwal(a) || {}).tanggal || ''));
            if (!rows.length) return V.card('Jadwal saya', '', UI.empty('Belum ada pengajuan', 'Pilih jadwal di menu "Jadwal Tersedia".', 'clock'));
            const body = rows.map((p) => {
                const j = myJadwal(p);
                const aktif = p.status === 'Diajukan' || p.status === 'Disetujui';
                const bisaBatal = aktif && j && j.tanggal >= UI.today();
                return `<tr><td>${V.jadwalCell(j)}</td><td>${esc(j ? j.ruang : '-')}</td>
                    <td><div class="clamp-2">${esc(j ? j.tugas : '-')}</div></td><td>${UI.badge(p.status)}</td>
                    <td><div class="actions">${p.status === 'Disetujui' ? `<button class="btn btn-sm btn-outline" data-act="nav" data-tab="lapor">Isi laporan</button>` : ''}
                        ${bisaBatal ? `<button class="btn btn-sm btn-danger" data-act="mhs-batal" data-id="${esc(p.id)}">Batalkan</button>` : ''}</div></td></tr>`;
            }).join('');
            return V.card('Pengajuan saya', `${rows.length} pengajuan`, `<div class="table-wrap"><table class="table"><thead><tr>
                <th>Jadwal</th><th>Ruang</th><th>Tugas</th><th>Status</th><th></th></tr></thead><tbody>${body}</tbody></table></div>`);
        },
    };

    /* ------------------------------------------------------------------ */
    /* ISI LAPORAN                                                        */
    /* ------------------------------------------------------------------ */
    function photosHtml() {
        const ph = App.ui.photos;
        const grid = ph.length
            ? `<div class="photo-grid">${ph.map((p, i) => `<div class="photo"><img src="${p.preview}" alt="Foto ${i + 1}">
                <button type="button" data-act="ph-del" data-i="${i}" aria-label="Hapus foto ${i + 1}">${icon('x', 'w-3.5 h-3.5')}</button></div>`).join('')}</div>` : '';
        return `${ph.length < CONFIG.MAX_FOTO ? `<label class="photo-drop" id="photoDrop" for="fotoInput">${icon('camera', 'w-6 h-6')}
            <b style="color:var(--text-2)">Ketuk untuk ambil / pilih foto</b><span style="font-size:12.5px">atau seret foto ke sini · JPG, PNG, WEBP · maks ${CONFIG.MAX_FOTO} foto</span></label>` : ''}${grid}`;
    }

    function updateDuration() {
        const out = UI.$('#durInfo');
        if (!out) return;
        const a = UI.val('lpMulai'), b = UI.val('lpSelesai');
        if (!UI.isTime(a) || !UI.isTime(b)) { out.textContent = 'Durasi: —'; out.style.color = 'var(--muted)'; return; }
        const m = UI.toMin(b) - UI.toMin(a);
        if (m <= 0) { out.textContent = 'Jam selesai harus setelah jam mulai.'; out.style.color = 'var(--err)'; return; }
        out.textContent = `Durasi: ${UI.durasiText(m / 60)}`;
        out.style.color = (m < 30 || m > 720) ? 'var(--err)' : 'var(--ok)';
    }

    function updateJadwalNote() {
        const box = UI.$('#lpJadwalNote');
        const j = App.idx.jadwal.get(UI.val('lpJadwal'));
        box.innerHTML = j ? `<div class="note-label">Tugas dari dosen</div><div class="note">${esc(j.tugas)}</div>` : '';
    }

    async function addFiles(fileList) {
        const files = Array.from(fileList || []);
        if (!files.length) return;
        const room = CONFIG.MAX_FOTO - App.ui.photos.length;
        if (room <= 0) return void UI.toast(`Maksimal ${CONFIG.MAX_FOTO} foto.`, 'warn');
        if (files.length > room) UI.toast(`Hanya ${room} foto lagi yang bisa ditambahkan.`, 'warn');
        for (const f of files.slice(0, room)) {
            try {
                const p = await UI.compressImage(f);
                if (p.data.length * 0.75 > 2.9 * 1024 * 1024) throw new Error(`"${f.name}" masih terlalu besar setelah dikompres.`);
                App.ui.photos.push(p);
            } catch (e) { UI.toast(e.message, 'err'); }
        }
        UI.setErr('fotoInput', '');
        UI.$('#photoArea').innerHTML = photosHtml();
        wireDrop();
    }
    function wireDrop() {
        const drop = UI.$('#photoDrop');
        if (!drop) return;
        ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add('drag'); }));
        ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.remove('drag'); }));
        drop.addEventListener('drop', (e) => addFiles(e.dataTransfer.files));
    }

    M.lapor = {
        title: 'Isi Laporan Magang',
        subtitle: 'Waktu magang bebas — tidak terikat jam jadwal. Cukup catat kapan Anda benar-benar magang.',
        render() {
            const approved = App.data.pengajuan.filter((p) => p.status === 'Disetujui').map((p) => App.idx.jadwal.get(p.jadwalId)).filter(Boolean)
                .sort((a, b) => b.tanggal.localeCompare(a.tanggal));
            return `<div style="max-width:780px"><section class="card">
                <div class="card-head"><div><h3>Laporan kegiatan magang</h3><p>Ruang magang: ${esc(CONFIG.RUANG)}</p></div></div>
                <div class="card-body">
                    <div class="form-grid">
                        ${UI.field({ id: 'lpTanggal', label: 'Tanggal magang', type: 'date', required: true, value: UI.today(), attrs: `max="${UI.today()}"` })}
                        ${UI.field({ id: 'lpRuang', label: 'Ruang magang', value: CONFIG.RUANG, attrs: 'readonly' })}
                        ${UI.field({ id: 'lpMulai', label: 'Jam mulai', type: 'time', required: true })}
                        ${UI.field({ id: 'lpSelesai', label: 'Jam selesai', type: 'time', required: true })}
                    </div>
                    <p id="durInfo" class="hint" style="margin:-6px 0 16px;font-weight:500">Durasi: —</p>

                    ${UI.select({ id: 'lpJadwal', label: 'Terkait jadwal dosen (opsional)', placeholder: '— Tidak terikat jadwal tertentu —',
                        options: approved.map((j) => ({ v: j.id, l: `${UI.fmtDate(j.tanggal)} · ${j.judul}` })),
                        hint: 'Pilih bila kegiatan ini mengerjakan tugas dari jadwal yang sudah disetujui.' })}
                    <div id="lpJadwalNote" style="margin:-4px 0 16px"></div>

                    ${UI.textarea({ id: 'lpKegiatan', label: 'Deskripsi kegiatan', required: true, rows: 5, max: 2000, placeholder: 'Jelaskan apa saja yang Anda kerjakan selama magang…', hint: 'Minimal 20 karakter.' })}
                    ${UI.textarea({ id: 'lpKendala', label: 'Kendala (opsional)', rows: 2, max: 500, placeholder: 'Tuliskan bila ada hambatan.' })}

                    <div class="field"><label class="label">Bukti foto di ruang magang<span class="req">*</span></label>
                        <input id="fotoInput" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden">
                        <div id="photoArea">${photosHtml()}</div>
                        <p class="field-error" data-err="fotoInput" role="alert"></p>
                        <p class="hint">Foto dikompres otomatis sebelum diunggah. Minimal 1, maksimal ${CONFIG.MAX_FOTO} foto.</p></div>

                    <button class="btn btn-primary" id="lpSubmit" data-act="lp-submit">${icon('send')}Kirim laporan</button>
                </div></section></div>`;
        },
        bind(host) {
            ['lpMulai', 'lpSelesai'].forEach((id) => UI.$('#' + id, host).addEventListener('input', updateDuration));
            UI.$('#lpJadwal', host).addEventListener('change', updateJadwalNote);
            UI.$('#fotoInput', host).addEventListener('change', (e) => { addFiles(e.target.files); e.target.value = ''; });
            wireDrop();
        },
    };

    Actions['ph-del'] = (el) => {
        App.ui.photos.splice(Number(el.dataset.i), 1);
        UI.$('#photoArea').innerHTML = photosHtml();
        wireDrop();
    };

    Actions['lp-submit'] = (btn) => {
        UI.clearErrs();
        const tanggal = UI.val('lpTanggal'), a = UI.val('lpMulai'), b = UI.val('lpSelesai');
        const kegiatan = UI.val('lpKegiatan'), kendala = UI.val('lpKendala'), jadwalId = UI.val('lpJadwal');
        const errs = {};

        if (!tanggal) errs.lpTanggal = 'Tanggal wajib diisi.';
        else if (tanggal > UI.today()) errs.lpTanggal = 'Tanggal tidak boleh di masa depan.';
        else if ((UI.parseDate(UI.today()) - UI.parseDate(tanggal)) / 86400000 > MAX_HARI_LAMPAU) errs.lpTanggal = `Maksimal ${MAX_HARI_LAMPAU} hari ke belakang.`;

        if (!UI.isTime(a)) errs.lpMulai = 'Jam mulai wajib diisi.';
        if (!UI.isTime(b)) errs.lpSelesai = 'Jam selesai wajib diisi.';
        if (!errs.lpMulai && !errs.lpSelesai) {
            const s = UI.toMin(a), e = UI.toMin(b);
            if (e <= s) errs.lpSelesai = 'Jam selesai harus setelah jam mulai.';
            else if (e - s < 30) errs.lpSelesai = 'Durasi minimal 30 menit.';
            else if (e - s > 720) errs.lpSelesai = 'Durasi maksimal 12 jam per laporan.';
            else if (tanggal === UI.today() && e > UI.nowMin() + 5) errs.lpSelesai = 'Jam selesai belum terjadi. Isi laporan setelah kegiatan selesai.';
            else if (!errs.lpTanggal) {
                const clash = App.data.laporan.find((l) => l.tanggal === tanggal && l.status !== 'Ditolak' && s < UI.toMin(l.jamSelesai) && UI.toMin(l.jamMulai) < e);
                if (clash) errs.lpMulai = `Waktu tumpang tindih dengan laporan Anda lainnya (${clash.jamMulai}–${clash.jamSelesai}).`;
            }
        }
        if (kegiatan.length < 20) errs.lpKegiatan = 'Deskripsi kegiatan minimal 20 karakter.';
        if (kendala.length > 500) errs.lpKendala = 'Kendala maksimal 500 karakter.';
        if (!App.ui.photos.length) errs.fotoInput = 'Unggah minimal 1 foto bukti kehadiran di ruang magang.';
        if (!UI.showErrs(errs)) return;

        const backup = App.ui.photos;
        const fotos = backup.map(({ name, mime, data }) => ({ name, mime, data }));
        App.run(btn, async () => {
            App.ui.photos = [];                       // agar form kosong saat data dimuat ulang
            try {
                await App.mutate('kirimLaporan', { tanggal, jamMulai: a, jamSelesai: b, jadwalId, kegiatan, kendala, fotos }, 'Laporan terkirim. Menunggu validasi dosen.');
            } catch (e) { App.ui.photos = backup; throw e; }
            App.go('riwayat');
        });
    };

    /* ------------------------------------------------------------------ */
    /* RIWAYAT LAPORAN                                                    */
    /* ------------------------------------------------------------------ */
    M.riwayat = {
        title: 'Riwayat Laporan',
        subtitle: 'Semua laporan yang pernah Anda kirim beserta catatan dosen',
        render() {
            const rows = App.data.laporan;
            if (!rows.length) return V.card('Riwayat laporan', '', UI.empty('Belum ada laporan', 'Laporan yang Anda kirim akan muncul di sini.', 'file'));
            const body = rows.map((l) => `<tr>
                <td class="nowrap"><div class="cell-title">${UI.fmtDate(l.tanggal)}</div><div class="cell-sub">${esc(l.jamMulai)}–${esc(l.jamSelesai)} · ${UI.durasiText(l.durasiJam)}</div></td>
                <td><div class="clamp-2">${esc(l.kegiatan)}</div>${l.status === 'Ditolak' && l.catatanDosen ? `<div class="cell-sub" style="color:var(--err);margin-top:4px">Catatan dosen: ${esc(l.catatanDosen)}</div>` : ''}</td>
                <td>${V.thumbs(l)}</td><td>${UI.badge(l.status)}</td>
                <td><div class="actions"><button class="btn btn-sm btn-outline" data-act="lap-view" data-id="${esc(l.id)}">${icon('eye')}Detail</button>
                    ${l.status !== 'Tervalidasi' ? `<button class="btn btn-sm btn-danger" data-act="lap-hapus" data-id="${esc(l.id)}">${icon('trash')}</button>` : ''}</div></td></tr>`).join('');
            return V.card('Riwayat laporan', `${rows.length} laporan`, `<div class="table-wrap"><table class="table"><thead><tr>
                <th>Tanggal &amp; waktu</th><th>Kegiatan</th><th>Foto</th><th>Status</th><th></th></tr></thead><tbody>${body}</tbody></table></div>`);
        },
    };

    // dipakai juga oleh admin (read-only)
    Actions['lap-view'] = (el) => V.openLaporan(el.dataset.id, { canValidate: false });

    Actions['lap-hapus'] = async (btn) => {
        const ok = await UI.confirmDialog({ title: 'Hapus laporan?', message: 'Laporan beserta foto buktinya akan dihapus permanen.', confirmText: 'Hapus', danger: true });
        if (ok) App.run(btn, () => App.mutate('hapusLaporan', { id: btn.dataset.id }, 'Laporan dihapus.'));
    };
})();
