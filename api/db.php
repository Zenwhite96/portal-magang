<?php
/**
 * PORTAL MAGANG — Database Handler (PDO MySQL & SQLite Hybrid)
 * -----------------------------------------------------------------------------
 * Menyediakan koneksi PDO dengan auto-migration tabel dan fallback cerdas.
 */

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;
    private static string $driverUsed = 'none';

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = require __DIR__ . '/config.php';
        $driver = strtolower($config['db_driver'] ?? 'mysql');

        // Coba MySQL terlebih dahulu jika disetel
        if ($driver === 'mysql') {
            try {
                $m = $config['mysql'];
                $host = $m['host'];
                $port = $m['port'];
                $dbName = $m['database'];
                $user = $m['username'];
                $pass = $m['password'];
                $charset = $m['charset'] ?? 'utf8mb4';

                // Buat koneksi awal ke server MySQL untuk memastikan database tersedia
                $initDsn = "mysql:host={$host};port={$port};charset={$charset}";
                $initPdo = new PDO($initDsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $initPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE utf8mb4_unicode_ci");

                // Sambungkan ke database portal_magang
                $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                self::$driverUsed = 'mysql';
            } catch (Throwable $e) {
                // Jika MySQL tidak aktif / gagal dihubungi, fallback ke SQLite
                error_log('[PortalMagang] MySQL connection failed, fallback to SQLite: ' . $e->getMessage());
                self::$instance = self::connectSqlite($config);
                self::$driverUsed = 'sqlite';
            }
        } else {
            self::$instance = self::connectSqlite($config);
            self::$driverUsed = 'sqlite';
        }

        self::ensureSchema(self::$instance);
        return self::$instance;
    }

    public static function getDriver(): string
    {
        return self::$driverUsed;
    }

    private static function connectSqlite(array $config): PDO
    {
        $path = $config['sqlite']['path'];
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA journal_mode = WAL;');
        $pdo->exec('PRAGMA foreign_keys = ON;');
        return $pdo;
    }

    private static function ensureSchema(PDO $pdo): void
    {
        $isSqlite = self::$driverUsed === 'sqlite';

        // 1. Tabel Pemagang
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pemagang (
                id VARCHAR(50) PRIMARY KEY,
                nim VARCHAR(30) NOT NULL UNIQUE,
                nama VARCHAR(150) NOT NULL,
                prodi VARCHAR(100) NOT NULL,
                dosen_key VARCHAR(50) NOT NULL,
                created_at VARCHAR(40) NOT NULL
            ) " . ($isSqlite ? "" : "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")
        );

        // 2. Tabel Jadwal
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS jadwal (
                id VARCHAR(50) PRIMARY KEY,
                dosen_key VARCHAR(50) NOT NULL,
                judul VARCHAR(255) NOT NULL,
                tanggal VARCHAR(20) NOT NULL,
                jam_mulai VARCHAR(10) NOT NULL,
                jam_selesai VARCHAR(10) NOT NULL,
                ruang VARCHAR(100) NOT NULL,
                tugas TEXT NOT NULL,
                kuota INT NOT NULL DEFAULT 1,
                status VARCHAR(20) NOT NULL DEFAULT 'Buka',
                created_at VARCHAR(40) NOT NULL
            ) " . ($isSqlite ? "" : "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")
        );

        // 3. Tabel Pengajuan
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pengajuan (
                id VARCHAR(50) PRIMARY KEY,
                jadwal_id VARCHAR(50) NOT NULL,
                nim VARCHAR(30) NOT NULL,
                nama VARCHAR(150) NOT NULL,
                catatan TEXT,
                status VARCHAR(20) NOT NULL DEFAULT 'Diajukan',
                created_at VARCHAR(40) NOT NULL,
                keputusan_at VARCHAR(40) DEFAULT ''
            ) " . ($isSqlite ? "" : "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")
        );

        // 4. Tabel Laporan
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS laporan (
                id VARCHAR(50) PRIMARY KEY,
                nim VARCHAR(30) NOT NULL,
                nama VARCHAR(150) NOT NULL,
                jadwal_id VARCHAR(50) DEFAULT '',
                tanggal VARCHAR(20) NOT NULL,
                jam_mulai VARCHAR(10) NOT NULL,
                jam_selesai VARCHAR(10) NOT NULL,
                durasi_jam DECIMAL(5,2) NOT NULL,
                ruang VARCHAR(100) NOT NULL,
                kegiatan TEXT NOT NULL,
                kendala TEXT,
                foto_ids TEXT,
                status VARCHAR(20) NOT NULL DEFAULT 'Menunggu',
                catatan_dosen TEXT,
                validator_key VARCHAR(50) DEFAULT '',
                created_at VARCHAR(40) NOT NULL,
                validated_at VARCHAR(40) DEFAULT ''
            ) " . ($isSqlite ? "" : "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci")
        );

        // Cek apakah tabel pemagang kosong, jika iya seed data awal
        $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM pemagang");
        $row = $stmt->fetch();
        if ((int)$row['cnt'] === 0) {
            self::seedInitialData($pdo);
        }
    }

    private static function seedInitialData(PDO $pdo): void
    {
        $now = date('c');

        // Pemagang
        $insP = $pdo->prepare("INSERT INTO pemagang (id, nim, nama, prodi, dosen_key, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $dataPemagang = [
            ['p1', '2023010001', 'Budi Santoso', 'Informatika', 'DOSEN001', $now],
            ['p2', '2023010002', 'Siti Aminah', 'Sistem Informasi', 'DOSEN001', $now],
            ['p3', '2023010003', 'Rian Pratama', 'Manajemen', 'DOSEN001', $now],
        ];
        foreach ($dataPemagang as $row) {
            $insP->execute($row);
        }

        // Jadwal
        $insJ = $pdo->prepare("INSERT INTO jadwal (id, dosen_key, judul, tanggal, jam_mulai, jam_selesai, ruang, tugas, kuota, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $dataJadwal = [
            ['j1', 'DOSEN001', 'Pengarsipan Dokumen Akreditasi', date('Y-m-d', strtotime('+1 day')), '09:00', '12:00', 'Wakil Rektor 1', 'Menyusun berkas borang akreditasi program studi ke dalam ordner sesuai standar SPMI.', 2, 'Buka', $now],
            ['j2', 'DOSEN001', 'Rekapitulasi Data Kehadiran Dosen', date('Y-m-d', strtotime('+2 days')), '13:00', '16:00', 'Wakil Rektor 1', 'Membantu staf administrasi menginput presensi perkuliahan semester ganjil ke spreadsheet.', 3, 'Buka', $now],
            ['j3', 'DOSEN001', 'Persiapan Rapat Senat Akademik', date('Y-m-d', strtotime('+3 days')), '09:00', '15:00', 'Wakil Rektor 1', 'Menyiapkan materi rapat senat, fotokopi berkas, dan pengaturan ruang rapat.', 2, 'Buka', $now],
        ];
        foreach ($dataJadwal as $row) {
            $insJ->execute($row);
        }

        // Pengajuan contoh
        $insPj = $pdo->prepare("INSERT INTO pengajuan (id, jadwal_id, nim, nama, catatan, status, created_at, keputusan_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insPj->execute(['pj1', 'j1', '2023010001', 'Budi Santoso', 'Siap membantu pengarsipan tepat waktu.', 'Disetujui', $now, $now]);

        // Laporan contoh
        $insL = $pdo->prepare("INSERT INTO laporan (id, nim, nama, jadwal_id, tanggal, jam_mulai, jam_selesai, durasi_jam, ruang, kegiatan, kendala, foto_ids, status, catatan_dosen, validator_key, created_at, validated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insL->execute([
            'l1', '2023010001', 'Budi Santoso', 'j1', date('Y-m-d', strtotime('-1 day')),
            '09:00', '12:00', 3.0, 'Wakil Rektor 1',
            'Membantu merapikan berkas arsip dan input data mahasiswa ke sistem informasi.',
            'Beberapa formulir data mahasiswa belum terisi lengkap.',
            '', 'Tervalidasi', 'Kerja bagus, arsip tersusun rapi.', 'DOSEN001', $now, $now
        ]);
    }
}
