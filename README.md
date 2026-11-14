# 🎓 Portal Magang — UNAS PASIM (v2.0)

> **Sistem Informasi Pemagangan berbasis Web — PHP, Vanilla JavaScript Modular, Tailwind CSS, Google Sheets API & Google Drive**

![Status](https://img.shields.io/badge/Status-v2.0_Active-00ff88?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)
![JavaScript](https://img.shields.io/badge/JavaScript-Modular_ES2020+-F7DF1E?style=flat-square&logo=javascript)
![Database](https://img.shields.io/badge/Database-Google_Sheets-34A853?style=flat-square&logo=googlesheets)
![Storage](https://img.shields.io/badge/Storage-Google_Drive-4285F4?style=flat-square&logo=googledrive)
![Hosting](https://img.shields.io/badge/Hosting-vcreed.my.id-00f3ff?style=flat-square)

---

## 🌐 Demo & Akses

| Environment | URL |
|-------------|-----|
| **Production** | [https://vcreed.my.id/universitas/portal-magang/](https://vcreed.my.id/universitas/portal-magang/) |
| **Lokal (XAMPP)** | [http://localhost/portal-magang/](http://localhost/portal-magang/) |
| **Repository** | [https://github.com/Zenwhite96/portal-magang](https://github.com/Zenwhite96/portal-magang) |

---

## 🔑 Sistem Key Akses & Validasi

Aplikasi menggunakan autentikasi berbasis role (case-insensitive) dengan perlindungan validasi data:

| Role | Format Key Akses | Mekanisme Login |
|------|-----------------|-----------------|
| 🎓 **Dosen Pembimbing** | `DOSEN001`, `DOSEN002`, dst | Langsung memasukkan Key Akses |
| 🎒 **Mahasiswa Pemagang** | `MHS001`, `MHS12345`, dst | Key Akses + Pilih Nama + Input **NIM Valid** |
| 🔐 **Administrator** | `ADMIN999`, `ADMIN001`, dst | Langsung memasukkan Key Akses |

> 💡 **Mode Demo Otomatis:** Apabila backend Google Apps Script belum dikonfigurasi atau sedang offline/mengalami gangguan jaringan, portal otomatis beralih ke **Mode Demo** (in-memory) sehingga seluruh alur antarmuka tetap dapat diuji tanpa hambatan.

---

## ✨ Fitur Utama

### 🎓 Dashboard Dosen Pembimbing
- **Manajemen Pemagang:** Daftarkan mahasiswa bimbingan (Nama, NIM, Program Studi).
- **Manajemen Jadwal:** Buat jadwal penugasan magang lengkap dengan rentang jam (`jamMulai` - `jamSelesai`), kuota peserta, dan deteksi bentrok ruangan.
- **Persetujuan Pengajuan:** Verifikasi dan putuskan pendaftaran jadwal mahasiswa (Setujui / Tolak).
- **Validasi Laporan:** Tinjau laporan harian pemagang lengkap dengan foto bukti dari Google Drive, beri catatan revisi, dan ubah status (*Disetujui* / *Ditolak*).

### 🎒 Dashboard Mahasiswa Pemagang
- **Ringkasan Aktivitas:** Indikator total jam magang yang telah disetujui terhadap target jam (default: 120 jam).
- **Pilih Jadwal Tersedia:** Lihat daftar jadwal magang yang dibuka dosen dan ajukan keikutsertaan.
- **Jadwal Saya:** Pantau status persetujuan jadwal yang diajukan.
- **Isi Laporan Harian:** Input laporan kegiatan per jam, kendala yang dihadapi, serta lampiran hingga 3 foto bukti. Foto dikompres otomatis di sisi browser sebelum diunggah ke Google Drive.
- **Riwayat Laporan:** Pantau riwayat status validasi laporan harian secara real-time.

### 🔐 Dashboard Administrator
- **Statistik & Rekap:** Ringkasan global pemagang aktif, total jadwal, jam kerja magang, dan sebaran status laporan.
- **Ekspor CSV:** Unduh rekapitulasi data laporan untuk kebutuhan arsip dan pelaporan akademik.
- **Monitoring Data:** Pantau seluruh data jadwal magang dan daftar pemagang terdaftar.
- **Diagnostik Sistem:** Informasi konfigurasi endpoint backend, runtime, dan status koneksi.

---

## 🏗️ Arsitektur & Struktur Folder

Aplikasi dirancang modular dengan pemisahan berkas antarmuka, logika bisnis, dan API backend:

```
portal-magang/
├── index.php                  ← Shell utama aplikasi & cache-busting loader
├── assets/
│   ├── css/
│   │   └── app.css            ← Desain UI modern responsif (Tema UNAS PASIM)
│   └── js/
│       ├── config.js          ← Konfigurasi endpoint GAS, ruang, & target jam
│       ├── api.js             ← HTTP client Fetch API & simulator Mode Demo
│       ├── ui.js              ← Utilitas antarmuka, dialog, toast, kompresi gambar
│       ├── core.js            ← State management & router navigasi
│       ├── auth.js            ← Logika autentikasi & validasi NIM
│       ├── views-dosen.js     ← Tampilan antarmuka Dashboard Dosen
│       ├── views-mhs.js       ← Tampilan antarmuka Dashboard Mahasiswa
│       ├── views-admin.js     ← Tampilan antarmuka Dashboard Admin
│       └── main.js            ← Entry point DOM & listener global
├── backend/
│   ├── google-apps-script.js  ← Backend Google Apps Script v2.0 (REST API)
│   └── appsscript.json        ← Manifest konfigurasi zona waktu & OAuth scopes
└── README.md                  ← Dokumentasi proyek
```

---

## 📊 Skema Database (Google Sheets v2.0)

Database menggunakan 4 sheet tab di dalam satu Google Spreadsheet:

| Sheet | Kolom Header | Fungsi |
|-------|--------------|--------|
| **`Pemagang`** | `id`, `nim`, `nama`, `prodi`, `dosenKey`, `timestamp` | Master data mahasiswa pemagang terdaftar |
| **`Jadwal`** | `id`, `dosenKey`, `judul`, `tanggal`, `jamMulai`, `jamSelesai`, `ruang`, `tugas`, `kuota`, `status`, `timestamp` | Master data jadwal dan penugasan |
| **`Pengajuan`** | `id`, `jadwalId`, `nim`, `nama`, `catatan`, `status`, `timestamp`, `keputusanAt` | Pendaftaran jadwal oleh mahasiswa |
| **`Laporan`** | `id`, `nim`, `nama`, `jadwalId`, `tanggal`, `jamMulai`, `jamSelesai`, `durasiJam`, `ruang`, `kegiatan`, `kendala`, `fotoIds`, `status`, `catatanDosen`, `validatorKey`, `timestamp`, `validatedAt` | Log kegiatan harian dan foto bukti Drive |

---

## 📡 API Endpoints (Google Apps Script v2.0)

Semua endpoint dilayani oleh Google Apps Script Web App (`doGet` & `doPost`):

### 📥 GET Endpoints
| Action | Parameter Query | Deskripsi |
|--------|----------------|-----------|
| `ping` | — | Tes ketersediaan dan latensi backend |
| `pemagangPublic` | — | Mengambil daftar nama pemagang untuk dropdown login |
| `bundle` | `role`, `key`, `nim` | Mengambil seluruh data terkait sesuai hak akses pengguna |

### 📤 POST Endpoints (JSON Body)
| Action | Field Penting | Deskripsi |
|--------|--------------|-----------|
| `loginPemagang` | `nim`, `nama` | Validasi kredensial login pemagang |
| `tambahPemagang` | `dosenKey`, `items: [{nim, nama, prodi}]` | Mendaftarkan pemagang baru |
| `hapusPemagang` | `id`, `dosenKey` | Menghapus data pemagang |
| `buatJadwal` | `dosenKey`, `judul`, `tanggal`, `jamMulai`, `jamSelesai`, `kuota`, `tugas` | Membuat jadwal magang baru |
| `ubahStatusJadwal` | `id`, `dosenKey`, `status` | Mengubah status jadwal (*Buka* / *Tutup*) |
| `hapusJadwal` | `id`, `dosenKey` / `adminKey` | Menghapus jadwal |
| `ajukanJadwal` | `jadwalId`, `nim`, `nama`, `catatan` | Mahasiswa mendaftar ke jadwal |
| `batalkanPengajuan`| `id`, `nim` | Membatalkan pengajuan jadwal |
| `putusPengajuan` | `id`, `dosenKey`, `status` | Dosen menyetujui/menolak pengajuan |
| `kirimLaporan` | `nim`, `nama`, `tanggal`, `jamMulai`, `jamSelesai`, `kegiatan`, `foto` | Mengirim laporan kegiatan harian & upload foto |
| `hapusLaporan` | `id`, `nim` / `dosenKey` | Menghapus laporan |
| `validasiLaporan` | `id`, `dosenKey`, `status`, `catatanDosen` | Validasi laporan oleh dosen |

---

## ⚙️ Petunjuk Konfigurasi

### 1. Hubungkan Frontend ke Backend
Buka berkas [assets/js/config.js](file:///c:/MAGANG/portal-magang/assets/js/config.js) dan isi `SCRIPT_URL` dengan URL Deployment Google Apps Script:
```javascript
const CONFIG = Object.freeze({
    SCRIPT_URL: 'https://script.google.com/macros/s/YOUR_DEPLOYMENT_ID/exec',
    RUANG: 'Wakil Rektor 1',
    TARGET_JAM: 120,
    MAX_FOTO: 3,
    TIMEOUT_MS: 45000,
    PORTAL_URL: 'https://vcreed.my.id/universitas/',
});
```

### 2. Konfigurasi Google Apps Script
1. Buka [Google Apps Script](https://script.google.com/) dan buat project baru.
2. Salin seluruh isi berkas [backend/google-apps-script.js](file:///c:/MAGANG/portal-magang/backend/google-apps-script.js) ke editor GAS.
3. Masukkan Spreadsheet ID pada variabel `SPREADSHEET_ID`:
   ```javascript
   const SPREADSHEET_ID = 'YOUR_SPREADSHEET_ID_ONLY';
   ```
4. Jalankan fungsi `setup` sekali melalui toolbar Apps Script untuk membuat 4 sheet tab dan folder Drive penyimpan foto secara otomatis.
5. Lakukan **Deploy > Deployment Baru** (Pilih tipe *Aplikasi Web*, Akses: *Siapa saja*).

---

## 🚀 Cara Menjalankan di Lokal (XAMPP)

1. Pastikan folder repositori berada di komputer Anda:
   ```text
   C:\MAGANG\portal-magang\
   ```
2. Sinkronkan seluruh berkas ke direktori `htdocs` XAMPP menggunakan PowerShell:
   ```powershell
   Copy-Item -Path "C:\MAGANG\portal-magang\*" -Destination "C:\XAMPPP\htdocs\portal-magang\" -Recurse -Force
   ```
3. Buka **XAMPP Control Panel** dan aktifkan modul **Apache**.
4. Akses melalui browser:
   ```text
   http://localhost/portal-magang/
   ```

---

## 🔄 Prosedur Sinkronisasi & Deploy

### Sinkronisasi ke XAMPP Lokal
Setiap kali melakukan perubahan kode di `C:\MAGANG\portal-magang\`, jalankan perintah berikut di terminal:
```powershell
Copy-Item -Path "C:\MAGANG\portal-magang\*" -Destination "C:\XAMPPP\htdocs\portal-magang\" -Recurse -Force
```

### Deploy ke Server Production
Unggah file hasil perubahan (`index.php`, folder `assets/`, dan folder `backend/`) ke server web cPanel/hosting pada folder target `portal-magang/`.

---

## 👤 Pengembang

**Abdul Azis Al Rasyid Sinaga**  
- 🏫 Universitas Nasional Pasim (UNAS PASIM)  
- 🐙 GitHub: [@Zenwhite96](https://github.com/Zenwhite96)  
- 📧 Email: abdulazis050407@gmail.com  

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE) — bebas digunakan untuk keperluan akademik dan pembelajaran.
