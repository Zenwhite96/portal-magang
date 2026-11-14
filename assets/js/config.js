/* ==========================================================================
   KONFIGURASI APLIKASI
   ========================================================================== */
const CONFIG = Object.freeze({

    /* URL Endpoint API:
       Secara bawaan menggunakan backend PHP lokal ('api/index.php').
       Jika menggunakan Google Apps Script, ganti dengan URL deploy GAS Anda. */
    SCRIPT_URL: 'api/index.php',

    RUANG: 'Wakil Rektor 1',      // ruang magang tunggal (harus sama dengan backend)
    TARGET_JAM: 120,              // target jam magang per pemagang (untuk progress bar)
    MAX_FOTO: 3,                  // maks foto bukti per laporan
    TIMEOUT_MS: 30000,            // batas waktu menunggu server API
    PORTAL_URL: 'https://vcreed.my.id/universitas/',
});
