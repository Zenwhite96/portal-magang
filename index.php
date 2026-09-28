<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Magang — Sistem Informasi Pemagangan</title>

    <!-- ============================================================
         TAILWIND CSS via CDN
         ============================================================ -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ============================================================
         FONT: Inter (Google Fonts)
         ============================================================ -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- ============================================================
         TAILWIND CONFIG — Custom Palette Universitas
         ============================================================ -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        },
                        navy: {
                            800: '#0f2557',
                            900: '#0a1a3d',
                        }
                    },
                    animation: {
                        'fade-in':    'fadeIn .35s ease-out',
                        'slide-up':   'slideUp .4s ease-out',
                        'slide-down': 'slideDown .35s ease-out',
                        'pulse-soft': 'pulseSoft 2s infinite',
                        'spin-slow':  'spin 2s linear infinite',
                    },
                    keyframes: {
                        fadeIn:     { '0%': { opacity: '0' },                    '100%': { opacity: '1' } },
                        slideUp:    { '0%': { opacity: '0', transform: 'translateY(24px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        slideDown:  { '0%': { opacity: '0', transform: 'translateY(-12px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        pulseSoft:  { '0%,100%': { opacity: '1' }, '50%': { opacity: '.65' } },
                    }
                }
            }
        }
    </script>

    <style>
        /* ── Global ─────────────────────────────────── */
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f4ff; }

        /* ── Page Transition ─────────────────────────── */
        .page          { display: none; opacity: 0; transform: translateY(18px); transition: opacity .35s ease, transform .35s ease; }
        .page.active   { display: block; opacity: 1; transform: translateY(0); }
        .page.leaving  { opacity: 0; transform: translateY(-12px); transition: opacity .25s ease, transform .25s ease; }

        /* ── Glass Card ──────────────────────────────── */
        .glass {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,.6);
        }

        /* ── Gradient BG ─────────────────────────────── */
        .hero-bg {
            background: linear-gradient(135deg, #0f2557 0%, #1d4ed8 50%, #3b82f6 100%);
        }

        /* ── Sidebar ─────────────────────────────────── */
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #0f2557 0%, #1e40af 100%); }

        /* ── Nav active ──────────────────────────────── */
        .nav-item.active { background: rgba(255,255,255,.15); border-left: 3px solid #93c5fd; }
        .nav-item { transition: background .2s, border-color .2s; }
        .nav-item:hover:not(.active) { background: rgba(255,255,255,.08); }

        /* ── Table ───────────────────────────────────── */
        .tbl-head { background: linear-gradient(90deg, #1e40af 0%, #2563eb 100%); }
        .tbl-row:hover { background: #eff6ff; transition: background .15s; }

        /* ── Badge ───────────────────────────────────── */
        .badge-valid   { background:#dcfce7; color:#15803d; }
        .badge-pending { background:#fef9c3; color:#a16207; }
        .badge-reject  { background:#fee2e2; color:#b91c1c; }

        /* ── Stat Card ───────────────────────────────── */
        .stat-card { background: white; border-radius: 1rem; padding: 1.5rem;
                     box-shadow: 0 4px 20px rgba(30,64,175,.08);
                     transition: transform .25s, box-shadow .25s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(30,64,175,.14); }

        /* ── Input Focus Ring ────────────────────────── */
        .input-focus { transition: border-color .2s, box-shadow .2s; }
        .input-focus:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.18); outline: none; }

        /* ── Spinner ─────────────────────────────────── */
        .spinner { border: 3px solid #bfdbfe; border-top-color: #2563eb; border-radius: 50%;
                   width: 22px; height: 22px; animation: spin .75s linear infinite; display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Toast ───────────────────────────────────── */
        #toast { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
                 min-width: 280px; padding: 1rem 1.4rem; border-radius: .75rem;
                 font-weight: 600; font-size: .9rem; box-shadow: 0 8px 32px rgba(0,0,0,.18);
                 transform: translateY(80px); opacity: 0; transition: transform .35s ease, opacity .35s ease; }
        #toast.show { transform: translateY(0); opacity: 1; }

        /* ── Scrollbar ───────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f0f4ff; }
        ::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 4px; }

        /* ── Modal ───────────────────────────────────── */
        .modal-overlay { background: rgba(15,37,87,.45); backdrop-filter: blur(4px); }

        /* ── Responsive sidebar hide on mobile ───────── */
        @media (max-width: 768px) {
            .sidebar-wrap { position: fixed; inset: 0; z-index: 50; transform: translateX(-100%); transition: transform .3s ease; }
            .sidebar-wrap.open { transform: translateX(0); }
        }
    </style>
</head>

<body class="min-h-screen">

<!-- ██████████████████████████████████████████████████████████████
     TOAST NOTIFICATION
     ██████████████████████████████████████████████████████████████ -->
<div id="toast"></div>

<!-- ██████████████████████████████████████████████████████████████
     PAGE: LOGIN
     ██████████████████████████████████████████████████████████████ -->
<div id="page-login" class="page active">
    <div class="hero-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

        <!-- Decorative circles -->
        <div class="absolute top-[-80px] left-[-80px] w-96 h-96 rounded-full bg-white opacity-5"></div>
        <div class="absolute bottom-[-60px] right-[-60px] w-80 h-80 rounded-full bg-blue-300 opacity-10"></div>
        <div class="absolute top-1/2 right-10 w-48 h-48 rounded-full bg-blue-200 opacity-5"></div>

        <div class="glass rounded-2xl shadow-2xl w-full max-w-md p-10 animate-slide-up relative z-10">

            <!-- Logo + Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-primary-700 to-primary-500 mb-4 shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l6.16-3.422A12.083 12.083 0 0121 13c0 3.866-4.03 7-9 7s-9-3.134-9-7c0-.75.137-1.47.388-2.134L12 14z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-primary-900 tracking-tight">Portal Magang</h1>
                <p class="text-sm text-gray-500 mt-1">Sistem Informasi Pemagangan Universitas</p>
            </div>

            <!-- Key Input -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Masukkan Key Akses
                    </span>
                </label>
                <input id="loginKey"
                       type="text"
                       placeholder="Contoh: DOSEN001, MHS001, ADMIN999"
                       class="input-focus w-full px-4 py-3 rounded-xl border-2 border-gray-200 text-gray-800 text-sm bg-white/70"
                       autocomplete="off"
                       onkeydown="if(event.key==='Enter') doLogin()"/>
            </div>

            <!-- Hint pills -->
            <div class="flex flex-wrap gap-2 mb-6 text-xs">
                <span class="bg-primary-100 text-primary-700 px-3 py-1 rounded-full font-medium cursor-pointer hover:bg-primary-200 transition"
                      onclick="document.getElementById('loginKey').value='DOSEN001'">DOSEN001</span>
                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-medium cursor-pointer hover:bg-emerald-200 transition"
                      onclick="document.getElementById('loginKey').value='MHS001'">MHS001</span>
                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-medium cursor-pointer hover:bg-amber-200 transition"
                      onclick="document.getElementById('loginKey').value='ADMIN999'">ADMIN999</span>
            </div>

            <!-- Login Button -->
            <button onclick="doLogin()"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-primary-700 to-primary-500 hover:from-primary-800 hover:to-primary-600
                           text-white font-bold text-base shadow-lg hover:shadow-xl transition-all duration-300 active:scale-95 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Masuk ke Dashboard
            </button>

            <!-- Info -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700">
                <strong>ℹ️ Panduan Key:</strong><br>
                Awalan <code class="bg-blue-100 px-1 rounded">DOSEN</code> → Dashboard Dosen<br>
                Awalan <code class="bg-blue-100 px-1 rounded">MHS</code> → Dashboard Pemagang<br>
                Awalan <code class="bg-blue-100 px-1 rounded">ADMIN</code> → Dashboard Admin
            </div>

            <p class="text-center text-xs text-gray-400 mt-5">© <?= date('Y') ?> Portal Magang Universitas</p>
        </div>
    </div>
</div>

<!-- ██████████████████████████████████████████████████████████████
     DASHBOARD WRAPPER (shared layout)
     ██████████████████████████████████████████████████████████████ -->
<div id="page-dashboard" class="page">
    <div class="flex">

        <!-- ── Sidebar ──────────────────────────────────────── -->
        <div id="sidebarWrap" class="sidebar-wrap">
            <aside class="sidebar w-64 flex flex-col p-5 gap-2 shadow-2xl">

                <!-- Brand -->
                <div class="flex items-center gap-3 px-2 py-4 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 14l6.16-3.422A12.083 12.083 0 0121 13c0 3.866-4.03 7-9 7s-9-3.134-9-7c0-.75.137-1.47.388-2.134L12 14z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-white font-bold text-sm leading-tight">Portal Magang</div>
                        <div id="sidebarRole" class="text-blue-300 text-xs"></div>
                    </div>
                </div>

                <!-- Nav items rendered dynamically -->
                <nav id="sidebarNav" class="flex flex-col gap-1"></nav>

                <div class="flex-1"></div>

                <!-- User card -->
                <div class="bg-white/10 rounded-xl p-3 mt-2">
                    <div class="text-xs text-blue-200 mb-1">Masuk sebagai</div>
                    <div id="sidebarUser" class="text-white font-semibold text-sm truncate"></div>
                    <button onclick="doLogout()"
                            class="mt-3 w-full text-xs text-red-300 hover:text-red-200 hover:bg-red-500/20 rounded-lg py-1.5 transition font-medium flex items-center gap-1 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </div>
            </aside>
        </div>

        <!-- Sidebar Overlay (mobile) -->
        <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black/40 z-40 md:hidden"
             onclick="closeSidebar()"></div>

        <!-- ── Main Content ──────────────────────────────────── -->
        <main class="flex-1 min-h-screen md:ml-64 flex flex-col">

            <!-- Topbar -->
            <header class="bg-white/90 backdrop-blur-md border-b border-gray-200 px-5 py-4 flex items-center gap-3 sticky top-0 z-30 shadow-sm">
                <!-- Mobile hamburger -->
                <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"
                        onclick="openSidebar()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="flex-1">
                    <h2 id="topbarTitle" class="font-bold text-gray-800 text-lg leading-tight"></h2>
                    <p id="topbarSub"   class="text-gray-400 text-xs"></p>
                </div>
                <!-- Refresh -->
                <button onclick="refreshCurrentTab()"
                        class="flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:bg-primary-50 px-3 py-2 rounded-lg transition">
                    <svg id="refreshIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </header>

            <!-- Tab Content -->
            <div id="tabContent" class="flex-1 p-5 md:p-7 animate-fade-in"></div>

        </main>
    </div>
</div>

<!-- ██████████████████████████████████████████████████████████████
     MODAL (reusable)
     ██████████████████████████████████████████████████████████████ -->
<div id="modalOverlay" class="modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="modalBox" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg animate-slide-up overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 id="modalTitle" class="font-bold text-gray-800 text-lg"></h3>
            <button onclick="closeModal()"
                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="modalBody" class="px-6 py-5"></div>
    </div>
</div>

<!-- ██████████████████████████████████████████████████████████████
     JAVASCRIPT — CORE APPLICATION
     ██████████████████████████████████████████████████████████████ -->
<script>
'use strict';

/* ================================================================
   ⚙️  KONFIGURASI — GANTI URL INI DENGAN GOOGLE APPS SCRIPT ANDA
   ================================================================
   1. Buka Google Apps Script: https://script.google.com
   2. Buat project baru → paste kode Apps Script dari README.md
   3. Deploy → "New Deployment" → Type: "Web App"
   4. Execute as: "Me" | Who has access: "Anyone"
   5. Salin URL deployment → paste di bawah ini
   ================================================================ */
const scriptURL = 'https://script.google.com/macros/s/GANTI_DENGAN_URL_DEPLOYMENT_ANDA/exec';

/* ================================================================
   STATE
   ================================================================ */
const state = {
    user:    null,   // { key, role, name }
    tab:     null,
    loading: false,
    cache:   {},
};

/* ================================================================
   ROLE DEFINITIONS
   ================================================================ */
const roles = {
    DOSEN: {
        label: 'Dosen Pembimbing',
        color: 'text-blue-300',
        nav: [
            { id: 'buat-jadwal',  icon: '📅', label: 'Buat Jadwal'      },
            { id: 'daftar-tugas', icon: '📋', label: 'Daftar Tugas'     },
            { id: 'validasi',     icon: '✅', label: 'Validasi Laporan'  },
        ],
    },
    MHS: {
        label: 'Pemagang',
        color: 'text-emerald-300',
        nav: [
            { id: 'lihat-jadwal',  icon: '📅', label: 'Jadwal Magang'  },
            { id: 'pilih-jadwal',  icon: '🗓️', label: 'Pilih Waktu'    },
            { id: 'form-laporan',  icon: '📝', label: 'Laporan Magang'  },
        ],
    },
    ADMIN: {
        label: 'Administrator',
        color: 'text-amber-300',
        nav: [
            { id: 'rekap',     icon: '📊', label: 'Rekap Semua Aktivitas' },
            { id: 'jadwal-all',icon: '📅', label: 'Semua Jadwal'          },
            { id: 'users',     icon: '👥', label: 'Manajemen Key'          },
        ],
    },
};

/* ================================================================
   PAGE TRANSITION UTILITY
   ================================================================ */
function showPage(pageId) {
    const pages = document.querySelectorAll('.page');
    const target = document.getElementById('page-' + pageId);
    if (!target) return;

    pages.forEach(p => {
        if (p.classList.contains('active') && p !== target) {
            p.classList.add('leaving');
            setTimeout(() => { p.classList.remove('active', 'leaving'); p.style.display = 'none'; }, 260);
        }
    });

    setTimeout(() => {
        target.style.display = 'block';
        requestAnimationFrame(() => { target.classList.add('active'); });
    }, 80);
}

/* ================================================================
   TOAST NOTIFICATION
   ================================================================ */
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    const cfg = {
        success: { bg: '#dcfce7', color: '#15803d', icon: '✅' },
        error:   { bg: '#fee2e2', color: '#b91c1c', icon: '❌' },
        info:    { bg: '#dbeafe', color: '#1d4ed8', icon: 'ℹ️' },
        warn:    { bg: '#fef9c3', color: '#a16207', icon: '⚠️' },
    };
    const c = cfg[type] || cfg.info;
    t.style.background = c.bg;
    t.style.color = c.color;
    t.textContent = c.icon + '  ' + msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

/* ================================================================
   MODAL UTILITY
   ================================================================ */
function openModal(title, bodyHTML) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalBody').innerHTML = bodyHTML;
    document.getElementById('modalOverlay').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('modalOverlay').classList.add('hidden');
}
document.getElementById('modalOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('modalOverlay')) closeModal();
});

/* ================================================================
   SIDEBAR MOBILE
   ================================================================ */
function openSidebar() {
    document.getElementById('sidebarWrap').classList.add('open');
    document.getElementById('sidebarOverlay').classList.remove('hidden');
}
function closeSidebar() {
    document.getElementById('sidebarWrap').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.add('hidden');
}

/* ================================================================
   API — Google Apps Script Fetch Wrapper
   ================================================================ */

/**
 * GET data dari Google Sheets via Apps Script
 * @param {string} action - action parameter untuk GAS
 * @param {object} params - query params tambahan
 */
async function apiGet(action, params = {}) {
    const url = new URL(scriptURL);
    url.searchParams.set('action', action);
    Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

    const res = await fetch(url.toString(), { method: 'GET' });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

/**
 * POST data ke Google Sheets via Apps Script
 * @param {string} action - action parameter untuk GAS
 * @param {object} data   - payload (akan di-JSON.stringify)
 */
async function apiPost(action, data = {}) {
    const payload = JSON.stringify({ action, ...data });
    const res = await fetch(scriptURL, {
        method: 'POST',
        headers: { 'Content-Type': 'text/plain;charset=utf-8' },
        body: payload,
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

/* ================================================================
   LOGIN
   ================================================================ */
function doLogin() {
    const raw = document.getElementById('loginKey').value.trim().toUpperCase();
    if (!raw) { showToast('Masukkan Key Akses terlebih dahulu!', 'warn'); return; }

    let role = null;
    if (raw.startsWith('DOSEN')) role = 'DOSEN';
    else if (raw.startsWith('MHS')) role = 'MHS';
    else if (raw.startsWith('ADMIN')) role = 'ADMIN';

    if (!role) {
        showToast('Key tidak dikenali. Gunakan awalan DOSEN / MHS / ADMIN', 'error');
        document.getElementById('loginKey').classList.add('border-red-400');
        setTimeout(() => document.getElementById('loginKey').classList.remove('border-red-400'), 1500);
        return;
    }

    state.user = { key: raw, role, name: raw };
    sessionStorage.setItem('portalUser', JSON.stringify(state.user));
    bootDashboard();
    showToast('Selamat datang, ' + raw + '! 👋', 'success');
}

/* ================================================================
   LOGOUT
   ================================================================ */
function doLogout() {
    sessionStorage.removeItem('portalUser');
    state.user = null;
    state.tab  = null;
    state.cache = {};
    document.getElementById('loginKey').value = '';
    showPage('login');
    showToast('Berhasil keluar. Sampai jumpa!', 'info');
}

/* ================================================================
   BOOT DASHBOARD
   ================================================================ */
function bootDashboard() {
    const { role, key } = state.user;
    const def = roles[role];

    // Sidebar role label
    document.getElementById('sidebarRole').textContent = def.label;
    document.getElementById('sidebarUser').textContent = key;

    // Build sidebar nav
    const nav = document.getElementById('sidebarNav');
    nav.innerHTML = '';
    def.nav.forEach((item, i) => {
        const btn = document.createElement('button');
        btn.id = 'nav-' + item.id;
        btn.className = 'nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-blue-100 hover:text-white w-full text-left';
        btn.innerHTML = `<span class="text-base">${item.icon}</span><span>${item.label}</span>`;
        btn.onclick = () => loadTab(item.id);
        nav.appendChild(btn);
    });

    showPage('dashboard');
    loadTab(def.nav[0].id);
}

/* ================================================================
   LOAD TAB
   ================================================================ */
function loadTab(tabId) {
    state.tab = tabId;
    closeSidebar();

    // Update nav active state
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    const activeBtn = document.getElementById('nav-' + tabId);
    if (activeBtn) activeBtn.classList.add('active');

    // Render
    const handlers = {
        // DOSEN
        'buat-jadwal':  renderBuatJadwal,
        'daftar-tugas': renderDaftarTugas,
        'validasi':     renderValidasi,
        // MHS
        'lihat-jadwal': renderLihatJadwal,
        'pilih-jadwal': renderPilihJadwal,
        'form-laporan': renderFormLaporan,
        // ADMIN
        'rekap':        renderRekap,
        'jadwal-all':   renderJadwalAll,
        'users':        renderUsers,
    };

    const fn = handlers[tabId];
    if (fn) fn();
}

function refreshCurrentTab() {
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('animate-spin-slow');
    delete state.cache[state.tab];
    loadTab(state.tab);
    setTimeout(() => icon.classList.remove('animate-spin-slow'), 1000);
}

/* ================================================================
   HELPER — Set topbar
   ================================================================ */
function setTopbar(title, sub = '') {
    document.getElementById('topbarTitle').textContent = title;
    document.getElementById('topbarSub').textContent  = sub;
}

/* ================================================================
   HELPER — Loading skeleton
   ================================================================ */
function showSkeleton(lines = 4) {
    let html = '<div class="space-y-3 animate-pulse">';
    for (let i = 0; i < lines; i++) {
        const w = ['w-full','w-5/6','w-4/6','w-3/4'][i % 4];
        html += `<div class="h-5 bg-blue-100 rounded-lg ${w}"></div>`;
    }
    html += '</div>';
    document.getElementById('tabContent').innerHTML = `<div class="max-w-4xl mx-auto">${html}</div>`;
}

/* ================================================================
   HELPER — Empty state
   ================================================================ */
function emptyState(msg = 'Belum ada data.') {
    return `<div class="flex flex-col items-center justify-center py-16 text-gray-400">
        <svg class="w-16 h-16 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="font-medium text-sm">${msg}</p>
    </div>`;
}

/* ================================================================
   HELPER — Status badge
   ================================================================ */
function badge(status) {
    const map = {
        'Tervalidasi': '<span class="badge-valid px-2 py-0.5 rounded-full text-xs font-semibold">✅ Tervalidasi</span>',
        'Menunggu':    '<span class="badge-pending px-2 py-0.5 rounded-full text-xs font-semibold">🕐 Menunggu</span>',
        'Ditolak':     '<span class="badge-reject px-2 py-0.5 rounded-full text-xs font-semibold">❌ Ditolak</span>',
    };
    return map[status] || `<span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs font-semibold">${status||'-'}</span>`;
}

/* ================================================================
   HELPER — Input field HTML
   ================================================================ */
function inputHTML(id, label, type = 'text', placeholder = '', required = true, extra = '') {
    return `<div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">${label}${required ? ' <span class="text-red-500">*</span>' : ''}</label>
        <input id="${id}" type="${type}" placeholder="${placeholder}"
               class="input-focus w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-200 text-sm text-gray-800 bg-white" ${required ? 'required' : ''} ${extra}/>
    </div>`;
}

function textareaHTML(id, label, placeholder = '', required = true) {
    return `<div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">${label}${required ? ' <span class="text-red-500">*</span>' : ''}</label>
        <textarea id="${id}" rows="3" placeholder="${placeholder}"
                  class="input-focus w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-200 text-sm text-gray-800 bg-white resize-none" ${required ? 'required' : ''}></textarea>
    </div>`;
}

function selectHTML(id, label, options = [], required = true) {
    const opts = options.map(o => `<option value="${o.v}">${o.l}</option>`).join('');
    return `<div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">${label}${required ? ' <span class="text-red-500">*</span>' : ''}</label>
        <select id="${id}" class="input-focus w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-200 text-sm text-gray-800 bg-white" ${required ? 'required' : ''}>
            <option value="">-- Pilih --</option>${opts}
        </select>
    </div>`;
}

/* ================================================================
   HELPER — Form card wrapper
   ================================================================ */
function formCard(title, subtitle, formHTML, btnLabel, btnFn) {
    return `
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-primary-800 to-primary-600 px-6 py-4">
            <h3 class="text-white font-bold text-base">${title}</h3>
            <p class="text-blue-200 text-xs mt-0.5">${subtitle}</p>
        </div>
        <div class="p-6">
            ${formHTML}
            <button onclick="${btnFn}"
                    class="mt-2 w-full py-3 rounded-xl bg-gradient-to-r from-primary-700 to-primary-500 text-white font-bold text-sm
                           hover:from-primary-800 hover:to-primary-600 transition-all duration-200 active:scale-95 shadow flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                ${btnLabel}
            </button>
        </div>
    </div>`;
}

/* ================================================================
   HELPER — Table wrapper
   ================================================================ */
function tableCard(title, subtitle, theadHTML, tbodyHTML) {
    return `
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-base">${title}</h3>
                <p class="text-gray-400 text-xs mt-0.5">${subtitle}</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="tbl-head text-white text-xs uppercase tracking-wide">
                    <tr>${theadHTML}</tr>
                </thead>
                <tbody>${tbodyHTML || emptyState()}</tbody>
            </table>
        </div>
    </div>`;
}

function th(label) { return `<th class="px-4 py-3 text-left font-semibold">${label}</th>`; }
function td(val)   { return `<td class="px-4 py-3 text-gray-700">${val||'-'}</td>`; }

/* ================================================================
   ███████ DOSEN TABS ███████
   ================================================================ */

/* ── Buat Jadwal ────────────────────────────────────────────────── */
function renderBuatJadwal() {
    setTopbar('📅 Buat Jadwal Magang', 'Tambahkan jadwal & informasi tugas untuk pemagang');

    const form = [
        inputHTML('jdJudul',   'Judul Jadwal',   'text',     'Mis: Magang Semester Ganjil 2025'),
        inputHTML('jdTanggal', 'Tanggal Mulai',  'date',     '', true),
        inputHTML('jdSelesai', 'Tanggal Selesai','date',     '', true),
        selectHTML('jdRuang', 'Ruang Magang', [
            {v:'Lab Komputer A',l:'Lab Komputer A'},
            {v:'Lab Komputer B',l:'Lab Komputer B'},
            {v:'Ruang Rapat 1', l:'Ruang Rapat 1'},
            {v:'Ruang Rapat 2', l:'Ruang Rapat 2'},
            {v:'Aula Utama',    l:'Aula Utama'},
        ]),
        textareaHTML('jdTugas', 'Deskripsi Tugas', 'Deskripsikan tugas yang harus dikerjakan...'),
        inputHTML('jdKuota', 'Kuota Pemagang (orang)', 'number', '5', true, 'min="1" max="50"'),
    ].join('');

    document.getElementById('tabContent').innerHTML = `
    <div class="max-w-2xl mx-auto space-y-6 animate-slide-up">
        ${formCard('Formulir Jadwal Magang', 'Isi detail jadwal yang akan ditampilkan kepada pemagang', form, 'Simpan Jadwal', 'submitJadwal()')}
        <div id="jadwalPreview"></div>
    </div>`;
}

async function submitJadwal() {
    const judul   = v('jdJudul');
    const tanggal = v('jdTanggal');
    const selesai = v('jdSelesai');
    const ruang   = v('jdRuang');
    const tugas   = v('jdTugas');
    const kuota   = v('jdKuota');

    if (!judul || !tanggal || !selesai || !ruang || !tugas || !kuota) {
        showToast('Semua field wajib diisi!', 'warn'); return;
    }

    setBtnLoading(true);
    try {
        const res = await apiPost('buatJadwal', {
            judul, tanggal, selesai, ruang, tugas, kuota,
            dosenKey: state.user.key,
            timestamp: new Date().toISOString(),
        });
        if (res.status === 'ok') {
            showToast('Jadwal berhasil disimpan ke Google Sheets! 🎉', 'success');
            ['jdJudul','jdTanggal','jdSelesai','jdTugas','jdKuota'].forEach(id => { if(document.getElementById(id)) document.getElementById(id).value=''; });
            document.getElementById('jdRuang').value = '';
        } else {
            showToast('Gagal: ' + (res.message || 'Unknown error'), 'error');
        }
    } catch(e) {
        showToast('Koneksi gagal: ' + e.message, 'error');
    } finally {
        setBtnLoading(false);
    }
}

/* ── Daftar Tugas Pemagang ──────────────────────────────────────── */
async function renderDaftarTugas() {
    setTopbar('📋 Daftar Pekerjaan Pemagang', 'Laporan yang dikirim oleh para pemagang');
    showSkeleton(5);

    try {
        const res = await apiGet('getLaporan', { dosenKey: state.user.key });
        const rows = res.data || [];
        const tbody = rows.length ? rows.map(r => `
            <tr class="tbl-row border-b border-gray-50">
                ${td(r.timestamp ? new Date(r.timestamp).toLocaleDateString('id-ID') : '-')}
                ${td(r.mahasiswaKey)}
                ${td(r.ruangMagang)}
                ${td(`<span class="max-w-xs block truncate" title="${r.detailTugas}">${r.detailTugas}</span>`)}
                ${td(badge(r.status))}
                <td class="px-4 py-3">
                    ${r.status !== 'Tervalidasi' ? `<button onclick="validasiLaporan('${r.id}','Tervalidasi')"
                        class="text-xs bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg font-semibold transition mr-1">✅ Validasi</button>
                    <button onclick="validasiLaporan('${r.id}','Ditolak')"
                        class="text-xs bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg font-semibold transition">❌ Tolak</button>` : '<span class="text-gray-400 text-xs">Sudah divalidasi</span>'}
                </td>
            </tr>`).join('') : `<tr><td colspan="6">${emptyState('Belum ada laporan masuk')}</td></tr>`;

        document.getElementById('tabContent').innerHTML = `
        <div class="animate-slide-up">
            ${tableCard('Laporan Pemagang', `Total: ${rows.length} laporan`,
                [th('Tanggal'),th('Mahasiswa'),th('Ruang'),th('Tugas'),th('Status'),th('Aksi')].join(''),
                tbody
            )}
        </div>`;
    } catch(e) {
        renderError(e);
    }
}

/* ── Validasi ───────────────────────────────────────────────────── */
async function renderValidasi() {
    setTopbar('✅ Validasi Laporan', 'Validasi atau tolak laporan pemagang');
    // Reuse daftar tugas
    await renderDaftarTugas();
    setTopbar('✅ Validasi Laporan', 'Klik tombol Validasi / Tolak pada baris yang diinginkan');
}

async function validasiLaporan(id, status) {
    try {
        const res = await apiPost('validasiLaporan', { id, status, dosenKey: state.user.key });
        if (res.status === 'ok') {
            showToast(`Laporan berhasil di-${status === 'Tervalidasi' ? 'validasi' : 'tolak'}! `, 'success');
            delete state.cache['daftar-tugas'];
            loadTab('daftar-tugas');
        } else {
            showToast('Gagal: ' + (res.message || ''), 'error');
        }
    } catch(e) {
        showToast('Koneksi gagal: ' + e.message, 'error');
    }
}

/* ================================================================
   ███████ PEMAGANG TABS ███████
   ================================================================ */

/* ── Lihat Jadwal ───────────────────────────────────────────────── */
async function renderLihatJadwal() {
    setTopbar('📅 Jadwal Magang', 'Daftar jadwal yang tersedia dari dosen pembimbing');
    showSkeleton(4);
    try {
        const res = await apiGet('getJadwal');
        const rows = res.data || [];
        const cards = rows.length ? rows.map(r => `
            <div class="stat-card border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-gray-800 text-base">${r.judul}</h4>
                        <p class="text-xs text-gray-400 mt-0.5">oleh ${r.dosenKey}</p>
                    </div>
                    <span class="bg-primary-100 text-primary-700 text-xs font-bold px-3 py-1 rounded-full">Kuota: ${r.kuota}</span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs text-gray-600 mb-3">
                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="font-semibold text-gray-500 mb-0.5">📅 Mulai</div>
                        <div>${r.tanggal}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="font-semibold text-gray-500 mb-0.5">🏁 Selesai</div>
                        <div>${r.selesai}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="font-semibold text-gray-500 mb-0.5">🏢 Ruang</div>
                        <div>${r.ruang}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2 col-span-2">
                        <div class="font-semibold text-gray-500 mb-0.5">📝 Tugas</div>
                        <div class="line-clamp-2">${r.tugas}</div>
                    </div>
                </div>
            </div>`).join('') : emptyState('Belum ada jadwal dari dosen');

        document.getElementById('tabContent').innerHTML = `
        <div class="animate-slide-up">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">${cards}</div>
        </div>`;
    } catch(e) {
        renderError(e);
    }
}

/* ── Pilih Jadwal ───────────────────────────────────────────────── */
async function renderPilihJadwal() {
    setTopbar('🗓️ Pilih Waktu Magang', 'Pilih jadwal yang sesuai dengan ketersediaan Anda');
    showSkeleton(4);
    try {
        const res = await apiGet('getJadwal');
        const rows = res.data || [];

        const options = rows.map(r => `<option value="${r.id}">[${r.tanggal} s/d ${r.selesai}] ${r.judul} — ${r.ruang}</option>`).join('');

        const form = `
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih Jadwal <span class="text-red-500">*</span></label>
                <select id="pilihId" class="input-focus w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-200 text-sm text-gray-800 bg-white">
                    <option value="">-- Pilih Jadwal Tersedia --</option>${options}
                </select>
            </div>
            ${textareaHTML('pilihCatatan', 'Catatan / Alasan Memilih Jadwal Ini', 'Tuliskan alasan atau catatan Anda...', false)}
        `;

        document.getElementById('tabContent').innerHTML = `
        <div class="max-w-xl mx-auto animate-slide-up">
            ${formCard('Daftarkan Diri ke Jadwal Magang', 'Pilih jadwal yang tersedia dan kirimkan pendaftaran', form, 'Daftarkan Diri', 'submitPilihJadwal()')}
        </div>`;
    } catch(e) {
        renderError(e);
    }
}

async function submitPilihJadwal() {
    const jadwalId = v('pilihId');
    const catatan  = v('pilihCatatan');
    if (!jadwalId) { showToast('Pilih jadwal terlebih dahulu!', 'warn'); return; }

    setBtnLoading(true);
    try {
        const res = await apiPost('pilihJadwal', {
            jadwalId,
            catatan,
            mahasiswaKey: state.user.key,
            timestamp: new Date().toISOString(),
        });
        if (res.status === 'ok') {
            showToast('Berhasil mendaftar ke jadwal magang! 🎉', 'success');
        } else {
            showToast('Gagal: ' + (res.message || ''), 'error');
        }
    } catch(e) {
        showToast('Koneksi gagal: ' + e.message, 'error');
    } finally {
        setBtnLoading(false);
    }
}

/* ── Form Laporan ───────────────────────────────────────────────── */
function renderFormLaporan() {
    setTopbar('📝 Laporan Selesai Magang', 'Kirimkan laporan aktivitas magang Anda');

    const form = [
        selectHTML('lapRuang', 'Ruang Magang', [
            {v:'Lab Komputer A',l:'Lab Komputer A'},
            {v:'Lab Komputer B',l:'Lab Komputer B'},
            {v:'Ruang Rapat 1', l:'Ruang Rapat 1'},
            {v:'Ruang Rapat 2', l:'Ruang Rapat 2'},
            {v:'Aula Utama',    l:'Aula Utama'},
        ]),
        inputHTML('lapTanggal', 'Tanggal Magang', 'date', '', true),
        textareaHTML('lapDetail', 'Detail Tugas yang Dikerjakan',
            'Deskripsikan secara lengkap tugas yang Anda kerjakan hari ini...', true),
        inputHTML('lapDurasi', 'Durasi Kerja (jam)', 'number', 'Mis: 4', true, 'min="1" max="12"'),
        `<div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kendala yang Dihadapi</label>
            <textarea id="lapKendala" rows="2" placeholder="Tuliskan kendala jika ada (opsional)..."
                      class="input-focus w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-200 text-sm text-gray-800 bg-white resize-none"></textarea>
        </div>`,
    ].join('');

    document.getElementById('tabContent').innerHTML = `
    <div class="max-w-2xl mx-auto animate-slide-up">
        ${formCard('Form Laporan Magang Harian', 'Isi laporan aktivitas magang Anda dengan lengkap dan jujur', form, 'Kirim Laporan', 'submitLaporan()')}
    </div>`;
}

async function submitLaporan() {
    const ruang   = v('lapRuang');
    const tanggal = v('lapTanggal');
    const detail  = v('lapDetail');
    const durasi  = v('lapDurasi');
    const kendala = v('lapKendala');

    if (!ruang || !tanggal || !detail || !durasi) {
        showToast('Lengkapi semua field wajib!', 'warn'); return;
    }

    setBtnLoading(true);
    try {
        const res = await apiPost('kirimLaporan', {
            mahasiswaKey: state.user.key,
            ruangMagang: ruang,
            tanggal, detailTugas: detail, durasi, kendala,
            timestamp: new Date().toISOString(),
            status: 'Menunggu',
        });
        if (res.status === 'ok') {
            showToast('Laporan berhasil dikirim! Menunggu validasi dosen. 📨', 'success');
            ['lapRuang','lapTanggal','lapDetail','lapDurasi','lapKendala'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
        } else {
            showToast('Gagal: ' + (res.message || ''), 'error');
        }
    } catch(e) {
        showToast('Koneksi gagal: ' + e.message, 'error');
    } finally {
        setBtnLoading(false);
    }
}

/* ================================================================
   ███████ ADMIN TABS ███████
   ================================================================ */

/* ── Rekap Semua Aktivitas ──────────────────────────────────────── */
async function renderRekap() {
    setTopbar('📊 Rekap & Monitor Semua Aktivitas', 'Pantau seluruh kegiatan magang secara real-time');
    showSkeleton(6);
    try {
        const [resLap, resJad] = await Promise.all([
            apiGet('getAllLaporan'),
            apiGet('getJadwal'),
        ]);

        const laporan = resLap.data  || [];
        const jadwal  = resJad.data  || [];

        // Stat cards
        const totalValid = laporan.filter(l => l.status === 'Tervalidasi').length;
        const totalPending = laporan.filter(l => l.status === 'Menunggu').length;
        const totalTolak = laporan.filter(l => l.status === 'Ditolak').length;

        const stats = `
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            ${statCardHTML('Total Jadwal',  jadwal.length,   '📅', 'from-blue-500 to-blue-700')}
            ${statCardHTML('Total Laporan', laporan.length,  '📋', 'from-indigo-500 to-indigo-700')}
            ${statCardHTML('Tervalidasi',   totalValid,      '✅', 'from-emerald-500 to-emerald-700')}
            ${statCardHTML('Menunggu',      totalPending,    '🕐', 'from-amber-500 to-amber-700')}
        </div>`;

        // Table laporan
        const tbody = laporan.length ? laporan.map(r => `
            <tr class="tbl-row border-b border-gray-50">
                ${td(r.timestamp ? new Date(r.timestamp).toLocaleDateString('id-ID') : '-')}
                ${td(r.mahasiswaKey)}
                ${td(r.ruangMagang)}
                ${td(r.durasi ? r.durasi + ' jam' : '-')}
                ${td(`<span class="max-w-[200px] block truncate" title="${r.detailTugas}">${r.detailTugas}</span>`)}
                ${td(badge(r.status))}
                ${td(r.dosenKey || '-')}
            </tr>`).join('') : `<tr><td colspan="7">${emptyState('Belum ada data laporan')}</td></tr>`;

        document.getElementById('tabContent').innerHTML = `
        <div class="animate-slide-up space-y-6">
            ${stats}
            ${tableCard('Rekapitulasi Laporan Magang', `Menampilkan ${laporan.length} data laporan`,
                [th('Tanggal'),th('Pemagang'),th('Ruang'),th('Durasi'),th('Tugas'),th('Status'),th('Dosen')].join(''),
                tbody
            )}
        </div>`;
    } catch(e) {
        renderError(e);
    }
}

function statCardHTML(label, value, icon, gradient) {
    return `<div class="stat-card relative overflow-hidden">
        <div class="absolute top-0 right-0 w-20 h-20 rounded-full bg-gradient-to-br ${gradient} opacity-10 translate-x-4 -translate-y-4"></div>
        <div class="text-3xl mb-1">${icon}</div>
        <div class="text-3xl font-extrabold text-gray-800">${value}</div>
        <div class="text-sm text-gray-500 font-medium mt-0.5">${label}</div>
    </div>`;
}

/* ── Semua Jadwal (Admin) ───────────────────────────────────────── */
async function renderJadwalAll() {
    setTopbar('📅 Semua Jadwal Magang', 'Monitor seluruh jadwal yang dibuat oleh dosen');
    showSkeleton(4);
    try {
        const res = await apiGet('getJadwal');
        const rows = res.data || [];
        const tbody = rows.length ? rows.map(r => `
            <tr class="tbl-row border-b border-gray-50">
                ${td(r.judul)}
                ${td(r.dosenKey)}
                ${td(r.tanggal)}
                ${td(r.selesai)}
                ${td(r.ruang)}
                ${td(r.kuota + ' orang')}
                <td class="px-4 py-3">
                    <button onclick="lihatDetailJadwal(${JSON.stringify(r).replace(/"/g,'&quot;')})"
                            class="text-xs bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg font-semibold transition">
                        👁️ Detail
                    </button>
                    <button onclick="hapusJadwal('${r.id}')"
                            class="text-xs bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg font-semibold transition ml-1">
                        🗑️ Hapus
                    </button>
                </td>
            </tr>`).join('') : `<tr><td colspan="7">${emptyState('Belum ada jadwal')}</td></tr>`;

        document.getElementById('tabContent').innerHTML = `
        <div class="animate-slide-up">
            ${tableCard('Daftar Jadwal Magang', `Total: ${rows.length} jadwal`,
                [th('Judul'),th('Dosen'),th('Mulai'),th('Selesai'),th('Ruang'),th('Kuota'),th('Aksi')].join(''),
                tbody
            )}
        </div>`;
    } catch(e) {
        renderError(e);
    }
}

function lihatDetailJadwal(r) {
    openModal('Detail Jadwal: ' + r.judul, `
        <div class="space-y-3 text-sm">
            <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500 font-semibold">Judul:</span> <span class="text-gray-800">${r.judul}</span></div>
            <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500 font-semibold">Dosen:</span> <span class="text-gray-800">${r.dosenKey}</span></div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500 font-semibold">Mulai:</span><br><span class="text-gray-800">${r.tanggal}</span></div>
                <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500 font-semibold">Selesai:</span><br><span class="text-gray-800">${r.selesai}</span></div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500 font-semibold">Ruang:</span> <span class="text-gray-800">${r.ruang}</span></div>
            <div class="bg-gray-50 rounded-xl p-3"><span class="text-gray-500 font-semibold">Kuota:</span> <span class="text-gray-800">${r.kuota} orang</span></div>
            <div class="bg-blue-50 rounded-xl p-3"><span class="text-gray-500 font-semibold">Deskripsi Tugas:</span><br><span class="text-gray-800">${r.tugas}</span></div>
        </div>
    `);
}

async function hapusJadwal(id) {
    if (!confirm('Yakin ingin menghapus jadwal ini?')) return;
    try {
        const res = await apiPost('hapusJadwal', { id, adminKey: state.user.key });
        if (res.status === 'ok') {
            showToast('Jadwal berhasil dihapus!', 'success');
            loadTab('jadwal-all');
        } else {
            showToast('Gagal: ' + (res.message || ''), 'error');
        }
    } catch(e) {
        showToast('Koneksi gagal: ' + e.message, 'error');
    }
}

/* ── Manajemen Key (Admin) ──────────────────────────────────────── */
function renderUsers() {
    setTopbar('👥 Manajemen Key Akses', 'Panduan format key untuk setiap peran');
    document.getElementById('tabContent').innerHTML = `
    <div class="max-w-2xl mx-auto space-y-5 animate-slide-up">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 text-base mb-4">📋 Format Key Akses</h3>
            <div class="space-y-3">
                ${keyFormatCard('DOSEN', 'DOSEN + nomor unik', 'DOSEN001, DOSEN002', 'bg-primary-50 border-primary-200 text-primary-800', '🎓')}
                ${keyFormatCard('MHS',   'MHS + NIM/nomor',    'MHS12345, MHS001',   'bg-emerald-50 border-emerald-200 text-emerald-800', '🎒')}
                ${keyFormatCard('ADMIN', 'ADMIN + kode admin', 'ADMIN999, ADMIN001', 'bg-amber-50 border-amber-200 text-amber-800', '🔐')}
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-sm text-amber-800">
            <div class="font-bold mb-2">⚠️ Keamanan Key</div>
            <ul class="list-disc list-inside space-y-1 text-xs">
                <li>Bagikan key hanya kepada pengguna yang berwenang</li>
                <li>Key bersifat case-insensitive (otomatis diubah ke HURUF KAPITAL)</li>
                <li>Simpan daftar key di spreadsheet admin yang terpisah</li>
                <li>Ganti key secara berkala untuk keamanan</li>
            </ul>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 text-base mb-4">🔧 Konfigurasi Google Apps Script</h3>
            <div class="bg-gray-900 text-green-400 rounded-xl p-4 font-mono text-xs overflow-x-auto">
                <div class="text-gray-400">// Ganti URL berikut di index.php line ~218:</div>
                <div class="mt-1">const scriptURL = '<span class="text-yellow-300">YOUR_GOOGLE_APPS_SCRIPT_URL</span>';</div>
            </div>
            <p class="text-xs text-gray-500 mt-3">Lihat <code>README.md</code> untuk panduan lengkap setup Google Apps Script.</p>
        </div>
    </div>`;
}

function keyFormatCard(role, format, contoh, cls, icon) {
    return `<div class="border rounded-xl p-4 ${cls}">
        <div class="flex items-center gap-2 font-bold text-sm mb-1">${icon} Role: ${role}</div>
        <div class="text-xs opacity-80">Format: <code class="font-mono">${format}</code></div>
        <div class="text-xs opacity-80 mt-0.5">Contoh: <code class="font-mono">${contoh}</code></div>
    </div>`;
}

/* ================================================================
   ERROR STATE
   ================================================================ */
function renderError(e) {
    document.getElementById('tabContent').innerHTML = `
    <div class="max-w-lg mx-auto">
        <div class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center">
            <div class="text-5xl mb-3">⚠️</div>
            <h3 class="font-bold text-red-700 text-lg mb-2">Gagal Memuat Data</h3>
            <p class="text-red-600 text-sm mb-4">${e.message}</p>
            <div class="bg-red-100 rounded-xl p-3 text-xs text-red-700 text-left mb-4">
                <strong>Kemungkinan penyebab:</strong><br>
                • URL Google Apps Script belum diisi di <code>index.php</code><br>
                • Web App belum di-deploy dengan akses "Anyone"<br>
                • Koneksi internet bermasalah
            </div>
            <button onclick="loadTab(state.tab)"
                    class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition">
                🔄 Coba Lagi
            </button>
        </div>
    </div>`;
}

/* ================================================================
   UTILITY
   ================================================================ */
function v(id)  { const el = document.getElementById(id); return el ? el.value.trim() : ''; }

function setBtnLoading(loading) {
    const btns = document.querySelectorAll('#tabContent button[onclick*="submit"]');
    btns.forEach(btn => {
        if (loading) {
            btn._orig = btn.innerHTML;
            btn.innerHTML = '<span class="spinner"></span>&nbsp; Menyimpan...';
            btn.disabled = true;
        } else {
            if (btn._orig) btn.innerHTML = btn._orig;
            btn.disabled = false;
        }
    });
}

/* ================================================================
   SESSION RESTORE
   ================================================================ */
(function restoreSession() {
    const stored = sessionStorage.getItem('portalUser');
    if (stored) {
        try {
            state.user = JSON.parse(stored);
            bootDashboard();
        } catch { sessionStorage.removeItem('portalUser'); }
    }
})();
</script>

</body>
</html>
