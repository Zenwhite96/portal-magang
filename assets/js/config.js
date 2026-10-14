/* ==========================================================================
   KONFIGURASI APLIKASI
   ========================================================================== */
const CONFIG = Object.freeze({

    /* >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
       MASUKKAN URL GOOGLE APPS SCRIPT ANDA DI SINI
       (URL "Aplikasi Web" hasil Deploy, berakhiran /exec)
       <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< */
    SCRIPT_URL: 'https://script.google.com/macros/s/AKfycbz5MK2jbf-SkPuO0sXYYGgCmCIvVdZpMGjCecmC46TnkIM8cEyr5Ha2i1v6v40ngE-UkQ/exec',

    RUANG: 'Wakil Rektor 1',      // ruang magang tunggal (harus sama dengan backend)
    TARGET_JAM: 120,              // target jam magang per pemagang (untuk progress bar)
    MAX_FOTO: 3,                  // maks foto bukti per laporan
    TIMEOUT_MS: 45000,            // batas waktu menunggu Google Apps Script
    PORTAL_URL: 'https://vcreed.my.id/universitas/',
});
