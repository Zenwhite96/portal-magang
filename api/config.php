<?php
/**
 * PORTAL MAGANG — Konfigurasi Backend PHP & Database
 * -----------------------------------------------------------------------------
 * Mendukung MySQL (default XAMPP) dan otomatis fallback ke SQLite
 * jika MySQL belum aktif/belum dibuat databasenya.
 */

declare(strict_types=1);

// Cegah akses langsung jika diperlukan atau definisikan konstanta konfigurasi
return [
    // Driver database: 'mysql' atau 'sqlite'
    // Jika 'mysql' dipilih tapi koneksi gagal, sistem akan otomatis beralih ke SQLite
    'db_driver' => 'mysql',

    // Konfigurasi MySQL
    'mysql' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'portal_magang',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    // Konfigurasi SQLite (lokasi file database)
    'sqlite' => [
        'path' => __DIR__ . '/../data/portal_magang.sqlite',
    ],

    // Direktori penyimpanan bukti foto magang (relatif terhadap root portal)
    'upload_dir' => __DIR__ . '/../uploads',

    // Pengaturan Magang
    'app_version'   => '2.5.0',
    'ruang_default' => 'Wakil Rektor 1',
    'max_foto'      => 3,
    'max_foto_bytes'=> 5 * 1024 * 1024, // 5 MB per foto
];
