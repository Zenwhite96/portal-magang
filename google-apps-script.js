/**
 * ================================================================
 *  PORTAL MAGANG — Google Apps Script Backend
 *  ================================================================
 *  Cara Deploy:
 *  1. Buka https://script.google.com → Buat Project Baru
 *  2. Paste seluruh kode ini
 *  3. Edit SPREADSHEET_ID di bawah
 *  4. Extensions → Apps Script → Deploy → New Deployment
 *     - Type: Web App
 *     - Execute as: Me
 *     - Who has access: Anyone
 *  5. Salin URL deployment → paste ke index.php (const scriptURL)
 *  ================================================================
 */

// ================================================================
// ⚙️ KONFIGURASI — GANTI DENGAN ID SPREADSHEET ANDA
// Cara dapat ID: buka Google Sheets → salin dari URL:
// https://docs.google.com/spreadsheets/d/[SPREADSHEET_ID]/edit
// ================================================================
const SPREADSHEET_ID = 'GANTI_DENGAN_SPREADSHEET_ID_ANDA';

// Sheet Names (buat sheet ini di Google Sheets Anda)
const SHEET_JADWAL  = 'Jadwal';
const SHEET_LAPORAN = 'Laporan';
const SHEET_PILIHAN = 'PilihanJadwal';

// ================================================================
// HEADERS Google Sheets (baris pertama setiap sheet)
// ================================================================
const HEADER_JADWAL  = ['id','dosenKey','judul','tanggal','selesai','ruang','tugas','kuota','timestamp'];
const HEADER_LAPORAN = ['id','mahasiswaKey','ruangMagang','tanggal','detailTugas','durasi','kendala','status','dosenKey','timestamp'];
const HEADER_PILIHAN = ['id','mahasiswaKey','jadwalId','catatan','timestamp'];

// ================================================================
// ENTRY POINT — HTTP GET
// ================================================================
function doGet(e) {
  const action = e.parameter.action || '';
  let result;

  try {
    switch (action) {
      case 'getJadwal':
        result = getJadwal(); break;
      case 'getLaporan':
        result = getLaporan(e.parameter.dosenKey); break;
      case 'getAllLaporan':
        result = getAllLaporan(); break;
      case 'getPilihan':
        result = getPilihan(e.parameter.mahasiswaKey); break;
      default:
        result = { status: 'error', message: 'Action tidak dikenali: ' + action };
    }
  } catch (err) {
    result = { status: 'error', message: err.message };
  }

  return ContentService
    .createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}

// ================================================================
// ENTRY POINT — HTTP POST
// ================================================================
function doPost(e) {
  let payload;
  try {
    payload = JSON.parse(e.postData.contents);
  } catch (err) {
    return jsonResponse({ status: 'error', message: 'Payload JSON tidak valid' });
  }

  const action = payload.action || '';
  let result;

  try {
    switch (action) {
      case 'buatJadwal':
        result = buatJadwal(payload); break;
      case 'kirimLaporan':
        result = kirimLaporan(payload); break;
      case 'validasiLaporan':
        result = validasiLaporan(payload); break;
      case 'pilihJadwal':
        result = pilihJadwal(payload); break;
      case 'hapusJadwal':
        result = hapusJadwal(payload); break;
      default:
        result = { status: 'error', message: 'Action POST tidak dikenali: ' + action };
    }
  } catch (err) {
    result = { status: 'error', message: err.message };
  }

  return jsonResponse(result);
}

function jsonResponse(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}

// ================================================================
// UTILITY — Generate ID unik
// ================================================================
function genId() {
  return Date.now().toString(36) + Math.random().toString(36).substr(2, 5);
}

// ================================================================
// UTILITY — Pastikan sheet + header ada
// ================================================================
function getOrCreateSheet(name, headers) {
  const ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  let sheet = ss.getSheetByName(name);
  if (!sheet) {
    sheet = ss.insertSheet(name);
    sheet.appendRow(headers);
    sheet.getRange(1, 1, 1, headers.length)
      .setBackground('#1e40af')
      .setFontColor('#ffffff')
      .setFontWeight('bold');
  }
  return sheet;
}

// ================================================================
// UTILITY — Baca semua data dari sheet (return array of objects)
// ================================================================
function readSheet(sheetName, headers) {
  const sheet = getOrCreateSheet(sheetName, headers);
  const data  = sheet.getDataRange().getValues();
  if (data.length <= 1) return []; // hanya header

  const keys = data[0];
  return data.slice(1).map(row => {
    const obj = {};
    keys.forEach((k, i) => obj[k] = row[i]);
    return obj;
  });
}

// ================================================================
// UTILITY — Append satu baris baru
// ================================================================
function appendRow(sheetName, headers, rowObj) {
  const sheet = getOrCreateSheet(sheetName, headers);
  const row   = headers.map(h => rowObj[h] || '');
  sheet.appendRow(row);
}

// ================================================================
// UTILITY — Update satu sel berdasarkan nilai kolom id
// ================================================================
function updateCell(sheetName, headers, id, colName, newValue) {
  const sheet = getOrCreateSheet(sheetName, headers);
  const data  = sheet.getDataRange().getValues();
  const idIdx = headers.indexOf('id');
  const colIdx= headers.indexOf(colName);

  for (let i = 1; i < data.length; i++) {
    if (data[i][idIdx] == id) {
      sheet.getRange(i + 1, colIdx + 1).setValue(newValue);
      return true;
    }
  }
  return false;
}

// ================================================================
// UTILITY — Hapus baris berdasarkan id
// ================================================================
function deleteRow(sheetName, headers, id) {
  const sheet = getOrCreateSheet(sheetName, headers);
  const data  = sheet.getDataRange().getValues();
  const idIdx = headers.indexOf('id');

  for (let i = 1; i < data.length; i++) {
    if (data[i][idIdx] == id) {
      sheet.deleteRow(i + 1);
      return true;
    }
  }
  return false;
}

// ================================================================
// CRUD — JADWAL
// ================================================================
function getJadwal() {
  const data = readSheet(SHEET_JADWAL, HEADER_JADWAL);
  return { status: 'ok', data };
}

function buatJadwal(p) {
  const row = {
    id:        genId(),
    dosenKey:  p.dosenKey  || '',
    judul:     p.judul     || '',
    tanggal:   p.tanggal   || '',
    selesai:   p.selesai   || '',
    ruang:     p.ruang     || '',
    tugas:     p.tugas     || '',
    kuota:     p.kuota     || '',
    timestamp: p.timestamp || new Date().toISOString(),
  };
  appendRow(SHEET_JADWAL, HEADER_JADWAL, row);
  return { status: 'ok', id: row.id };
}

function hapusJadwal(p) {
  const ok = deleteRow(SHEET_JADWAL, HEADER_JADWAL, p.id);
  return ok ? { status: 'ok' } : { status: 'error', message: 'Jadwal tidak ditemukan' };
}

// ================================================================
// CRUD — LAPORAN
// ================================================================
function kirimLaporan(p) {
  const row = {
    id:           genId(),
    mahasiswaKey: p.mahasiswaKey || '',
    ruangMagang:  p.ruangMagang  || '',
    tanggal:      p.tanggal      || '',
    detailTugas:  p.detailTugas  || '',
    durasi:       p.durasi       || '',
    kendala:      p.kendala      || '',
    status:       'Menunggu',
    dosenKey:     '',
    timestamp:    p.timestamp    || new Date().toISOString(),
  };
  appendRow(SHEET_LAPORAN, HEADER_LAPORAN, row);
  return { status: 'ok', id: row.id };
}

function getLaporan(dosenKey) {
  // Dosen melihat semua laporan (filter by dosen jika sudah divalidasi)
  const data = readSheet(SHEET_LAPORAN, HEADER_LAPORAN);
  return { status: 'ok', data };
}

function getAllLaporan() {
  const data = readSheet(SHEET_LAPORAN, HEADER_LAPORAN);
  return { status: 'ok', data };
}

function validasiLaporan(p) {
  const ok1 = updateCell(SHEET_LAPORAN, HEADER_LAPORAN, p.id, 'status',   p.status);
  const ok2 = updateCell(SHEET_LAPORAN, HEADER_LAPORAN, p.id, 'dosenKey', p.dosenKey || '');
  return ok1 ? { status: 'ok' } : { status: 'error', message: 'Laporan tidak ditemukan' };
}

// ================================================================
// CRUD — PILIHAN JADWAL
// ================================================================
function pilihJadwal(p) {
  // Cek apakah sudah pernah memilih jadwal ini
  const existing = readSheet(SHEET_PILIHAN, HEADER_PILIHAN);
  const sudahDaftar = existing.some(
    r => r.mahasiswaKey === p.mahasiswaKey && r.jadwalId === p.jadwalId
  );
  if (sudahDaftar) {
    return { status: 'error', message: 'Anda sudah mendaftar ke jadwal ini.' };
  }

  const row = {
    id:           genId(),
    mahasiswaKey: p.mahasiswaKey || '',
    jadwalId:     p.jadwalId     || '',
    catatan:      p.catatan      || '',
    timestamp:    p.timestamp    || new Date().toISOString(),
  };
  appendRow(SHEET_PILIHAN, HEADER_PILIHAN, row);
  return { status: 'ok', id: row.id };
}

function getPilihan(mahasiswaKey) {
  const data = readSheet(SHEET_PILIHAN, HEADER_PILIHAN)
    .filter(r => !mahasiswaKey || r.mahasiswaKey === mahasiswaKey);
  return { status: 'ok', data };
}
