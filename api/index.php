<?php
/**
 * PORTAL MAGANG — API Endpoint (PHP + PDO)
 * -----------------------------------------------------------------------------
 * Menyediakan seluruh REST API untuk portal magang.
 * Kompatibel penuh dengan antarmuka front-end v2.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

// Header CORS & format respons
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/db.php';
$config = require __DIR__ . '/config.php';

function json_res(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_err(string $message, int $statusCode = 400, string $code = 'ERROR'): void
{
    json_res(['status' => 'error', 'code' => $code, 'message' => $message], $statusCode);
}

// Tangkap input
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input = [];

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw !== false && trim($raw) !== '') {
        $parsed = json_decode($raw, true);
        if (is_array($parsed)) {
            $input = $parsed;
        }
    }
    if (empty($input) && !empty($_POST)) {
        $input = $_POST;
    }
    if (empty($action) && isset($input['action'])) {
        $action = (string)$input['action'];
    }
} else {
    $input = $_GET;
}

if (empty($action)) {
    json_err('Aksi (action) tidak ditentukan.', 400, 'NO_ACTION');
}

// Helpers
function uid(string $prefix = ''): string
{
    return $prefix . bin2hex(random_bytes(6));
}

function clean(mixed $v): string
{
    return trim((string)($v ?? ''));
}

function toMin(string $t): int
{
    $parts = explode(':', $t);
    return ((int)($parts[0] ?? 0) * 60) + (int)($parts[1] ?? 0);
}

try {
    $pdo = Database::getConnection();

    switch ($action) {

        /* ================================================================
           GET: ping
           ================================================================ */
        case 'ping':
            json_res([
                'status'      => 'ok',
                'versi'       => $config['app_version'],
                'engine'      => 'PHP ' . PHP_VERSION,
                'driver'      => Database::getDriver(),
                'ruang'       => $config['ruang_default'],
                'waktu'       => date('c'),
            ]);
            break;

        /* ================================================================
           GET: pemagangPublic
           ================================================================ */
        case 'pemagangPublic':
            $stmt = $pdo->query("SELECT id, nama, prodi FROM pemagang ORDER BY nama ASC");
            $rows = $stmt->fetchAll();
            json_res(['status' => 'ok', 'data' => $rows]);
            break;

        /* ================================================================
           POST: loginPemagang
           ================================================================ */
        case 'loginPemagang':
            $pid = clean($input['pid'] ?? '');
            $nim = clean($input['nim'] ?? '');

            if ($pid === '') json_err('Pilih nama Anda terlebih dahulu.');
            if (!preg_match('/^[0-9]{6,15}$/', $nim)) json_err('NIM harus berupa 6–15 digit angka.');

            $stmt = $pdo->prepare("SELECT id, nim, nama, prodi, dosen_key FROM pemagang WHERE id = ?");
            $stmt->execute([$pid]);
            $p = $stmt->fetch();

            if (!$p) json_err('Data pemagang tidak ditemukan. Hubungi dosen pembimbing.');
            if ($p['nim'] !== $nim) json_err('NIM tidak cocok dengan nama yang dipilih.');

            json_res([
                'status'  => 'ok',
                'profile' => [
                    'id'       => $p['id'],
                    'nim'      => $p['nim'],
                    'nama'     => $p['nama'],
                    'prodi'    => $p['prodi'],
                    'dosenKey' => $p['dosen_key'],
                ]
            ]);
            break;

        /* ================================================================
           GET: bundle
           ================================================================ */
        case 'bundle':
            $role = strtoupper(clean($input['role'] ?? ''));
            $key  = clean($input['key'] ?? '');
            $nim  = clean($input['nim'] ?? '');

            // Ambil semua jadwal beserta jumlah pengajuan aktif (terisi)
            $stmtJadwal = $pdo->query("
                SELECT j.id, j.dosen_key AS dosenKey, j.judul, j.tanggal,
                       j.jam_mulai AS jamMulai, j.jam_selesai AS jamSelesai,
                       j.ruang, j.tugas, j.kuota, j.status, j.created_at AS timestamp,
                       COALESCE(SUM(CASE WHEN pj.status IN ('Diajukan', 'Disetujui') THEN 1 ELSE 0 END), 0) AS terisi
                FROM jadwal j
                LEFT JOIN pengajuan pj ON j.id = pj.jadwal_id
                GROUP BY j.id, j.dosen_key, j.judul, j.tanggal, j.jam_mulai, j.jam_selesai, j.ruang, j.tugas, j.kuota, j.status, j.created_at
            ");
            $allJadwal = $stmtJadwal->fetchAll();
            foreach ($allJadwal as &$j) {
                $j['kuota'] = (int)$j['kuota'];
                $j['terisi'] = (int)$j['terisi'];
            }
            unset($j);

            // Seluruh pengajuan
            $stmtPengajuan = $pdo->query("
                SELECT id, jadwal_id AS jadwalId, nim, nama, catatan, status,
                       created_at AS timestamp, keputusan_at AS keputusanAt
                FROM pengajuan
            ");
            $allPengajuan = $stmtPengajuan->fetchAll();

            // Seluruh laporan
            $stmtLaporan = $pdo->query("
                SELECT id, nim, nama, jadwal_id AS jadwalId, tanggal,
                       jam_mulai AS jamMulai, jam_selesai AS jamSelesai,
                       durasi_jam AS durasiJam, ruang, kegiatan, kendala,
                       foto_ids AS fotoIds, status, catatan_dosen AS catatanDosen,
                       validator_key AS validatorKey, created_at AS timestamp,
                       validated_at AS validatedAt
                FROM laporan
            ");
            $allLaporan = $stmtLaporan->fetchAll();
            foreach ($allLaporan as &$l) {
                $l['durasiJam'] = (float)$l['durasiJam'];
            }
            unset($l);

            // Seluruh pemagang
            $stmtPemagang = $pdo->query("
                SELECT id, nim, nama, prodi, dosen_key AS dosenKey, created_at AS timestamp
                FROM pemagang
            ");
            $allPemagang = $stmtPemagang->fetchAll();

            $data = ['pemagang' => [], 'jadwal' => [], 'pengajuan' => [], 'laporan' => []];

            if ($role === 'ADMIN') {
                if (!str_starts_with($key, 'ADMIN')) json_err('Akses ditolak: Key admin tidak valid.');
                $data = [
                    'pemagang'  => $allPemagang,
                    'jadwal'    => $allJadwal,
                    'pengajuan' => $allPengajuan,
                    'laporan'   => $allLaporan,
                ];
            } elseif ($role === 'DOSEN') {
                if (!str_starts_with($key, 'DOSEN')) json_err('Akses ditolak: Key dosen tidak valid.');
                $myP = array_values(array_filter($allPemagang, fn($p) => $p['dosenKey'] === $key));
                $nims = array_flip(array_column($myP, 'nim'));
                $myJ = array_values(array_filter($allJadwal, fn($j) => $j['dosenKey'] === $key));
                $jids = array_flip(array_column($myJ, 'id'));

                $data = [
                    'pemagang'  => $myP,
                    'jadwal'    => $myJ,
                    'pengajuan' => array_values(array_filter($allPengajuan, fn($pj) => isset($jids[$pj['jadwalId']]))),
                    'laporan'   => array_values(array_filter($allLaporan, fn($l) => isset($nims[$l['nim']]))),
                ];
            } elseif ($role === 'MHS') {
                $stmtMe = $pdo->prepare("SELECT id, nim, nama, prodi, dosen_key AS dosenKey, created_at AS timestamp FROM pemagang WHERE nim = ?");
                $stmtMe->execute([$nim]);
                $me = $stmtMe->fetch();
                if (!$me) json_err('Data pemagang tidak ditemukan.');

                $data = [
                    'pemagang'  => [$me],
                    'jadwal'    => array_values(array_filter($allJadwal, fn($j) => $j['dosenKey'] === $me['dosenKey'])),
                    'pengajuan' => array_values(array_filter($allPengajuan, fn($pj) => $pj['nim'] === $nim)),
                    'laporan'   => array_values(array_filter($allLaporan, fn($l) => $l['nim'] === $nim)),
                ];
            } else {
                json_err('Peran (role) pengguna tidak dikenali.');
            }

            json_res(['status' => 'ok', 'data' => $data, 'waktu' => date('c')]);
            break;

        /* ================================================================
           POST: tambahPemagang
           ================================================================ */
        case 'tambahPemagang':
            $key = strtoupper(clean($input['key'] ?? ''));
            if (!str_starts_with($key, 'DOSEN')) json_err('Akses ditolak: Hanya dosen pembimbing yang dapat mendaftarkan pemagang.');

            $items = $input['items'] ?? [];
            if (!is_array($items) || count($items) === 0) json_err('Tidak ada data pemagang yang dikirim.');

            $stmtExist = $pdo->prepare("SELECT nim FROM pemagang WHERE nim = ?");
            $stmtIns = $pdo->prepare("INSERT INTO pemagang (id, nim, nama, prodi, dosen_key, created_at) VALUES (?, ?, ?, ?, ?, ?)");

            $added = [];
            $skipped = [];

            foreach ($items as $it) {
                $nim   = clean($it['nim'] ?? '');
                $nama  = clean($it['nama'] ?? '');
                $prodi = clean($it['prodi'] ?? '');

                if (!preg_match('/^[0-9]{6,15}$/', $nim)) {
                    $skipped[] = ['nim' => $nim, 'nama' => $nama, 'why' => 'NIM harus berupa 6–15 digit angka'];
                    continue;
                }
                if (mb_strlen($nama) < 3) {
                    $skipped[] = ['nim' => $nim, 'nama' => $nama, 'why' => 'Nama minimal 3 karakter'];
                    continue;
                }

                $stmtExist->execute([$nim]);
                if ($stmtExist->fetch()) {
                    $skipped[] = ['nim' => $nim, 'nama' => $nama, 'why' => 'NIM sudah terdaftar'];
                    continue;
                }

                $id = uid('p_');
                $stmtIns->execute([$id, $nim, $nama, $prodi, $key, date('c')]);
                $added[] = ['id' => $id, 'nim' => $nim, 'nama' => $nama, 'prodi' => $prodi];
            }

            json_res(['status' => 'ok', 'added' => $added, 'skipped' => $skipped]);
            break;

        /* ================================================================
           POST: hapusPemagang
           ================================================================ */
        case 'hapusPemagang':
            $key = strtoupper(clean($input['key'] ?? ''));
            $pid = clean($input['pid'] ?? '');

            if (!str_starts_with($key, 'DOSEN') && !str_starts_with($key, 'ADMIN')) {
                json_err('Akses ditolak.');
            }

            $stmt = $pdo->prepare("DELETE FROM pemagang WHERE id = ?" . (str_starts_with($key, 'DOSEN') ? " AND dosen_key = ?" : ""));
            $params = str_starts_with($key, 'DOSEN') ? [$pid, $key] : [$pid];
            $stmt->execute($params);

            json_res(['status' => 'ok']);
            break;

        /* ================================================================
           POST: buatJadwal
           ================================================================ */
        case 'buatJadwal':
            $key        = strtoupper(clean($input['key'] ?? ''));
            if (!str_starts_with($key, 'DOSEN')) json_err('Hanya dosen yang dapat membuat jadwal.');

            $judul      = clean($input['judul'] ?? '');
            $tanggal    = clean($input['tanggal'] ?? '');
            $jamMulai   = clean($input['jamMulai'] ?? '');
            $jamSelesai = clean($input['jamSelesai'] ?? '');
            $ruang      = clean($input['ruang'] ?? $config['ruang_default']);
            $tugas      = clean($input['tugas'] ?? '');
            $kuota      = (int)($input['kuota'] ?? 1);

            if (mb_strlen($judul) < 3) json_err('Judul jadwal minimal 3 karakter.');
            if (mb_strlen($tugas) < 5) json_err('Deskripsi tugas minimal 5 karakter.');
            if ($kuota < 1 || $kuota > 50) json_err('Kuota harus antara 1 sampai 50 pemagang.');
            if (toMin($jamSelesai) <= toMin($jamMulai)) json_err('Jam selesai harus setelah jam mulai.');

            // Deteksi bentrok jadwal di ruangan yang sama
            $stmtClash = $pdo->prepare("
                SELECT id, judul, jam_mulai, jam_selesai FROM jadwal
                WHERE ruang = ? AND tanggal = ? AND status = 'Buka'
            ");
            $stmtClash->execute([$ruang, $tanggal]);
            $sNew = toMin($jamMulai);
            $eNew = toMin($jamSelesai);
            foreach ($stmtClash->fetchAll() as $ex) {
                $sEx = toMin($ex['jam_mulai']);
                $eEx = toMin($ex['jam_selesai']);
                if (max($sNew, $sEx) < min($eNew, $eEx)) {
                    json_err("Bentrok jadwal di ruang {$ruang} dengan '{$ex['judul']}' ({$ex['jam_mulai']}–{$ex['jam_selesai']}).");
                }
            }

            $id = uid('j_');
            $stmt = $pdo->prepare("
                INSERT INTO jadwal (id, dosen_key, judul, tanggal, jam_mulai, jam_selesai, ruang, tugas, kuota, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Buka', ?)
            ");
            $stmt->execute([$id, $key, $judul, $tanggal, $jamMulai, $jamSelesai, $ruang, $tugas, $kuota, date('c')]);

            json_res(['status' => 'ok', 'id' => $id]);
            break;

        /* ================================================================
           POST: ubahStatusJadwal
           ================================================================ */
        case 'ubahStatusJadwal':
            $key    = strtoupper(clean($input['key'] ?? ''));
            $id     = clean($input['id'] ?? '');
            $status = clean($input['status'] ?? '');

            if (!in_array($status, ['Buka', 'Tutup'], true)) json_err('Status jadwal tidak valid.');

            $stmt = $pdo->prepare("UPDATE jadwal SET status = ? WHERE id = ?" . (str_starts_with($key, 'DOSEN') ? " AND dosen_key = ?" : ""));
            $params = str_starts_with($key, 'DOSEN') ? [$status, $id, $key] : [$status, $id];
            $stmt->execute($params);

            json_res(['status' => 'ok']);
            break;

        /* ================================================================
           POST: hapusJadwal
           ================================================================ */
        case 'hapusJadwal':
            $key = strtoupper(clean($input['key'] ?? ''));
            $id  = clean($input['id'] ?? '');

            $stmt = $pdo->prepare("DELETE FROM jadwal WHERE id = ?" . (str_starts_with($key, 'DOSEN') ? " AND dosen_key = ?" : ""));
            $params = str_starts_with($key, 'DOSEN') ? [$id, $key] : [$id];
            $stmt->execute($params);

            // Hapus pengajuan terkait
            $pdo->prepare("DELETE FROM pengajuan WHERE jadwal_id = ?")->execute([$id]);

            json_res(['status' => 'ok']);
            break;

        /* ================================================================
           POST: ajukanJadwal
           ================================================================ */
        case 'ajukanJadwal':
            $nim      = clean($input['nim'] ?? '');
            $jadwalId = clean($input['jadwalId'] ?? '');
            $catatan  = clean($input['catatan'] ?? '');

            // Pastikan pemagang valid
            $stmtP = $pdo->prepare("SELECT nim, nama, dosen_key FROM pemagang WHERE nim = ?");
            $stmtP->execute([$nim]);
            $p = $stmtP->fetch();
            if (!$p) json_err('Pemagang tidak ditemukan.');

            // Pastikan jadwal valid
            $stmtJ = $pdo->prepare("SELECT * FROM jadwal WHERE id = ?");
            $stmtJ->execute([$jadwalId]);
            $j = $stmtJ->fetch();
            if (!$j) json_err('Jadwal tidak ditemukan.');
            if ($j['dosen_key'] !== $p['dosen_key']) json_err('Jadwal ini bukan dari dosen pembimbing Anda.');
            if ($j['status'] !== 'Buka') json_err('Jadwal ini sudah ditutup.');

            // Cek apakah sudah pernah mengajukan
            $stmtCek = $pdo->prepare("SELECT id, status FROM pengajuan WHERE jadwal_id = ? AND nim = ?");
            $stmtCek->execute([$jadwalId, $nim]);
            $existPj = $stmtCek->fetch();
            if ($existPj && in_array($existPj['status'], ['Diajukan', 'Disetujui'], true)) {
                json_err('Anda sudah mengajukan jadwal ini.');
            }

            // Cek kuota
            $stmtCnt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM pengajuan WHERE jadwal_id = ? AND status IN ('Diajukan', 'Disetujui')");
            $stmtCnt->execute([$jadwalId]);
            $cntRow = $stmtCnt->fetch();
            if ((int)$cntRow['cnt'] >= (int)$j['kuota']) {
                json_err('Kuota jadwal ini sudah penuh.');
            }

            $id = uid('pj_');
            $stmtIns = $pdo->prepare("
                INSERT INTO pengajuan (id, jadwal_id, nim, nama, catatan, status, created_at, keputusan_at)
                VALUES (?, ?, ?, ?, ?, 'Diajukan', ?, '')
            ");
            $stmtIns->execute([$id, $jadwalId, $nim, $p['nama'], $catatan, date('c')]);

            json_res(['status' => 'ok', 'id' => $id]);
            break;

        /* ================================================================
           POST: batalkanPengajuan
           ================================================================ */
        case 'batalkanPengajuan':
            $id  = clean($input['id'] ?? '');
            $nim = clean($input['nim'] ?? '');

            $stmt = $pdo->prepare("DELETE FROM pengajuan WHERE id = ? AND nim = ? AND status = 'Diajukan'");
            $stmt->execute([$id, $nim]);

            json_res(['status' => 'ok']);
            break;

        /* ================================================================
           POST: putusPengajuan
           ================================================================ */
        case 'putusPengajuan':
            $key    = strtoupper(clean($input['key'] ?? ''));
            $id     = clean($input['id'] ?? '');
            $status = clean($input['status'] ?? '');

            if (!in_array($status, ['Disetujui', 'Ditolak'], true)) json_err('Keputusan tidak valid.');

            $stmt = $pdo->prepare("UPDATE pengajuan SET status = ?, keputusan_at = ? WHERE id = ?");
            $stmt->execute([$status, date('c'), $id]);

            json_res(['status' => 'ok']);
            break;

        /* ================================================================
           POST: kirimLaporan
           ================================================================ */
        case 'kirimLaporan':
            $nim        = clean($input['nim'] ?? '');
            $tanggal    = clean($input['tanggal'] ?? '');
            $jamMulai   = clean($input['jamMulai'] ?? '');
            $jamSelesai = clean($input['jamSelesai'] ?? '');
            $jadwalId   = clean($input['jadwalId'] ?? '');
            $kegiatan   = clean($input['kegiatan'] ?? '');
            $kendala    = clean($input['kendala'] ?? '');
            $fotos      = $input['fotos'] ?? [];

            // Validasi pemagang
            $stmtP = $pdo->prepare("SELECT nim, nama FROM pemagang WHERE nim = ?");
            $stmtP->execute([$nim]);
            $p = $stmtP->fetch();
            if (!$p) json_err('Pemagang tidak terdaftar.');

            if (mb_strlen($kegiatan) < 10) json_err('Uraian kegiatan minimal 10 karakter.');
            $startMin = toMin($jamMulai);
            $endMin   = toMin($jamSelesai);
            if ($endMin <= $startMin) json_err('Jam selesai harus setelah jam mulai.');

            $durasiJam = round(($endMin - $startMin) / 60, 2);

            // Simpan foto bukti ke direktori uploads/
            $savedFotoPaths = [];
            $uploadDir = $config['upload_dir'];
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (is_array($fotos)) {
                $fotoIdx = 0;
                foreach ($fotos as $f) {
                    if ($fotoIdx >= $config['max_foto']) break;
                    $dataStr = (string)($f['data'] ?? '');
                    if ($dataStr === '') continue;

                    if (str_starts_with($dataStr, 'data:image/')) {
                        $parts = explode(',', $dataStr, 2);
                        $rawBytes = base64_decode($parts[1] ?? '');
                        $meta = $parts[0];
                    } else {
                        $rawBytes = base64_decode($dataStr);
                        $meta = (string)($f['mime'] ?? 'image/jpeg');
                    }

                    if ($rawBytes !== false && strlen($rawBytes) > 0 && strlen($rawBytes) <= $config['max_foto_bytes']) {
                        $ext = 'jpg';
                        if (str_contains($meta, 'png')) $ext = 'png';
                        elseif (str_contains($meta, 'webp')) $ext = 'webp';

                        $fileName = 'bukti_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $targetFile = $uploadDir . '/' . $fileName;

                        if (file_put_contents($targetFile, $rawBytes) !== false) {
                            $savedFotoPaths[] = 'uploads/' . $fileName;
                            $fotoIdx++;
                        }
                    }
                }
            }

            $fotoIdsStr = implode(',', $savedFotoPaths);
            $id = uid('l_');

            $stmtIns = $pdo->prepare("
                INSERT INTO laporan (id, nim, nama, jadwal_id, tanggal, jam_mulai, jam_selesai, durasi_jam, ruang, kegiatan, kendala, foto_ids, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu', ?)
            ");
            $stmtIns->execute([
                $id, $nim, $p['nama'], $jadwalId, $tanggal, $jamMulai, $jamSelesai,
                $durasiJam, $config['ruang_default'], $kegiatan, $kendala, $fotoIdsStr, date('c')
            ]);

            json_res(['status' => 'ok', 'id' => $id]);
            break;

        /* ================================================================
           POST: hapusLaporan
           ================================================================ */
        case 'hapusLaporan':
            $id  = clean($input['id'] ?? '');
            $nim = clean($input['nim'] ?? '');
            $key = strtoupper(clean($input['key'] ?? ''));

            // Cek laporan & hapus file foto fisiknya
            $stmtL = $pdo->prepare("SELECT foto_ids, nim FROM laporan WHERE id = ?");
            $stmtL->execute([$id]);
            $lap = $stmtL->fetch();
            if ($lap) {
                if ($nim !== '' && $lap['nim'] !== $nim && !str_starts_with($key, 'ADMIN') && !str_starts_with($key, 'DOSEN')) {
                    json_err('Akses ditolak.');
                }
                if (!empty($lap['foto_ids'])) {
                    $photos = explode(',', $lap['foto_ids']);
                    foreach ($photos as $ph) {
                        $fPath = __DIR__ . '/../' . trim($ph);
                        if (is_file($fPath)) {
                            @unlink($fPath);
                        }
                    }
                }
                $pdo->prepare("DELETE FROM laporan WHERE id = ?")->execute([$id]);
            }

            json_res(['status' => 'ok']);
            break;

        /* ================================================================
           POST: validasiLaporan
           ================================================================ */
        case 'validasiLaporan':
            $key     = strtoupper(clean($input['key'] ?? ''));
            $id      = clean($input['id'] ?? '');
            $status  = clean($input['status'] ?? '');
            $catatan = clean($input['catatan'] ?? '');

            if (!str_starts_with($key, 'DOSEN') && !str_starts_with($key, 'ADMIN')) {
                json_err('Hanya dosen/admin yang dapat memvalidasi laporan.');
            }
            if (!in_array($status, ['Tervalidasi', 'Ditolak'], true)) {
                json_err('Status validasi tidak valid.');
            }

            $stmt = $pdo->prepare("
                UPDATE laporan
                SET status = ?, catatan_dosen = ?, validator_key = ?, validated_at = ?
                WHERE id = ?
            ");
            $stmt->execute([$status, $catatan, $key, date('c'), $id]);

            json_res(['status' => 'ok']);
            break;

        default:
            json_err("Aksi '{$action}' tidak dikenali.", 404, 'UNKNOWN_ACTION');
            break;
    }

} catch (Throwable $e) {
    error_log('[PortalMagang API Error] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    json_err('Terjadi kesalahan pada server: ' . $e->getMessage(), 500, 'SERVER_EXCEPTION');
}
