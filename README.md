# 🎓 Portal Magang — Sistem Informasi Pemagangan

> **Aplikasi web pemagangan berbasis PHP/HTML/Vanilla JS dengan Google Sheets sebagai database.**

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss)
![Google Sheets](https://img.shields.io/badge/Google_Sheets-API-34A853?style=flat-square&logo=googlesheets)
![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)

---

## 📁 Struktur File

```
portal-magang/
├── index.php                  ← Aplikasi utama (semua dalam satu file)
├── google-apps-script.js      ← Kode backend Google Apps Script
├── README.md                  ← Dokumentasi ini
└── .gitignore
```

---

## 🚀 Panduan Setup Lengkap

### Langkah 1 — Siapkan Google Spreadsheet

1. Buka [Google Sheets](https://sheets.google.com) → Buat spreadsheet baru
2. Namai file: **"Portal Magang DB"**
3. Buat **3 sheet** dengan nama persis:
   - `Jadwal`
   - `Laporan`
   - `PilihanJadwal`
4. Salin **ID Spreadsheet** dari URL:
   ```
   https://docs.google.com/spreadsheets/d/[SPREADSHEET_ID_ANDA]/edit
   ```

---

### Langkah 2 — Deploy Google Apps Script

1. Buka [Google Apps Script](https://script.google.com/home) → **New Project**
2. Hapus kode default → Paste **seluruh isi** `google-apps-script.js`
3. Ganti `SPREADSHEET_ID`:
   ```javascript
   // Baris ~17 di google-apps-script.js
   const SPREADSHEET_ID = 'ISI_ID_SPREADSHEET_ANDA_DI_SINI';
   ```
4. Klik **Deploy** → **New Deployment**:
   | Setting | Nilai |
   |---------|-------|
   | Type | **Web App** |
   | Execute as | **Me** |
   | Who has access | **Anyone** |
5. Klik **Deploy** → **Authorize** → Salin URL deployment

---

### Langkah 3 — Konfigurasi `index.php`

Buka `index.php`, cari baris ~218 dan ganti URL:

```javascript
// ⚙️ GANTI BAGIAN INI:
const scriptURL = 'https://script.google.com/macros/s/GANTI_DENGAN_URL_DEPLOYMENT_ANDA/exec';
```

Menjadi:

```javascript
const scriptURL = 'https://script.google.com/macros/s/AKfycbxXXXXXXXXXXXXXXXX/exec';
//                                                   ^^^^ URL dari Step 2 ^^^^
```

---

### Langkah 4 — Upload ke Hosting

#### Opsi A: cPanel / Shared Hosting
```bash
# Upload ke subfolder
/public_html/portal-magang/index.php
```
Akses via: `https://domainanda.com/portal-magang/`

#### Opsi B: Localhost (XAMPP/Laragon)
```
C:/xampp/htdocs/portal-magang/index.php
```
Akses via: `http://localhost/portal-magang/`

---

## 🔑 Sistem Key Akses

| Awalan Key | Role | Contoh Key |
|-----------|------|------------|
| `DOSEN` | Dosen Pembimbing | `DOSEN001`, `DOSEN_BUDI` |
| `MHS` | Mahasiswa Pemagang | `MHS12345`, `MHS001` |
| `ADMIN` | Administrator | `ADMIN999`, `ADMIN001` |

> **Tips Keamanan:** Bagikan key secara langsung/tatap muka, jangan via chat publik.

---

## 📊 Fitur Per Role

### 🎓 Dashboard Dosen
- ✅ **Buat Jadwal** — Form lengkap membuat jadwal magang
- ✅ **Daftar Tugas** — Lihat semua laporan yang masuk dari pemagang
- ✅ **Validasi Laporan** — Approve/Tolak laporan pemagang

### 🎒 Dashboard Pemagang
- ✅ **Lihat Jadwal** — Melihat jadwal yang dibuat dosen (card view)
- ✅ **Pilih Waktu** — Mendaftarkan diri ke jadwal magang
- ✅ **Form Laporan** — Mengirimkan laporan harian magang

### 🔐 Dashboard Admin
- ✅ **Rekap Aktivitas** — Statistik + tabel semua laporan
- ✅ **Semua Jadwal** — Monitor + hapus jadwal
- ✅ **Manajemen Key** — Panduan format key

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Fungsi |
|-----------|--------|
| **PHP** | Server-side rendering minimal (tanggal, dst) |
| **HTML5** | Struktur halaman |
| **Vanilla JavaScript** | Logika frontend, fetch API |
| **Tailwind CSS 3 (CDN)** | Styling modern & responsif |
| **Google Sheets API** | Database via Google Apps Script |
| **Google Apps Script** | Backend REST API |

---

## 📡 API Endpoints (Google Apps Script)

### GET Endpoints
| Action | Deskripsi |
|--------|-----------|
| `?action=getJadwal` | Ambil semua jadwal |
| `?action=getLaporan&dosenKey=DOSEN001` | Laporan per dosen |
| `?action=getAllLaporan` | Semua laporan (admin) |

### POST Endpoints (JSON body)
| Action | Deskripsi |
|--------|-----------|
| `buatJadwal` | Buat jadwal baru |
| `kirimLaporan` | Kirim laporan pemagang |
| `validasiLaporan` | Update status laporan |
| `pilihJadwal` | Daftar ke jadwal |
| `hapusJadwal` | Hapus jadwal (admin) |

---

## 🔄 Update & Push GitHub

```bash
# Setiap ada perubahan:
git add .
git commit -m "feat: deskripsi perubahan"
git push origin main
```

---

## 🐛 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Data tidak muncul | Cek `scriptURL` di `index.php` sudah benar |
| Error "Access denied" | Pastikan GAS deploy dengan access "Anyone" |
| Data tidak tersimpan | Cek `SPREADSHEET_ID` di GAS sudah benar |
| CORS Error | Deploy ulang GAS dengan setting baru |

---

## 👤 Author

**Abdul Azis Al Rasyid Sinaga**  
📧 abdulazis050407@gmail.com  
📦 GitHub: [@abdulazis050407](https://github.com/abdulazis050407)

---

*© 2025 Portal Magang Universitas — Built with ❤️ by Senior Web Dev*
