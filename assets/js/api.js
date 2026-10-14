/* ==========================================================================
   API — pembungkus fetch() GET & POST ke Google Apps Script
   + MODE DEMO otomatis bila backend tidak tersedia.
   Semua error dinormalisasi menjadi pesan yang bisa dipahami pengguna.
   ========================================================================== */
class ApiError extends Error {
    constructor(message, code) { super(message); this.name = 'ApiError'; this.code = code || 'ERROR'; }
}

const Api = (() => {
    const isConfigured = () => /^https?:\/\//i.test(CONFIG.SCRIPT_URL) && !/GANTI|YOUR_|URL_ANDA/i.test(CONFIG.SCRIPT_URL);

    /* ================================================================
       MODE DEMO — data contoh di memori, aktif saat backend gagal.
       ================================================================ */
    let _demo = false;
    const isDemo = () => _demo;

    const TS = () => new Date().toISOString();
    const uid = () => 'demo-' + Math.random().toString(36).slice(2, 10);

    /* Seed data */
    const DEMO = {
        pemagang: [
            { id: 'p1', nim: '2023010001', nama: 'Budi Santoso', prodi: 'Informatika', dosenKey: 'DOSEN001', timestamp: '2026-09-01T08:00:00Z' },
            { id: 'p2', nim: '2023010002', nama: 'Siti Aminah', prodi: 'Sistem Informasi', dosenKey: 'DOSEN001', timestamp: '2026-09-01T08:00:00Z' },
            { id: 'p3', nim: '2023010003', nama: 'Rina Wulandari', prodi: 'Informatika', dosenKey: 'DOSEN001', timestamp: '2026-09-05T08:00:00Z' },
        ],
        jadwal: [
            { id: 'j1', dosenKey: 'DOSEN001', judul: 'Pengarsipan Dokumen Akademik', tanggal: '2026-10-20', jamMulai: '08:00', jamSelesai: '12:00', ruang: 'Wakil Rektor 1', tugas: 'Membantu pengarsipan dan digitalisasi dokumen akademik fakultas. Pastikan setiap folder tertata rapi sesuai kategori.', kuota: 3, terisi: 1, status: 'Buka', timestamp: '2026-10-01T08:00:00Z' },
            { id: 'j2', dosenKey: 'DOSEN001', judul: 'Input Data Mahasiswa Baru', tanggal: '2026-10-22', jamMulai: '09:00', jamSelesai: '11:30', ruang: 'Wakil Rektor 1', tugas: 'Menginput data mahasiswa baru ke dalam sistem akademik. Periksa kelengkapan berkas dan kebenaran data.', kuota: 2, terisi: 0, status: 'Buka', timestamp: '2026-10-02T08:00:00Z' },
            { id: 'j3', dosenKey: 'DOSEN001', judul: 'Persiapan Rapat Senat', tanggal: '2026-10-25', jamMulai: '13:00', jamSelesai: '16:00', ruang: 'Wakil Rektor 1', tugas: 'Menyiapkan berkas dan ruangan untuk rapat senat akademik. Fotokopi materi presentasi dan atur tata letak kursi.', kuota: 2, terisi: 0, status: 'Buka', timestamp: '2026-10-03T08:00:00Z' },
        ],
        pengajuan: [
            { id: 'pj1', jadwalId: 'j1', nim: '2023010001', nama: 'Budi Santoso', catatan: 'Saya bisa hadir penuh.', status: 'Disetujui', timestamp: '2026-10-10T08:00:00Z', keputusanAt: '2026-10-10T10:00:00Z' },
        ],
        laporan: [
            { id: 'l1', nim: '2023010001', nama: 'Budi Santoso', jadwalId: 'j1', tanggal: '2026-10-12', jamMulai: '08:00', jamSelesai: '11:30', durasiJam: 3.5, ruang: 'Wakil Rektor 1', kegiatan: 'Mengarsipkan dokumen akademik semester ganjil 2025/2026. Menata berkas ke dalam folder sesuai kategori dan memindai dokumen penting ke format digital. Semua dokumen sudah tertata rapi.', kendala: '', fotoIds: '', status: 'Tervalidasi', catatanDosen: '', validatorKey: 'DOSEN001', timestamp: '2026-10-12T11:35:00Z', validatedAt: '2026-10-12T14:00:00Z' },
            { id: 'l2', nim: '2023010001', nama: 'Budi Santoso', jadwalId: '', tanggal: '2026-10-13', jamMulai: '09:00', jamSelesai: '12:00', durasiJam: 3, ruang: 'Wakil Rektor 1', kegiatan: 'Membantu menyusun jadwal ujian akhir semester untuk program studi Informatika. Menginput data ke dalam spreadsheet dan mengecek bentrokan jadwal ruangan.', kendala: 'Beberapa data jadwal dosen belum lengkap.', fotoIds: '', status: 'Menunggu', catatanDosen: '', validatorKey: '', timestamp: '2026-10-13T12:05:00Z', validatedAt: '' },
        ],
    };

    /* Deep clone seed agar dapat di-reset */
    function cloneData() { return JSON.parse(JSON.stringify(DEMO)); }
    let _data = cloneData();

    function activateDemo() {
        if (_demo) return;
        _demo = true;
        _data = cloneData();
        console.warn('[Portal Magang] MODE DEMO aktif — data contoh di memori, tidak terhubung ke server.');
    }

    /** Simulasi response untuk setiap action */
    function demoGet(action, params) {
        switch (action) {
            case 'pemagangPublic':
                return { status: 'ok', data: _data.pemagang.map(p => ({ id: p.id, nama: p.nama, prodi: p.prodi })) };
            case 'bundle': {
                const role = params.role;
                const key = params.key;
                const nim = params.nim;
                if (role === 'DOSEN') {
                    return { status: 'ok', data: {
                        pemagang: _data.pemagang.filter(p => p.dosenKey === key),
                        jadwal: _data.jadwal.filter(j => j.dosenKey === key),
                        pengajuan: _data.pengajuan,
                        laporan: _data.laporan,
                    }};
                }
                if (role === 'MHS') {
                    const me = _data.pemagang.find(p => p.nim === nim);
                    if (!me) throw new ApiError('Pemagang tidak terdaftar.', 'SERVER');
                    return { status: 'ok', data: {
                        pemagang: [me],
                        jadwal: _data.jadwal.filter(j => j.dosenKey === me.dosenKey),
                        pengajuan: _data.pengajuan.filter(p => p.nim === nim),
                        laporan: _data.laporan.filter(l => l.nim === nim),
                    }};
                }
                /* ADMIN */
                return { status: 'ok', data: { ..._data } };
            }
            case 'ping':
                return { status: 'ok', spreadsheet: 'Portal Magang DB (DEMO)', sheets: ['Pemagang', 'Jadwal', 'Pengajuan', 'Laporan'], versi: '2.0.0-demo', ruang: CONFIG.RUANG, waktu: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) };
            default:
                throw new ApiError(`Action tidak dikenali: ${action}`, 'SERVER');
        }
    }

    function demoPost(action, data) {
        switch (action) {
            case 'loginPemagang': {
                const p = _data.pemagang.find(x => x.id === data.pid);
                if (!p) throw new ApiError('Nama tidak ditemukan.', 'SERVER');
                if (p.nim !== data.nim) throw new ApiError('NIM tidak sesuai dengan data yang didaftarkan dosen untuk nama tersebut.', 'SERVER');
                return { status: 'ok', profile: { id: p.id, nim: p.nim, nama: p.nama, prodi: p.prodi, dosenKey: p.dosenKey } };
            }
            case 'tambahPemagang': {
                const items = data.items || [];
                let added = 0;
                const skipped = [];
                items.forEach(it => {
                    if (_data.pemagang.some(p => p.nim === it.nim)) {
                        skipped.push({ nim: it.nim, alasan: 'NIM sudah terdaftar' });
                    } else {
                        _data.pemagang.push({ id: uid(), nim: it.nim, nama: it.nama, prodi: it.prodi || '', dosenKey: data.key || 'DOSEN001', timestamp: TS() });
                        added++;
                    }
                });
                return { status: 'ok', ditambahkan: added, dilewati: skipped };
            }
            case 'hapusPemagang': {
                _data.pemagang = _data.pemagang.filter(p => p.id !== data.id);
                _data.pengajuan = _data.pengajuan.filter(pj => {
                    const pm = _data.pemagang.find(pp => pp.nim === pj.nim);
                    return !!pm || pj.id; // keep all for now, simplified
                });
                return { status: 'ok' };
            }
            case 'buatJadwal': {
                const j = {
                    id: uid(), dosenKey: data.key || 'DOSEN001',
                    judul: data.judul, tanggal: data.tanggal, jamMulai: data.jamMulai, jamSelesai: data.jamSelesai,
                    ruang: CONFIG.RUANG, tugas: data.tugas, kuota: +data.kuota || 2, terisi: 0, status: 'Buka', timestamp: TS(),
                };
                /* Bentrok check */
                const clash = _data.jadwal.find(x =>
                    x.tanggal === j.tanggal && x.status === 'Buka' &&
                    j.jamMulai < x.jamSelesai && x.jamMulai < j.jamSelesai);
                if (clash) throw new ApiError(`Bentrok dengan jadwal "${clash.judul}" di jam ${clash.jamMulai}–${clash.jamSelesai}.`, 'SERVER');
                _data.jadwal.push(j);
                return { status: 'ok', id: j.id };
            }
            case 'hapusJadwal': {
                _data.jadwal = _data.jadwal.filter(j => j.id !== data.id);
                return { status: 'ok' };
            }
            case 'ubahStatusJadwal': {
                const j = _data.jadwal.find(x => x.id === data.id);
                if (j) j.status = data.status;
                return { status: 'ok' };
            }
            case 'ajukanJadwal': {
                const pj = {
                    id: uid(), jadwalId: data.jadwalId, nim: data.nim, nama: '',
                    catatan: data.catatan || '', status: 'Diajukan', timestamp: TS(), keputusanAt: '',
                };
                const me = _data.pemagang.find(p => p.nim === data.nim);
                if (me) pj.nama = me.nama;
                _data.pengajuan.push(pj);
                return { status: 'ok', id: pj.id };
            }
            case 'batalkanPengajuan': {
                const pj = _data.pengajuan.find(x => x.id === data.id);
                if (pj) { pj.status = 'Dibatalkan'; pj.keputusanAt = TS(); }
                return { status: 'ok' };
            }
            case 'putusPengajuan': {
                const pj = _data.pengajuan.find(x => x.id === data.id);
                if (pj) {
                    pj.status = data.status;
                    pj.keputusanAt = TS();
                    if (data.status === 'Disetujui') {
                        const j = _data.jadwal.find(jj => jj.id === pj.jadwalId);
                        if (j) j.terisi = (j.terisi || 0) + 1;
                    }
                }
                return { status: 'ok' };
            }
            case 'kirimLaporan': {
                const dur = (() => {
                    const [h1, m1] = data.jamMulai.split(':').map(Number);
                    const [h2, m2] = data.jamSelesai.split(':').map(Number);
                    return Math.round(((h2 * 60 + m2) - (h1 * 60 + m1)) / 60 * 100) / 100;
                })();
                const l = {
                    id: uid(), nim: data.nim, nama: '', jadwalId: data.jadwalId || '',
                    tanggal: data.tanggal, jamMulai: data.jamMulai, jamSelesai: data.jamSelesai,
                    durasiJam: dur, ruang: CONFIG.RUANG, kegiatan: data.kegiatan,
                    kendala: data.kendala || '', fotoIds: '', status: 'Menunggu',
                    catatanDosen: '', validatorKey: '', timestamp: TS(), validatedAt: '',
                };
                const me = _data.pemagang.find(p => p.nim === data.nim);
                if (me) l.nama = me.nama;
                /* Simpan foto sebagai id dummy */
                if (data.fotos && data.fotos.length) {
                    l.fotoIds = data.fotos.map(() => 'demo-foto-' + Math.random().toString(36).slice(2, 8)).join(',');
                }
                _data.laporan.push(l);
                return { status: 'ok', id: l.id };
            }
            case 'hapusLaporan': {
                _data.laporan = _data.laporan.filter(l => l.id !== data.id);
                return { status: 'ok' };
            }
            case 'validasiLaporan': {
                const l = _data.laporan.find(x => x.id === data.id);
                if (l) {
                    l.status = data.status;
                    l.catatanDosen = data.catatan || '';
                    l.validatorKey = data.key || 'DOSEN001';
                    l.validatedAt = TS();
                }
                return { status: 'ok' };
            }
            default:
                return { status: 'ok' };
        }
    }

    /* ================================================================
       REAL API (fetch ke Apps Script)
       ================================================================ */
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
        if (_demo) return Promise.resolve(demoGet(action, params));
        const u = new URL(CONFIG.SCRIPT_URL);
        u.searchParams.set('action', action);
        Object.entries(params).forEach(([k, v]) => { if (v !== undefined && v !== null && v !== '') u.searchParams.set(k, v); });
        return request(u.toString(), { method: 'GET' });
    }

    /** POST -> body JSON (text/plain agar tidak memicu preflight CORS) */
    function post(action, data = {}) {
        if (_demo) return Promise.resolve(demoPost(action, data));
        return request(CONFIG.SCRIPT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'text/plain;charset=utf-8' },
            body: JSON.stringify({ action, ...data }),
        });
    }

    /** Cek koneksi pertama, jika gagal aktifkan demo. */
    async function tryConnect() {
        if (!isConfigured()) { activateDemo(); return; }
        try {
            await get('pemagangPublic');
        } catch (e) {
            activateDemo();
        }
    }

    return { get, post, isConfigured, isDemo, activateDemo, tryConnect };
})();
