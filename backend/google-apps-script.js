/**
 * ============================================================================
 *  PORTAL MAGANG — Google Apps Script Backend  (v2.0)
 * ============================================================================
 *  Fungsi : REST API sederhana (doGet / doPost) dengan Google Sheets sebagai
 *           database dan Google Drive sebagai penyimpanan foto bukti.
 *
 *  CARA PASANG (ringkas — detail lengkap ada di README.md):
 *   1. Buka https://script.google.com  -> project Anda -> ganti SELURUH kode
 *      dengan isi file ini.
 *   2. Pastikan SPREADSHEET_ID di bawah benar (cukup ID-nya; URL penuh pun
 *      otomatis dipotong).
 *   3. Pilih fungsi `setup` di toolbar -> klik "Jalankan" -> izinkan akses
 *      (Sheets + Drive). Fungsi ini membuat semua sheet & folder foto.
 *   4. Terapkan -> Kelola deployment -> ikon pensil -> Versi: "Versi baru"
 *      -> Terapkan.  (URL /exec tidak berubah.)
 *
 *  Deployment harus:  Jalankan sebagai = Saya | Yang memiliki akses = Siapa saja
 * ============================================================================
 */

// ============================================================================
// KONFIGURASI
// ============================================================================

/**
 * >>> ID SPREADSHEET <<<
 * Ambil dari URL:  https://docs.google.com/spreadsheets/d/[ID_INI]/edit
 * Jika script dibuat lewat menu "Ekstensi > Apps Script" DI DALAM spreadsheet,
 * Anda boleh mengosongkan nilai ini ('').
 */
const SPREADSHEET_ID = '11Ibq9Vb87abRz3G4pZYC9xkNqWvgDp7d0t_idUDgDHw';

const APP_VERSION    = '2.0.0';
const TZ             = 'Asia/Jakarta';
const RUANG_DEFAULT  = 'Wakil Rektor 1';      // ruang magang tunggal
const FOLDER_NAME    = 'Portal Magang - Bukti Foto';
const MAX_FOTO       = 3;                      // maks foto per laporan
const MAX_FOTO_BYTES = 3 * 1024 * 1024;        // maks 3 MB per foto (setelah dikompres di browser)
const FOTO_MIME      = ['image/jpeg', 'image/png', 'image/webp'];
const MAX_HARI_LAMPAU = 180;                   // laporan boleh diisi mundur s.d. 180 hari

/** Skema sheet (urutan kolom = urutan header di baris 1). */
const SCHEMA = {
  Pemagang : ['id', 'nim', 'nama', 'prodi', 'dosenKey', 'timestamp'],
  Jadwal   : ['id', 'dosenKey', 'judul', 'tanggal', 'jamMulai', 'jamSelesai', 'ruang', 'tugas', 'kuota', 'status', 'timestamp'],
  Pengajuan: ['id', 'jadwalId', 'nim', 'nama', 'catatan', 'status', 'timestamp', 'keputusanAt'],
  Laporan  : ['id', 'nim', 'nama', 'jadwalId', 'tanggal', 'jamMulai', 'jamSelesai', 'durasiJam', 'ruang',
              'kegiatan', 'kendala', 'fotoIds', 'status', 'catatanDosen', 'validatorKey', 'timestamp', 'validatedAt'],
};

// ============================================================================
// ENTRY POINT
// ============================================================================

/** Daftar aksi. method: GET/POST, write: true -> dijalankan di dalam lock. */
const ROUTES = {
  ping            : { method: 'GET',  write: false, fn: ping_ },
  pemagangPublic  : { method: 'GET',  write: false, fn: pemagangPublic_ },
  bundle          : { method: 'GET',  write: false, fn: bundle_ },

  loginPemagang   : { method: 'POST', write: false, fn: loginPemagang_ },
  tambahPemagang  : { method: 'POST', write: true,  fn: tambahPemagang_ },
  hapusPemagang   : { method: 'POST', write: true,  fn: hapusPemagang_ },

  buatJadwal      : { method: 'POST', write: true,  fn: buatJadwal_ },
  ubahStatusJadwal: { method: 'POST', write: true,  fn: ubahStatusJadwal_ },
  hapusJadwal     : { method: 'POST', write: true,  fn: hapusJadwal_ },

  ajukanJadwal    : { method: 'POST', write: true,  fn: ajukanJadwal_ },
  batalkanPengajuan: { method: 'POST', write: true, fn: batalkanPengajuan_ },
  putusPengajuan  : { method: 'POST', write: true,  fn: putusPengajuan_ },

  kirimLaporan    : { method: 'POST', write: true,  fn: kirimLaporan_ },
  hapusLaporan    : { method: 'POST', write: true,  fn: hapusLaporan_ },
  validasiLaporan : { method: 'POST', write: true,  fn: validasiLaporan_ },
};

function doGet(e) {
  return handle_((e && e.parameter) || {}, 'GET');
}

function doPost(e) {
  let body;
  try {
    body = JSON.parse(e.postData.contents);
  } catch (err) {
    return out_({ status: 'error', message: 'Payload bukan JSON yang valid.' });
  }
  return handle_(body || {}, 'POST');
}

function handle_(req, method) {
  try {
    const action = String(req.action || '');
    const route  = ROUTES[action];
    if (!route) throw new Error('Aksi tidak dikenali: "' + action + '".');
    if (route.method !== method) throw new Error('Aksi "' + action + '" harus memakai ' + route.method + '.');

    let result;
    if (route.write) {
      const lock = LockService.getScriptLock();
      lock.waitLock(25000);
      try { result = route.fn(req); } finally { lock.releaseLock(); }
    } else {
      result = route.fn(req);
    }
    result = result || {};
    result.status = 'ok';
    return out_(result);
  } catch (err) {
    return out_({ status: 'error', message: (err && err.message) ? err.message : String(err) });
  }
}

function out_(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}

/**
 * Jalankan SEKALI dari editor (pilih `setup` -> Jalankan).
 * Membuat semua sheet + folder foto dan memicu dialog izin Sheets & Drive.
 */
function setup() {
  const ss = getSS_();
  Object.keys(SCHEMA).forEach(function (n) { ensureSheet_(n); });
  const folder = getFolder_();
  Logger.log('OK. Spreadsheet: "' + ss.getName() + '" | Folder foto: ' + folder.getUrl());
}

// ============================================================================
// UTILITAS UMUM
// ============================================================================

function getSS_() {
  const raw = String(SPREADSHEET_ID || '').trim();
  const m   = raw.match(/\/d\/([a-zA-Z0-9_-]+)/);             // toleran jika yang ditempel URL penuh
  const id  = m ? m[1] : raw.split(/[\/?#]/)[0];
  if (id && id.indexOf('GANTI') === -1 && id.indexOf('ISI_') === -1) {
    try {
      return SpreadsheetApp.openById(id);
    } catch (err) {
      throw new Error('Spreadsheet tidak dapat dibuka. Periksa SPREADSHEET_ID dan pastikan akun pemilik script punya akses. (' + err.message + ')');
    }
  }
  const active = SpreadsheetApp.getActiveSpreadsheet();
  if (!active) throw new Error('SPREADSHEET_ID belum diisi di google-apps-script.js.');
  return active;
}

function genId_() {
  return Utilities.getUuid().replace(/-/g, '').substring(0, 12);
}

function nowIso_() {
  return Utilities.formatDate(new Date(), TZ, "yyyy-MM-dd'T'HH:mm:ssXXX");
}
function todayStr_() {
  return Utilities.formatDate(new Date(), TZ, 'yyyy-MM-dd');
}
function nowMin_() {
  return toMin_(Utilities.formatDate(new Date(), TZ, 'HH:mm'));
}

function isDate_(s) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(String(s))) return false;
  const p = String(s).split('-').map(Number);
  const d = new Date(Date.UTC(p[0], p[1] - 1, p[2]));
  return d.getUTCFullYear() === p[0] && d.getUTCMonth() === p[1] - 1 && d.getUTCDate() === p[2];
}
function isTime_(s) { return /^([01]\d|2[0-3]):[0-5]\d$/.test(String(s)); }
function toMin_(t)  { const p = String(t).split(':'); return Number(p[0]) * 60 + Number(p[1]); }
function fmtMin_(m) { return ('0' + Math.floor(m / 60)).slice(-2) + ':' + ('0' + (m % 60)).slice(-2); }
function dayDiff_(a, b) { // selisih hari a - b (yyyy-MM-dd)
  const pa = a.split('-').map(Number), pb = b.split('-').map(Number);
  return Math.round((Date.UTC(pa[0], pa[1] - 1, pa[2]) - Date.UTC(pb[0], pb[1] - 1, pb[2])) / 86400000);
}
function overlap_(s1, e1, s2, e2) { return s1 < e2 && s2 < e1; }

function clean_(s)  { return String(s == null ? '' : s).replace(/\s+/g, ' ').trim(); }
function cleanMultiline_(s) {
  return String(s == null ? '' : s).replace(/\r/g, '').replace(/[ \t]+/g, ' ').replace(/\n{3,}/g, '\n\n').trim();
}
function need_(cond, msg) { if (!cond) throw new Error(msg); }

// ============================================================================
// AKSES SHEET
// ============================================================================

/** Pastikan sheet ada & header benar. Sheet lama dengan struktur berbeda DIARSIPKAN (tidak dihapus). */
function ensureSheet_(name) {
  const ss = getSS_();
  const headers = SCHEMA[name];
  let sh = ss.getSheetByName(name);

  if (sh) {
    if (sh.getLastRow() === 0 || sh.getLastColumn() === 0) {
      writeHeader_(sh, headers);
      return sh;
    }
    const width = Math.max(sh.getLastColumn(), headers.length);
    const first = sh.getRange(1, 1, 1, width).getValues()[0].map(function (c) { return String(c).trim(); });
    let same = true;
    for (let i = 0; i < width; i++) {
      if (first[i] !== (headers[i] || '')) { same = false; break; }
    }
    if (same) return sh;

    sh.setName(name + '_arsip_' + Utilities.formatDate(new Date(), TZ, 'yyyyMMdd_HHmmss'));
    sh = null;
  }

  sh = ss.insertSheet(name);
  writeHeader_(sh, headers);
  return sh;
}

function writeHeader_(sh, headers) {
  const maxRows = sh.getMaxRows();
  // Format TEKS agar tanggal/jam/NIM tidak diubah otomatis oleh Sheets menjadi Date/angka.
  if (maxRows > 1) sh.getRange(2, 1, maxRows - 1, headers.length).setNumberFormat('@');
  sh.getRange(1, 1, 1, headers.length).setNumberFormat('@').setValues([headers])
    .setFontWeight('bold').setBackground('#1e3a8a').setFontColor('#ffffff');
  sh.setFrozenRows(1);
}

function readRows_(name) {
  const sh = ensureSheet_(name);
  const headers = SCHEMA[name];
  const last = sh.getLastRow();
  if (last < 2) return [];
  const values = sh.getRange(2, 1, last - 1, headers.length).getValues();
  const rows = [];
  values.forEach(function (r, i) {
    const o = { _row: i + 2 };
    headers.forEach(function (h, j) { o[h] = norm_(r[j]); });
    if (o.id) rows.push(o);
  });
  return rows;
}

function norm_(v) {
  if (v instanceof Date) return Utilities.formatDate(v, TZ, "yyyy-MM-dd'T'HH:mm:ss");
  return v === null || v === undefined ? '' : String(v);
}

function appendRow_(name, obj) {
  const sh = ensureSheet_(name);
  const headers = SCHEMA[name];
  const row = headers.map(function (h) { return obj[h] === undefined || obj[h] === null ? '' : String(obj[h]); });
  const next = sh.getLastRow() + 1;
  if (next > sh.getMaxRows()) sh.insertRowsAfter(sh.getMaxRows(), 200);
  sh.getRange(next, 1, 1, headers.length).setNumberFormat('@').setValues([row]);
}

function updateRow_(name, rowObj, patch) {
  const sh = ensureSheet_(name);
  const headers = SCHEMA[name];
  Object.keys(patch).forEach(function (k) {
    const col = headers.indexOf(k);
    if (col < 0) return;
    sh.getRange(rowObj._row, col + 1).setNumberFormat('@').setValue(String(patch[k]));
    rowObj[k] = String(patch[k]);
  });
}

function deleteRow_(name, rowObj) {
  ensureSheet_(name).deleteRow(rowObj._row);
}

function pub_(o) {                       // buang field internal sebelum dikirim ke klien
  const c = {};
  Object.keys(o).forEach(function (k) { if (k !== '_row') c[k] = o[k]; });
  return c;
}

// ============================================================================
// OTORISASI RINGAN (berbasis key — sesuai konsep login tanpa password)
// ============================================================================

function normKey_(k) { return String(k || '').trim().toUpperCase(); }

function requireRole_(key, role) {
  const k = normKey_(key);
  need_(new RegExp('^' + role + '.+').test(k), 'Akses ditolak: key tidak valid untuk peran ' + role + '.');
  return k;
}

function requirePemagang_(nim) {
  const n = clean_(nim);
  need_(/^[0-9]{6,15}$/.test(n), 'NIM tidak valid.');
  const me = readRows_('Pemagang').filter(function (p) { return p.nim === n; })[0];
  need_(me, 'Pemagang dengan NIM tersebut tidak terdaftar.');
  return me;
}

// ============================================================================
// ENDPOINT — UMUM
// ============================================================================

function ping_() {
  const ss = getSS_();
  Object.keys(SCHEMA).forEach(function (n) { ensureSheet_(n); });
  return {
    versi: APP_VERSION,
    spreadsheet: ss.getName(),
    sheets: Object.keys(SCHEMA),
    ruang: RUANG_DEFAULT,
    waktu: nowIso_(),
  };
}

/** Daftar nama untuk dropdown login. Sengaja TIDAK menyertakan NIM. */
function pemagangPublic_() {
  const list = readRows_('Pemagang')
    .map(function (p) { return { id: p.id, nama: p.nama, prodi: p.prodi }; })
    .sort(function (a, b) { return a.nama.localeCompare(b.nama); });
  return { data: list };
}

function loginPemagang_(req) {
  const id  = clean_(req.pid);
  const nim = clean_(req.nim);
  need_(id, 'Pilih nama Anda terlebih dahulu.');
  need_(/^[0-9]{6,15}$/.test(nim), 'NIM harus berupa angka 6–15 digit.');
  const me = readRows_('Pemagang').filter(function (p) { return p.id === id; })[0];
  need_(me, 'Nama tidak ditemukan. Hubungi dosen pembimbing.');
  need_(me.nim === nim, 'NIM tidak sesuai dengan data yang didaftarkan dosen untuk nama tersebut.');
  return { profile: pub_(me) };
}

/** Seluruh data yang dibutuhkan satu dashboard (1 request = lebih cepat). */
function bundle_(req) {
  const role = String(req.role || '').toUpperCase();
  const pemagang  = readRows_('Pemagang');
  const jadwal    = readRows_('Jadwal');
  const pengajuan = readRows_('Pengajuan');
  const laporan   = readRows_('Laporan');

  const terisi = {};
  pengajuan.forEach(function (p) {
    if (p.status === 'Diajukan' || p.status === 'Disetujui') terisi[p.jadwalId] = (terisi[p.jadwalId] || 0) + 1;
  });
  jadwal.forEach(function (j) { j.terisi = terisi[j.id] || 0; });

  let data;
  if (role === 'ADMIN') {
    requireRole_(req.key, 'ADMIN');
    data = { pemagang: pemagang, jadwal: jadwal, pengajuan: pengajuan, laporan: laporan };

  } else if (role === 'DOSEN') {
    const key = requireRole_(req.key, 'DOSEN');
    const myP = pemagang.filter(function (p) { return p.dosenKey === key; });
    const nims = {}; myP.forEach(function (p) { nims[p.nim] = true; });
    const myJ = jadwal.filter(function (j) { return j.dosenKey === key; });
    const jids = {}; myJ.forEach(function (j) { jids[j.id] = true; });
    data = {
      pemagang : myP,
      jadwal   : myJ,
      pengajuan: pengajuan.filter(function (p) { return jids[p.jadwalId]; }),
      laporan  : laporan.filter(function (l) { return nims[l.nim]; }),
    };

  } else if (role === 'MHS') {
    const me = requirePemagang_(req.nim);
    data = {
      pemagang : [me],
      jadwal   : jadwal.filter(function (j) { return j.dosenKey === me.dosenKey; }),
      pengajuan: pengajuan.filter(function (p) { return p.nim === me.nim; }),
      laporan  : laporan.filter(function (l) { return l.nim === me.nim; }),
    };

  } else {
    throw new Error('Peran tidak dikenali.');
  }

  Object.keys(data).forEach(function (k) { data[k] = data[k].map(pub_); });
  return { data: data, waktu: nowIso_() };
}

// ============================================================================
// PEMAGANG (didaftarkan dosen)
// ============================================================================

function tambahPemagang_(req) {
  const key = requireRole_(req.key, 'DOSEN');
  const items = req.items;
  need_(Array.isArray(items) && items.length > 0, 'Tidak ada data pemagang yang dikirim.');
  need_(items.length <= 100, 'Maksimal 100 pemagang sekali input.');

  const existing = {};
  readRows_('Pemagang').forEach(function (p) { existing[p.nim] = true; });

  const added = [], skipped = [];
  items.forEach(function (it) {
    const nim   = clean_(it.nim);
    const nama  = clean_(it.nama);
    const prodi = clean_(it.prodi);
    let why = '';
    if (!/^[0-9]{6,15}$/.test(nim)) why = 'NIM harus angka 6–15 digit';
    else if (!/^[\p{L}][\p{L} .,'’-]{1,59}$/u.test(nama)) why = 'Nama tidak valid (2–60 huruf)';
    else if (prodi.length > 60) why = 'Program studi terlalu panjang';
    else if (existing[nim]) why = 'NIM sudah terdaftar';

    if (why) { skipped.push({ nim: nim, nama: nama, alasan: why }); return; }
    existing[nim] = true;
    appendRow_('Pemagang', { id: genId_(), nim: nim, nama: nama, prodi: prodi, dosenKey: key, timestamp: nowIso_() });
    added.push(nim);
  });

  need_(added.length > 0, 'Tidak ada pemagang yang ditambahkan: ' + (skipped[0] ? skipped[0].alasan + '.' : ''));
  return { ditambahkan: added.length, dilewati: skipped };
}

function hapusPemagang_(req) {
  const key = requireRole_(req.key, 'DOSEN');
  const p = readRows_('Pemagang').filter(function (x) { return x.id === clean_(req.id); })[0];
  need_(p, 'Data pemagang tidak ditemukan.');
  need_(p.dosenKey === key, 'Anda hanya dapat menghapus pemagang milik Anda sendiri.');
  const punyaLaporan = readRows_('Laporan').some(function (l) { return l.nim === p.nim; });
  need_(!punyaLaporan, 'Pemagang ini sudah memiliki laporan sehingga tidak dapat dihapus.');

  // hapus pengajuan miliknya dari bawah ke atas agar nomor baris tidak bergeser
  readRows_('Pengajuan').filter(function (x) { return x.nim === p.nim; })
    .sort(function (a, b) { return b._row - a._row; })
    .forEach(function (x) { deleteRow_('Pengajuan', x); });
  deleteRow_('Pemagang', p);
  return {};
}

// ============================================================================
// JADWAL (dibuat dosen)
// ============================================================================

function buatJadwal_(req) {
  const key = requireRole_(req.key, 'DOSEN');
  const judul = clean_(req.judul);
  const tugas = cleanMultiline_(req.tugas);
  const tanggal = clean_(req.tanggal);
  const jamMulai = clean_(req.jamMulai);
  const jamSelesai = clean_(req.jamSelesai);
  const kuota = Number(req.kuota);

  need_(judul.length >= 3 && judul.length <= 80, 'Judul jadwal 3–80 karakter.');
  need_(tugas.length >= 10 && tugas.length <= 1000, 'Deskripsi tugas 10–1000 karakter.');
  need_(isDate_(tanggal), 'Tanggal tidak valid.');
  need_(tanggal >= todayStr_(), 'Tanggal jadwal tidak boleh di masa lalu.');
  need_(isTime_(jamMulai) && isTime_(jamSelesai), 'Format jam tidak valid (HH:MM).');
  const s = toMin_(jamMulai), e = toMin_(jamSelesai);
  need_(e > s, 'Jam selesai harus setelah jam mulai.');
  need_(e - s >= 30, 'Durasi jadwal minimal 30 menit.');
  need_(tanggal !== todayStr_() || e > nowMin_(), 'Jam selesai sudah lewat untuk hari ini.');
  need_(Number.isInteger(kuota) && kuota >= 1 && kuota <= 50, 'Kuota harus bilangan bulat 1–50.');

  // Hanya ada satu ruang -> dua jadwal tidak boleh bertabrakan waktu.
  const bentrok = readRows_('Jadwal').filter(function (j) {
    return j.tanggal === tanggal && j.status !== 'Tutup' &&
           overlap_(s, e, toMin_(j.jamMulai), toMin_(j.jamSelesai));
  })[0];
  need_(!bentrok, 'Bentrok dengan jadwal "' + (bentrok && bentrok.judul) + '" (' +
        (bentrok && bentrok.jamMulai) + '–' + (bentrok && bentrok.jamSelesai) + ') di ' + RUANG_DEFAULT +
        '. Pilih jam lain atau tutup jadwal tersebut.');

  const id = genId_();
  appendRow_('Jadwal', {
    id: id, dosenKey: key, judul: judul, tanggal: tanggal, jamMulai: jamMulai, jamSelesai: jamSelesai,
    ruang: RUANG_DEFAULT, tugas: tugas, kuota: kuota, status: 'Buka', timestamp: nowIso_(),
  });
  return { id: id };
}

function findJadwalOwned_(req, key) {
  const j = readRows_('Jadwal').filter(function (x) { return x.id === clean_(req.id); })[0];
  need_(j, 'Jadwal tidak ditemukan.');
  need_(j.dosenKey === key, 'Anda hanya dapat mengubah jadwal milik Anda sendiri.');
  return j;
}

function ubahStatusJadwal_(req) {
  const key = requireRole_(req.key, 'DOSEN');
  const status = clean_(req.status);
  need_(status === 'Buka' || status === 'Tutup', 'Status jadwal tidak valid.');
  const j = findJadwalOwned_(req, key);
  if (status === 'Buka') {
    // jangan buka kembali bila akan bentrok dengan jadwal lain
    const s = toMin_(j.jamMulai), e = toMin_(j.jamSelesai);
    const bentrok = readRows_('Jadwal').filter(function (x) {
      return x.id !== j.id && x.tanggal === j.tanggal && x.status !== 'Tutup' &&
             overlap_(s, e, toMin_(x.jamMulai), toMin_(x.jamSelesai));
    })[0];
    need_(!bentrok, 'Tidak dapat dibuka: bentrok dengan jadwal "' + (bentrok && bentrok.judul) + '".');
  }
  updateRow_('Jadwal', j, { status: status });
  return {};
}

function hapusJadwal_(req) {
  const role = roleOfKey_(req.key);
  need_(role === 'DOSEN' || role === 'ADMIN', 'Akses ditolak.');
  const j = readRows_('Jadwal').filter(function (x) { return x.id === clean_(req.id); })[0];
  need_(j, 'Jadwal tidak ditemukan.');
  if (role === 'DOSEN') need_(j.dosenKey === normKey_(req.key), 'Anda hanya dapat menghapus jadwal milik Anda sendiri.');

  const terkait = readRows_('Pengajuan').filter(function (p) { return p.jadwalId === j.id; });
  need_(!terkait.some(function (p) { return p.status === 'Disetujui'; }),
        'Jadwal sudah memiliki pemagang yang disetujui. Gunakan "Tutup" agar riwayat tetap tersimpan.');
  terkait.sort(function (a, b) { return b._row - a._row; }).forEach(function (p) { deleteRow_('Pengajuan', p); });
  deleteRow_('Jadwal', j);
  return {};
}

function roleOfKey_(key) {
  const k = normKey_(key);
  if (/^ADMIN.+/.test(k)) return 'ADMIN';
  if (/^DOSEN.+/.test(k)) return 'DOSEN';
  return null;
}

// ============================================================================
// PENGAJUAN (pemagang memilih jadwal)
// ============================================================================

function aktif_(p) { return p.status === 'Diajukan' || p.status === 'Disetujui'; }

function ajukanJadwal_(req) {
  const me = requirePemagang_(req.nim);
  const catatan = cleanMultiline_(req.catatan);
  need_(catatan.length <= 300, 'Catatan maksimal 300 karakter.');

  const j = readRows_('Jadwal').filter(function (x) { return x.id === clean_(req.jadwalId); })[0];
  need_(j, 'Jadwal tidak ditemukan.');
  need_(j.dosenKey === me.dosenKey, 'Jadwal ini bukan dari dosen pembimbing Anda.');
  need_(j.status === 'Buka', 'Jadwal sudah ditutup.');
  need_(j.tanggal > todayStr_() || (j.tanggal === todayStr_() && toMin_(j.jamSelesai) > nowMin_()),
        'Jadwal sudah lewat.');

  const semua = readRows_('Pengajuan');
  const milikku = semua.filter(function (p) { return p.nim === me.nim; });
  const dobel = milikku.filter(function (p) { return p.jadwalId === j.id; });
  need_(!dobel.some(aktif_), 'Anda sudah mengajukan jadwal ini.');
  need_(!dobel.some(function (p) { return p.status === 'Ditolak'; }), 'Pengajuan Anda untuk jadwal ini sebelumnya ditolak dosen.');

  const terisi = semua.filter(function (p) { return p.jadwalId === j.id && aktif_(p); }).length;
  need_(terisi < Number(j.kuota), 'Kuota jadwal ini sudah penuh.');

  // tidak boleh dua jadwal aktif pada waktu yang sama
  const jadwalAll = readRows_('Jadwal');
  const s = toMin_(j.jamMulai), e = toMin_(j.jamSelesai);
  const tabrakan = milikku.filter(aktif_).map(function (p) {
    return jadwalAll.filter(function (x) { return x.id === p.jadwalId; })[0];
  }).filter(function (x) {
    return x && x.tanggal === j.tanggal && overlap_(s, e, toMin_(x.jamMulai), toMin_(x.jamSelesai));
  })[0];
  need_(!tabrakan, 'Waktu bentrok dengan jadwal Anda yang lain: "' + (tabrakan && tabrakan.judul) + '".');

  const id = genId_();
  appendRow_('Pengajuan', {
    id: id, jadwalId: j.id, nim: me.nim, nama: me.nama, catatan: catatan,
    status: 'Diajukan', timestamp: nowIso_(), keputusanAt: '',
  });
  return { id: id };
}

function batalkanPengajuan_(req) {
  const me = requirePemagang_(req.nim);
  const p = readRows_('Pengajuan').filter(function (x) { return x.id === clean_(req.id); })[0];
  need_(p && p.nim === me.nim, 'Pengajuan tidak ditemukan.');
  need_(aktif_(p), 'Pengajuan ini tidak dapat dibatalkan.');
  const j = readRows_('Jadwal').filter(function (x) { return x.id === p.jadwalId; })[0];
  need_(!j || j.tanggal >= todayStr_(), 'Jadwal sudah lewat, tidak dapat dibatalkan.');
  updateRow_('Pengajuan', p, { status: 'Dibatalkan', keputusanAt: nowIso_() });
  return {};
}

function putusPengajuan_(req) {
  const key = requireRole_(req.key, 'DOSEN');
  const status = clean_(req.status);
  need_(status === 'Disetujui' || status === 'Ditolak', 'Keputusan tidak valid.');
  const p = readRows_('Pengajuan').filter(function (x) { return x.id === clean_(req.id); })[0];
  need_(p, 'Pengajuan tidak ditemukan.');
  const j = readRows_('Jadwal').filter(function (x) { return x.id === p.jadwalId; })[0];
  need_(j && j.dosenKey === key, 'Pengajuan ini bukan untuk jadwal Anda.');
  need_(p.status === 'Diajukan', 'Pengajuan sudah diputuskan atau dibatalkan.');
  if (status === 'Disetujui') {
    const disetujui = readRows_('Pengajuan').filter(function (x) { return x.jadwalId === j.id && x.status === 'Disetujui'; }).length;
    need_(disetujui < Number(j.kuota), 'Kuota jadwal sudah terpenuhi oleh pemagang yang disetujui.');
  }
  updateRow_('Pengajuan', p, { status: status, keputusanAt: nowIso_() });
  return {};
}

// ============================================================================
// LAPORAN (pemagang) + VALIDASI (dosen)
// ============================================================================

function kirimLaporan_(req) {
  const me = requirePemagang_(req.nim);
  const tanggal = clean_(req.tanggal);
  const jamMulai = clean_(req.jamMulai);
  const jamSelesai = clean_(req.jamSelesai);
  const kegiatan = cleanMultiline_(req.kegiatan);
  const kendala = cleanMultiline_(req.kendala);
  const jadwalId = clean_(req.jadwalId);
  const fotos = Array.isArray(req.fotos) ? req.fotos : [];

  need_(isDate_(tanggal), 'Tanggal tidak valid.');
  need_(tanggal <= todayStr_(), 'Tanggal laporan tidak boleh di masa depan.');
  need_(dayDiff_(todayStr_(), tanggal) <= MAX_HARI_LAMPAU, 'Tanggal laporan terlalu lama (maks ' + MAX_HARI_LAMPAU + ' hari ke belakang).');
  need_(isTime_(jamMulai) && isTime_(jamSelesai), 'Format jam tidak valid (HH:MM).');
  const s = toMin_(jamMulai), e = toMin_(jamSelesai);
  need_(e > s, 'Jam selesai harus setelah jam mulai.');
  need_(e - s >= 30, 'Durasi magang minimal 30 menit.');
  need_(e - s <= 720, 'Durasi magang maksimal 12 jam per laporan.');
  need_(tanggal !== todayStr_() || e <= nowMin_() + 5, 'Jam selesai belum terjadi. Isi laporan setelah kegiatan selesai.');
  need_(kegiatan.length >= 20 && kegiatan.length <= 2000, 'Deskripsi kegiatan 20–2000 karakter.');
  need_(kendala.length <= 500, 'Kendala maksimal 500 karakter.');
  need_(fotos.length >= 1, 'Unggah minimal 1 foto bukti kehadiran di ruang magang.');
  need_(fotos.length <= MAX_FOTO, 'Maksimal ' + MAX_FOTO + ' foto.');

  if (jadwalId) {
    const disetujui = readRows_('Pengajuan').some(function (p) {
      return p.nim === me.nim && p.jadwalId === jadwalId && p.status === 'Disetujui';
    });
    need_(disetujui, 'Jadwal terkait belum disetujui dosen atau bukan milik Anda.');
  }

  // tidak boleh tumpang tindih dengan laporan Anda yang lain (selain yang ditolak)
  const tabrakan = readRows_('Laporan').filter(function (l) {
    return l.nim === me.nim && l.tanggal === tanggal && l.status !== 'Ditolak' &&
           overlap_(s, e, toMin_(l.jamMulai), toMin_(l.jamSelesai));
  })[0];
  need_(!tabrakan, 'Waktu tumpang tindih dengan laporan Anda yang lain (' +
        (tabrakan && tabrakan.jamMulai) + '–' + (tabrakan && tabrakan.jamSelesai) + ').');

  // validasi semua foto SEBELUM menyimpan apa pun
  const blobs = fotos.map(function (f, i) {
    const mime = String(f.mime || '').toLowerCase();
    need_(FOTO_MIME.indexOf(mime) >= 0, 'Foto ke-' + (i + 1) + ' harus JPG/PNG/WEBP.');
    need_(typeof f.data === 'string' && f.data.length > 100, 'Foto ke-' + (i + 1) + ' kosong.');
    let bytes;
    try { bytes = Utilities.base64Decode(f.data); } catch (err) { throw new Error('Foto ke-' + (i + 1) + ' rusak.'); }
    need_(bytes.length <= MAX_FOTO_BYTES, 'Foto ke-' + (i + 1) + ' terlalu besar (maks 3 MB).');
    const ext = mime === 'image/png' ? 'png' : (mime === 'image/webp' ? 'webp' : 'jpg');
    return Utilities.newBlob(bytes, mime, me.nim + '_' + tanggal + '_' + (i + 1) + '_' + genId_().substring(0, 5) + '.' + ext);
  });

  const folder = getFolder_();
  const ids = [];
  try {
    blobs.forEach(function (b) {
      const file = folder.createFile(b);
      try { file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW); } catch (err) { /* kebijakan domain */ }
      ids.push(file.getId());
    });
  } catch (err) {
    ids.forEach(trashFile_);
    throw new Error('Gagal menyimpan foto ke Google Drive: ' + err.message);
  }

  const id = genId_();
  appendRow_('Laporan', {
    id: id, nim: me.nim, nama: me.nama, jadwalId: jadwalId, tanggal: tanggal,
    jamMulai: jamMulai, jamSelesai: jamSelesai, durasiJam: Math.round(((e - s) / 60) * 100) / 100,
    ruang: RUANG_DEFAULT, kegiatan: kegiatan, kendala: kendala, fotoIds: ids.join(','),
    status: 'Menunggu', catatanDosen: '', validatorKey: '', timestamp: nowIso_(), validatedAt: '',
  });
  return { id: id };
}

function hapusLaporan_(req) {
  const me = requirePemagang_(req.nim);
  const l = readRows_('Laporan').filter(function (x) { return x.id === clean_(req.id); })[0];
  need_(l && l.nim === me.nim, 'Laporan tidak ditemukan.');
  need_(l.status !== 'Tervalidasi', 'Laporan yang sudah tervalidasi tidak dapat dihapus.');
  String(l.fotoIds || '').split(',').filter(String).forEach(trashFile_);
  deleteRow_('Laporan', l);
  return {};
}

function validasiLaporan_(req) {
  const key = requireRole_(req.key, 'DOSEN');
  const status = clean_(req.status);
  const catatan = cleanMultiline_(req.catatan);
  need_(status === 'Tervalidasi' || status === 'Ditolak', 'Keputusan tidak valid.');
  need_(catatan.length <= 500, 'Catatan maksimal 500 karakter.');
  if (status === 'Ditolak') need_(catatan.length >= 5, 'Isi alasan penolakan (min. 5 karakter) agar pemagang dapat memperbaiki.');

  const l = readRows_('Laporan').filter(function (x) { return x.id === clean_(req.id); })[0];
  need_(l, 'Laporan tidak ditemukan.');
  const p = readRows_('Pemagang').filter(function (x) { return x.nim === l.nim; })[0];
  need_(p && p.dosenKey === key, 'Laporan ini bukan dari pemagang bimbingan Anda.');

  updateRow_('Laporan', l, { status: status, catatanDosen: catatan, validatorKey: key, validatedAt: nowIso_() });
  return {};
}

// ============================================================================
// GOOGLE DRIVE
// ============================================================================

function getFolder_() {
  const props = PropertiesService.getScriptProperties();
  const saved = props.getProperty('FOLDER_ID');
  if (saved) { try { return DriveApp.getFolderById(saved); } catch (err) { /* dibuat ulang */ } }
  const it = DriveApp.getFoldersByName(FOLDER_NAME);
  const folder = it.hasNext() ? it.next() : DriveApp.createFolder(FOLDER_NAME);
  props.setProperty('FOLDER_ID', folder.getId());
  return folder;
}

function trashFile_(id) {
  try { DriveApp.getFileById(id).setTrashed(true); } catch (err) { /* sudah tidak ada */ }
}
