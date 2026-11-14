# 🎓 Portal Magang — UNAS PASIM (v2.5)

> **Sistem Informasi Pemagangan berbasis Web — PHP 8.x, MySQL / SQLite PDO, Vanilla JavaScript Modular, Tailwind CSS, & Google Apps Script Support**

![Status](https://img.shields.io/badge/Status-v2.5_Active-00ff88?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.x_PDO-777BB4?style=flat-square&logo=php)
![Database](https://img.shields.io/badge/Database-MySQL_%2F_SQLite-4479A1?style=flat-square&logo=mysql)
![JavaScript](https://img.shields.io/badge/JavaScript-Modular_ES2020+-F7DF1E?style=flat-square&logo=javascript)
![Storage](https://img.shields.io/badge/Storage-Local_Uploads_%2F_Drive-4285F4?style=flat-square)
![Hosting](https://img.shields.io/badge/Hosting-vcreed.my.id-00f3ff?style=flat-square)

---

## 🌐 Demo & Akses

| Environment | URL |
|-------------|-----|
| **Production** | [https://vcreed.my.id/universitas/portal-magang/](https://vcreed.my.id/universitas/portal-magang/) |
| **Lokal (XAMPP)** | [http://localhost/portal-magang/](http://localhost/portal-magang/) |
| **Repository** | [https://github.com/Zenwhite96/portal-magang](https://github.com/Zenwhite96/portal-magang) |

---

## 🚀 Fitur Baru di Versi 2.5

1. ⚡ **Backend PHP Asli (REST API) & PDO Database**:
   - Mendukung **MySQL / MariaDB** (bawaan XAMPP) dan **SQLite** (auto-fallback cerdas tanpa setup tambahan).
   - Seluruh operasi database menggunakan transaksi aman, sanitasi masukan, dan prepared statements (anti-SQL Injection).
2. 📸 **Penyimpanan Foto Bukti Cepat & Aman**:
   - Foto bukti dikompres otomatis di sisi klien (Canvas) lalu disimpan langsung ke direktori `uploads/` server.
   - Dilindungi dengan `.htaccess` dan `index.html` untuk memblokir eksekusi skrip berbahaya.
3. 🖨️ **Fitur Cetak Lembar Presensi & Rekapitulasi**:
   - Pemagang dapat langsung mencetak lembar kegiatan resmi ber-KOP Universitas Nasional Pasim dengan tanda tangan mahasiswa dan dosen pembimbing untuk keperluan pelaporan akademik.
4. 🔄 **Dual Backend Support**:
   - Backend PHP lokal di `api/index.php` (default, cepat, mandiri).
   - Mendukung opsi Google Apps Script + Google Sheets jika ingin diintegrasikan dengan spreadsheet Google Drive.
5. 🛡️ **Mode Demo Fallback**:
   - Jika server database belum menyala sama sekali, portal secara otomatis masuk ke Mode Demo di memori.

---

## 🔑 Sistem Key Akses & Validasi

Aplikasi menggunakan autentikasi berbasis role (case-insensitive) dengan perlindungan validasi data:

| Role | Format Key Akses | Mekanisme Login |
|------|-----------------|-----------------|
| 🎓 **Dosen Pembimbing** | `DOSEN001`, `DOSEN002`, dst | Langsung memasukkan Key Akses |
| 🎒 **Mahasiswa Pemagang** | `MHS001`, `MHS12345`, dst | Key Akses + Pilih Nama + Input **NIM Valid** |
| 🔐 **Administrator** | `ADMIN999`, `ADMIN001`, dst | Langsung memasukkan Key Akses |

---

## ✨ Fitur Antarmuka Berdasarkan Peran

### 🎓 Dashboard Dosen Pembimbing
- **Manajemen Pemagang:** Daftarkan mahasiswa bimbingan (Nama, NIM, Program Studi).
- **Manajemen Jadwal:** Buat jadwal penugasan magang lengkap dengan rentang jam (`jamMulai` - `jamSelesai`), kuota peserta, dan deteksi bentrok ruangan otomatis.
- **Persetujuan Pengajuan:** Verifikasi dan putuskan pendaftaran jadwal mahasiswa (Setujui / Tolak).
- **Validasi Laporan:** Tinjau laporan harian pemagang lengkap dengan foto bukti kehadiran, beri catatan revisi, dan ubah status (*Disetujui* / *Ditolak*).

### 🎒 Dashboard Mahasiswa Pemagang
- **Ringkasan Aktivitas:** Indikator total jam magang yang telah disetujui terhadap target jam (default: 120 jam).
- **Pilih Jadwal Tersedia:** Lihat daftar jadwal magang yang dibuka dosen dan ajukan keikutsertaan.
- **Jadwal Saya:** Pantau status persetujuan jadwal yang diajukan.
- **Isi Laporan Harian:** Input laporan kegiatan per jam, kendala yang dihadapi, serta lampiran hingga 3 foto bukti. Foto dikompres otomatis di sisi browser.
- **Riwayat Laporan & Cetak Lembar Presensi:** Cetak rekapitulasi presensi resmi (PDF/Print) lengkap dengan KOP UNAS PASIM.

### 🔐 Dashboard Administrator
- **Statistik & Rekap:** Ringkasan global pemagang aktif, total jadwal, jam kerja magang, dan sebaran status laporan.
- **Ekspor CSV:** Unduh rekapitulasi data laporan untuk kebutuhan arsip dan pelaporan akademik.
- **Monitoring Data:** Pantau seluruh data jadwal magang dan daftar pemagang terdaftar.
- **Diagnostik Sistem:** Informasi konfigurasi endpoint backend, runtime engine, driver database aktif (MySQL/SQLite), dan latensi ping server.

---

## 🏗️ Struktur Folder Proyek

```
portal-magang/
├── index.php                  ← Shell utama aplikasi & cache-busting loader
├── api/                       ← Backend REST API (PHP + PDO)
│   ├── config.php             ← Konfigurasi koneksi MySQL & SQLite
│   ├── db.php                 ← Database handler, auto-migration & auto-seeding
│   └── index.php              ← REST API controller utama
├── assets/
│   ├── css/
│   │   └── app.css            ← Desain UI modern responsif (Tema UNAS PASIM)
│   └── js/
│       ├── config.js          ← Konfigurasi endpoint API ('api/index.php'), ruang, target jam
│       ├── api.js             ← HTTP client Fetch API & simulator Mode Demo
│       ├── ui.js              ← Utilitas antarmuka, dialog, toast, kompresi gambar
│       ├── core.js            ← State management & router navigasi
│       ├── auth.js            ← Logika autentikasi & validasi NIM
│       ├── views-dosen.js     ← Tampilan antarmuka Dashboard Dosen
│       ├── views-mhs.js       ← Tampilan antarmuka Dashboard Mahasiswa
│       ├── views-admin.js     ← Tampilan antarmuka Dashboard Admin
│       └── main.js            ← Entry point DOM & listener global
├── backend/                   ← Opsi Backend Google Apps Script (opsional)
│   ├── google-apps-script.js  ← Kode GAS v2.0
│   └── appsscript.json        ← Manifest Apps Script
├── uploads/                   ← Direktori penyimpanan foto bukti magang
│   ├── .htaccess              ← Pengamanan direktori uploads
│   └── index.html             ← Pencegahan directory listing
├── data/                      ← Lokasi file SQLite (jika MySQL nonaktif)
└── README.md                  ← Dokumentasi proyek
```

---

## 📊 Skema Database (MySQL & SQLite)

Tabel dibuat otomatis secara instan saat API pertama kali diakses:

| Tabel | Kolom | Fungsi |
|-------|-------|--------|
| **`pemagang`** | `id`, `nim`, `nama`, `prodi`, `dosen_key`, `created_at` | Data master mahasiswa pemagang |
| **`jadwal`** | `id`, `dosen_key`, `judul`, `tanggal`, `jam_mulai`, `jam_selesai`, `ruang`, `tugas`, `kuota`, `status`, `created_at` | Data master jadwal magang |
| **`pengajuan`** | `id`, `jadwal_id`, `nim`, `nama`, `catatan`, `status`, `created_at`, `keputusan_at` | Pendaftaran jadwal oleh pemagang |
| **`laporan`** | `id`, `nim`, `nama`, `jadwal_id`, `tanggal`, `jam_mulai`, `jam_selesai`, `durasi_jam`, `ruang`, `kegiatan`, `kendala`, `foto_ids`, `status`, `catatan_dosen`, `validator_key`, `created_at`, `validated_at` | Log kegiatan harian dan foto bukti |

---

## ⚙️ Petunjuk Konfigurasi Database

Buka berkas [api/config.php](file:///c:/MAGANG/portal-magang/api/config.php):
```php
return [
    // Driver: 'mysql' (default XAMPP) atau 'sqlite'
    'db_driver' => 'mysql',

    'mysql' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'portal_magang',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
    // ...
];
```
> 💡 **Fitur Fallback:** Jika Anda belum menyalakan MySQL atau belum membuat database di phpMyAdmin, backend akan **secara otomatis beralih ke SQLite** di `data/portal_magang.sqlite`. Database akan dibuat dan diisi data awal secara otomatis tanpa memerlukan konfigurasi manual!

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
3. Buka **XAMPP Control Panel** dan klik **Start** pada modul **Apache** (dan **MySQL** jika ingin menggunakan MySQL).
4. Akses melalui browser:
   ```text
   http://localhost/portal-magang/
   ```

---

## 👤 Pengembang

**Abdul Azis Al Rasyid Sinaga**  
- 🏫 Universitas Nasional Pasim (UNAS PASIM)  
- 🐙 GitHub: [@Zenwhite96](https://github.com/Zenwhite96)  
- 📧 Email: abdulazis050407@gmail.com  

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE) — bebas digunakan untuk keperluan akademik dan pembelajaran.
