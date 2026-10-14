<?php
/**
 * PORTAL MAGANG — UNAS PASIM
 * -----------------------------------------------------------------------------
 * Titik masuk aplikasi (HTML + Tailwind CSS via CDN).
 * Logika front-end ada di assets/js/*.js, gaya di assets/css/app.css.
 *
 *  >>> URL Google Apps Script diisi di:  assets/js/config.js  (SCRIPT_URL) <<<
 */
function asset(string $path): string
{
    $file = __DIR__ . '/' . $path;
    return $path . '?v=' . (is_file($file) ? filemtime($file) : 1);   // cache-busting otomatis
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f2347">
    <title>Portal Magang — UNAS PASIM</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230f2347'/%3E%3Cpath d='M16 7l10 5-10 5-10-5 10-5zm-6 9v4c0 2 3 4 6 4s6-2 6-4v-4l-6 3-6-3z' fill='white'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) untuk utilitas tata letak -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { corePlugins: { preflight: true }, theme: { extend: { fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } };</script>

    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body>

<noscript><p style="padding:24px">Aplikasi ini membutuhkan JavaScript. Aktifkan JavaScript pada browser Anda.</p></noscript>

<!-- =====================================================================
     HALAMAN LOGIN
     ===================================================================== -->
<div id="login-screen" class="screen login-bg" hidden>
    <div class="login-card">
        <div class="flex items-center gap-3 mb-5">
            <span class="logo">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/></svg>
            </span>
            <div>
                <h1 style="font-size:20px;line-height:1.2">Portal Magang</h1>
                <p style="color:var(--muted);font-size:13px">Universitas Nasional PASIM</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap mb-6">
            <span class="conn load" id="conn" role="status"><span class="dot"></span><span id="connText">Menghubungkan…</span></span>
            <button type="button" id="connRetry" class="btn btn-ghost btn-sm hidden">Coba lagi</button>
        </div>

        <form id="loginForm" novalidate autocomplete="off">
            <div class="field">
                <label class="label" for="loginKey">Key Akses<span class="req">*</span></label>
                <input class="input" id="loginKey" type="text" maxlength="30" placeholder="Masukkan key akses Anda"
                       autocapitalize="characters" spellcheck="false" autocomplete="off">
                <p class="hint">Awalan <code>DOSEN</code>, <code>MHS</code>, atau <code>ADMIN</code> menentukan dashboard Anda.</p>
                <p class="field-error" data-err="loginKey" role="alert"></p>
            </div>

            <!-- Muncul otomatis bila key diawali MHS -->
            <div id="mhsFields" class="hidden">
                <div class="field">
                    <label class="label" for="loginNama">Nama Pemagang<span class="req">*</span></label>
                    <select class="input" id="loginNama"><option value="">— Pilih nama Anda —</option></select>
                    <p id="namaEmpty" class="hint hidden">Belum ada pemagang terdaftar. Minta dosen pembimbing menambahkan nama &amp; NIM Anda.</p>
                    <p class="field-error" data-err="loginNama" role="alert"></p>
                </div>
                <div class="field">
                    <label class="label" for="loginNim">NIM<span class="req">*</span></label>
                    <input class="input" id="loginNim" type="text" inputmode="numeric" maxlength="15" placeholder="NIM sesuai data dosen">
                    <p class="hint">NIM harus sama dengan yang didaftarkan oleh dosen.</p>
                    <p class="field-error" data-err="loginNim" role="alert"></p>
                </div>
            </div>

            <button id="loginBtn" class="btn btn-primary btn-block" type="submit" style="height:42px">Masuk</button>
        </form>

        <p class="text-center mt-6" style="font-size:12.5px">
            <a href="https://vcreed.my.id/universitas/">&larr; Kembali ke Portal UNAS PASIM</a>
        </p>
        <p class="text-center mt-3" style="font-size:12px;color:var(--muted)">&copy; <?= date('Y') ?> Portal Magang · UNAS PASIM</p>
    </div>
</div>

<!-- =====================================================================
     APLIKASI (DASHBOARD)
     ===================================================================== -->
<div id="app-screen" class="screen shell" hidden>
    <aside id="sidebar" class="sidebar" aria-label="Menu utama">
        <div class="brand">
            <span class="logo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/></svg>
            </span>
            <div><b>Portal Magang</b><small id="sideRole">—</small></div>
        </div>
        <nav id="nav" class="nav"></nav>
        <div class="side-user">
            <div class="who"><b id="sideName">—</b><small id="sideKey">—</small></div>
            <button class="btn btn-side" data-act="logout">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Keluar
            </button>
        </div>
    </aside>
    <div id="navBackdrop" class="backdrop-nav" data-act="nav-close"></div>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn menu-btn" data-act="nav-open" aria-label="Buka menu">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="flex-1 min-w-0">
                <h1 id="pageTitle" class="truncate"></h1>
                <p id="pageSub" class="truncate"></p>
            </div>
            <a class="btn btn-ghost btn-sm hide-sm" href="https://vcreed.my.id/universitas/" style="text-decoration:none">Portal UNAS</a>
            <button class="btn btn-outline btn-sm" data-act="refresh">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                <span class="hide-sm">Muat ulang</span>
            </button>
        </header>
        <main id="view" class="content" tabindex="-1"></main>
    </div>
</div>

<div id="modal-root"></div>
<div id="toasts" aria-live="polite"></div>

<!-- Urutan penting: config -> api -> ui -> core -> auth -> views -> main -->
<script src="<?= asset('assets/js/config.js') ?>"></script>
<script src="<?= asset('assets/js/api.js') ?>"></script>
<script src="<?= asset('assets/js/ui.js') ?>"></script>
<script src="<?= asset('assets/js/core.js') ?>"></script>
<script src="<?= asset('assets/js/auth.js') ?>"></script>
<script src="<?= asset('assets/js/views-dosen.js') ?>"></script>
<script src="<?= asset('assets/js/views-mhs.js') ?>"></script>
<script src="<?= asset('assets/js/views-admin.js') ?>"></script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
</body>
</html>
