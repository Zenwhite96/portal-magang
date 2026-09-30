<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Portal Magang — UNAS PASIM</title>

<!-- Google Fonts — sama dengan portal induk -->
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700&family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* ============================================================
   ROOT — Design Token UNAS PASIM
   ============================================================ */
:root {
    --bg:         #050914;
    --bg2:        #0a1020;
    --card-bg:    rgba(16,24,43,0.7);
    --sidebar-bg: linear-gradient(180deg,#06101f 0%,#0d1e3a 100%);
    --cyan:       #00f3ff;
    --purple:     #9d4edd;
    --green:      #00ff88;
    --gold:       #d4af37;
    --text:       #e0e6ed;
    --muted:      #8b9bb4;
    --border:     rgba(0,243,255,0.12);
    --border2:    rgba(255,255,255,0.06);
    --glow-c:     0 0 20px rgba(0,243,255,0.25);
    --glow-p:     0 0 20px rgba(157,78,221,0.25);
    --glow-g:     0 0 20px rgba(0,255,136,0.2);
}

/* ── Reset ── */
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{
    font-family:'Rajdhani',sans-serif;
    background:var(--bg);
    color:var(--text);
    min-height:100dvh;
    background-image:
        radial-gradient(circle at 15% 50%,rgba(0,243,255,0.04),transparent 30%),
        radial-gradient(circle at 85% 20%,rgba(157,78,221,0.04),transparent 30%);
}

/* ── Typography ── */
h1,h2,h3,h4{font-family:'Orbitron',sans-serif;}
p,label,span,td,th,input,select,textarea,button{font-family:'Rajdhani',sans-serif;}

/* ============================================================
   PAGE TRANSITION
   ============================================================ */
.page{display:none;opacity:0;transform:translateY(16px);transition:opacity .35s ease,transform .35s ease;}
.page.active{display:block;opacity:1;transform:translateY(0);}
.page.leaving{opacity:0;transform:translateY(-10px);transition:opacity .22s ease,transform .22s ease;}

/* ============================================================
   SCROLLBAR
   ============================================================ */
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:var(--bg2);}
::-webkit-scrollbar-thumb{background:rgba(0,243,255,0.3);border-radius:4px;}

/* ============================================================
   TOAST
   ============================================================ */
#toast{
    position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
    min-width:280px;padding:.9rem 1.3rem;border-radius:.75rem;
    font-weight:600;font-size:.95rem;
    box-shadow:0 8px 32px rgba(0,0,0,.5);
    transform:translateY(80px);opacity:0;
    transition:transform .35s ease,opacity .35s ease;
    border:1px solid rgba(255,255,255,0.08);
}
#toast.show{transform:translateY(0);opacity:1;}

/* ============================================================
   MODAL
   ============================================================ */
.modal-overlay{background:rgba(5,9,20,.75);backdrop-filter:blur(8px);}
#modalBox{
    background:#0d1e3a;
    border:1px solid var(--border);
    border-radius:1rem;
    box-shadow:var(--glow-c);
}
#modalTitle{color:var(--cyan);}

/* ============================================================
   ████  LOGIN PAGE  ████
   ============================================================ */
#page-login{
    min-height:100dvh;
    display:flex;align-items:center;justify-content:center;
    padding:1rem;
    background:var(--bg);
    background-image:
        radial-gradient(circle at 20% 40%,rgba(0,243,255,0.07),transparent 40%),
        radial-gradient(circle at 80% 60%,rgba(157,78,221,0.07),transparent 40%);
}
.login-card{
    background:rgba(13,30,58,0.85);
    backdrop-filter:blur(20px);
    border:1px solid var(--border);
    border-radius:1.25rem;
    padding:2.5rem 2rem;
    width:100%;max-width:440px;
    box-shadow:var(--glow-c),0 32px 64px rgba(0,0,0,.5);
    animation:slideUp .4s ease-out;
}
.login-logo{
    width:72px;height:72px;
    background:linear-gradient(135deg,rgba(0,243,255,.15),rgba(157,78,221,.15));
    border:1px solid var(--border);
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 1.2rem;
    font-size:2rem;
}
.login-title{
    text-align:center;
    font-size:1.5rem;letter-spacing:1px;
    background:linear-gradient(90deg,var(--cyan),var(--purple));
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    margin-bottom:.35rem;
}
.login-sub{text-align:center;color:var(--muted);font-size:.95rem;margin-bottom:1.8rem;}

.input-neon{
    width:100%;padding:.8rem 1rem;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(0,243,255,.25);
    border-radius:.65rem;color:var(--text);
    font-size:1rem;font-family:'Rajdhani',sans-serif;
    transition:border-color .25s,box-shadow .25s;
    outline:none;
}
.input-neon::placeholder{color:var(--muted);}
.input-neon:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,243,255,.12);}
.input-neon.error{border-color:#ff4d6d;box-shadow:0 0 0 3px rgba(255,77,109,.15);}

.btn-login{
    width:100%;padding:.85rem;margin-top:1rem;
    background:linear-gradient(135deg,rgba(0,243,255,.18),rgba(157,78,221,.18));
    border:1px solid var(--border);
    border-radius:.65rem;
    color:var(--cyan);font-family:'Orbitron',sans-serif;
    font-size:.85rem;font-weight:600;letter-spacing:1px;
    cursor:pointer;
    transition:all .25s;
}
.btn-login:hover{
    background:linear-gradient(135deg,rgba(0,243,255,.28),rgba(157,78,221,.28));
    box-shadow:var(--glow-c);transform:translateY(-2px);
}
.btn-login:active{transform:translateY(0);}

.key-pill{
    display:inline-flex;align-items:center;gap:.35rem;
    padding:.35rem .8rem;border-radius:2rem;
    font-size:.8rem;font-weight:600;cursor:pointer;
    border:1px solid;transition:all .2s;
    font-family:'Rajdhani',sans-serif;
}
.pill-cyan{color:var(--cyan);border-color:rgba(0,243,255,.3);background:rgba(0,243,255,.06);}
.pill-cyan:hover{background:rgba(0,243,255,.14);}
.pill-purple{color:var(--purple);border-color:rgba(157,78,221,.3);background:rgba(157,78,221,.06);}
.pill-purple:hover{background:rgba(157,78,221,.14);}
.pill-gold{color:var(--gold);border-color:rgba(212,175,55,.3);background:rgba(212,175,55,.06);}
.pill-gold:hover{background:rgba(212,175,55,.14);}

.info-box{
    background:rgba(0,243,255,.05);
    border:1px solid rgba(0,243,255,.15);
    border-radius:.65rem;padding:.85rem 1rem;
    font-size:.85rem;color:var(--muted);margin-top:1.2rem;
    line-height:1.6;
}
.info-box code{
    background:rgba(0,243,255,.1);color:var(--cyan);
    padding:.1rem .35rem;border-radius:.25rem;font-size:.8rem;
}

/* ============================================================
   ████  DASHBOARD LAYOUT  ████
   ============================================================ */
#page-dashboard{min-height:100dvh;}
.dashboard-wrap{display:flex;min-height:100dvh;}

/* ── Sidebar ── */
.sidebar{
    width:260px;min-height:100vh;
    background:var(--sidebar-bg);
    border-right:1px solid var(--border);
    display:flex;flex-direction:column;
    padding:1.25rem 1rem;gap:.35rem;
    position:sticky;top:0;height:100vh;overflow-y:auto;
}
.sidebar-brand{
    display:flex;align-items:center;gap:.75rem;
    padding:.75rem .5rem 1.25rem;
    border-bottom:1px solid var(--border2);margin-bottom:.5rem;
}
.sidebar-icon{
    width:40px;height:40px;border-radius:.65rem;
    background:rgba(0,243,255,.1);border:1px solid var(--border);
    display:flex;align-items:center;justify-content:center;
    color:var(--cyan);font-size:1.1rem;flex-shrink:0;
}
.sidebar-brand-text{line-height:1.2;}
.sidebar-brand-title{font-family:'Orbitron',sans-serif;font-size:.8rem;color:var(--cyan);}
.sidebar-brand-sub{font-size:.75rem;color:var(--muted);}

.nav-item{
    display:flex;align-items:center;gap:.75rem;
    padding:.7rem 1rem;border-radius:.65rem;
    color:var(--muted);font-size:.95rem;font-weight:500;
    cursor:pointer;border:none;background:none;width:100%;text-align:left;
    transition:all .2s;
}
.nav-item:hover{background:rgba(0,243,255,.07);color:var(--text);}
.nav-item.active{
    background:rgba(0,243,255,.12);
    color:var(--cyan);
    border-left:2px solid var(--cyan);
}
.nav-item .nav-icon{font-size:1rem;width:20px;text-align:center;}

.sidebar-user{
    margin-top:auto;padding-top:1rem;
    border-top:1px solid var(--border2);
}
.user-card{
    background:rgba(0,243,255,.05);
    border:1px solid var(--border);
    border-radius:.75rem;padding:.85rem 1rem;
}
.user-label{font-size:.7rem;color:var(--muted);margin-bottom:.2rem;}
.user-key{color:var(--cyan);font-weight:700;font-size:.95rem;font-family:'Orbitron',sans-serif;
    font-size:.75rem;word-break:break-all;}
.btn-logout{
    width:100%;margin-top:.7rem;padding:.55rem;
    background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.25);
    border-radius:.5rem;color:#ff4d6d;font-size:.8rem;font-weight:600;
    cursor:pointer;transition:all .2s;font-family:'Rajdhani',sans-serif;
}
.btn-logout:hover{background:rgba(255,77,109,.2);}

/* Sidebar overlay mobile */
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(5,9,20,.7);z-index:40;}
.sidebar-wrap-mobile{transition:transform .3s ease;}
@media(max-width:768px){
    .sidebar{
        position:fixed;inset:0 auto 0 0;z-index:50;
        transform:translateX(-100%);transition:transform .3s ease;
    }
    .sidebar.open{transform:translateX(0);}
    .sidebar-overlay.open{display:block;}
    .main-content{margin-left:0!important;}
}

/* ── Main Content ── */
.main-content{flex:1;display:flex;flex-direction:column;min-height:100vh;margin-left:260px;}
@media(max-width:768px){.main-content{margin-left:0;}}

/* ── Topbar ── */
.topbar{
    position:sticky;top:0;z-index:30;
    background:rgba(5,9,20,.9);backdrop-filter:blur(16px);
    border-bottom:1px solid var(--border2);
    padding:.9rem 1.5rem;
    display:flex;align-items:center;gap:.75rem;
}
.topbar-title{font-family:'Orbitron',sans-serif;font-size:.9rem;color:var(--cyan);}
.topbar-sub{font-size:.8rem;color:var(--muted);}
.btn-topbar{
    display:flex;align-items:center;gap:.4rem;
    padding:.5rem .9rem;border-radius:.5rem;
    background:rgba(0,243,255,.07);border:1px solid var(--border);
    color:var(--cyan);font-size:.8rem;font-weight:600;cursor:pointer;
    transition:all .2s;font-family:'Rajdhani',sans-serif;
}
.btn-topbar:hover{background:rgba(0,243,255,.14);}
.hamburger{
    padding:.5rem;border-radius:.5rem;background:none;border:1px solid var(--border2);
    color:var(--muted);cursor:pointer;display:none;transition:all .2s;
}
.hamburger:hover{border-color:var(--border);color:var(--text);}
@media(max-width:768px){.hamburger{display:flex;align-items:center;justify-content:center;}}

/* ── Tab Content Area ── */
.tab-content{flex:1;padding:1.5rem;animation:fadeIn .3s ease-out;}
@media(max-width:768px){.tab-content{padding:1rem;}}

/* ============================================================
   COMPONENTS
   ============================================================ */

/* ── Neon Card ── */
.neon-card{
    background:var(--card-bg);
    border:1px solid var(--border2);
    border-radius:1rem;overflow:hidden;
    backdrop-filter:blur(10px);
    transition:transform .25s,box-shadow .25s;
}
.neon-card:hover{transform:translateY(-3px);}
.card-header-cyan{
    background:linear-gradient(90deg,rgba(0,243,255,.12),rgba(0,243,255,.04));
    border-bottom:1px solid rgba(0,243,255,.15);
    padding:1rem 1.25rem;
}
.card-header-purple{
    background:linear-gradient(90deg,rgba(157,78,221,.12),rgba(157,78,221,.04));
    border-bottom:1px solid rgba(157,78,221,.15);
    padding:1rem 1.25rem;
}
.card-header-green{
    background:linear-gradient(90deg,rgba(0,255,136,.1),rgba(0,255,136,.03));
    border-bottom:1px solid rgba(0,255,136,.12);
    padding:1rem 1.25rem;
}
.card-header-gold{
    background:linear-gradient(90deg,rgba(212,175,55,.12),rgba(212,175,55,.04));
    border-bottom:1px solid rgba(212,175,55,.15);
    padding:1rem 1.25rem;
}
.card-h-title{font-family:'Orbitron',sans-serif;font-size:.85rem;letter-spacing:.5px;}
.card-h-sub{font-size:.8rem;color:var(--muted);margin-top:.2rem;}
.card-body{padding:1.25rem;}

/* ── Stat Card ── */
.stat-card{
    background:var(--card-bg);
    border:1px solid var(--border2);
    border-radius:.85rem;padding:1.25rem;
    position:relative;overflow:hidden;
    transition:transform .25s,box-shadow .25s;
}
.stat-card:hover{transform:translateY(-4px);}
.stat-card::before{
    content:'';position:absolute;top:-30px;right:-30px;
    width:100px;height:100px;border-radius:50%;
    opacity:.1;
}
.stat-cyan{border-top:2px solid var(--cyan);}
.stat-cyan::before{background:var(--cyan);}
.stat-purple{border-top:2px solid var(--purple);}
.stat-purple::before{background:var(--purple);}
.stat-green{border-top:2px solid var(--green);}
.stat-green::before{background:var(--green);}
.stat-gold{border-top:2px solid var(--gold);}
.stat-gold::before{background:var(--gold);}
.stat-icon{font-size:1.5rem;margin-bottom:.6rem;}
.stat-value{font-family:'Orbitron',sans-serif;font-size:2rem;font-weight:700;}
.stat-label{font-size:.85rem;color:var(--muted);margin-top:.2rem;}

/* ── Input Neon (form inside dashboard) ── */
.form-label{display:block;font-size:.85rem;font-weight:600;color:var(--muted);margin-bottom:.4rem;}
.form-label span.req{color:#ff4d6d;}
.form-input{
    width:100%;padding:.75rem 1rem;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.1);
    border-radius:.6rem;color:var(--text);
    font-size:.95rem;font-family:'Rajdhani',sans-serif;
    transition:border-color .2s,box-shadow .2s;outline:none;
}
.form-input:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,243,255,.1);}
.form-input::placeholder{color:rgba(139,155,180,.5);}
.form-group{margin-bottom:1rem;}

/* ── Neon Button ── */
.btn-neon-cyan{
    display:inline-flex;align-items:center;gap:.5rem;
    padding:.75rem 1.5rem;border-radius:.65rem;
    background:rgba(0,243,255,.1);border:1px solid rgba(0,243,255,.3);
    color:var(--cyan);font-weight:600;font-size:.9rem;cursor:pointer;
    font-family:'Rajdhani',sans-serif;transition:all .25s;
}
.btn-neon-cyan:hover{background:rgba(0,243,255,.2);box-shadow:var(--glow-c);}
.btn-neon-cyan:active{transform:scale(.97);}
.btn-neon-cyan:disabled{opacity:.5;cursor:not-allowed;}

.btn-neon-green{
    display:inline-flex;align-items:center;gap:.4rem;
    padding:.5rem 1rem;border-radius:.5rem;
    background:rgba(0,255,136,.1);border:1px solid rgba(0,255,136,.3);
    color:var(--green);font-weight:600;font-size:.8rem;cursor:pointer;
    font-family:'Rajdhani',sans-serif;transition:all .2s;
}
.btn-neon-green:hover{background:rgba(0,255,136,.2);}

.btn-neon-red{
    display:inline-flex;align-items:center;gap:.4rem;
    padding:.5rem 1rem;border-radius:.5rem;
    background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.3);
    color:#ff4d6d;font-weight:600;font-size:.8rem;cursor:pointer;
    font-family:'Rajdhani',sans-serif;transition:all .2s;
}
.btn-neon-red:hover{background:rgba(255,77,109,.2);}

.btn-neon-purple{
    display:inline-flex;align-items:center;gap:.4rem;
    padding:.5rem 1rem;border-radius:.5rem;
    background:rgba(157,78,221,.1);border:1px solid rgba(157,78,221,.3);
    color:var(--purple);font-weight:600;font-size:.8rem;cursor:pointer;
    font-family:'Rajdhani',sans-serif;transition:all .2s;
}
.btn-neon-purple:hover{background:rgba(157,78,221,.2);}

/* ── Table ── */
.neon-table{width:100%;border-collapse:collapse;font-size:.9rem;}
.neon-table thead tr{
    background:linear-gradient(90deg,rgba(0,243,255,.1),rgba(157,78,221,.08));
    border-bottom:1px solid rgba(0,243,255,.2);
}
.neon-table th{
    padding:.85rem 1rem;text-align:left;
    font-family:'Orbitron',sans-serif;font-size:.7rem;
    color:var(--cyan);letter-spacing:.5px;white-space:nowrap;
}
.neon-table td{
    padding:.8rem 1rem;border-bottom:1px solid var(--border2);
    color:var(--text);vertical-align:middle;
}
.neon-table tbody tr:hover{background:rgba(0,243,255,.04);}
.neon-table tbody tr:last-child td{border-bottom:none;}

/* ── Badges ── */
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .75rem;border-radius:2rem;font-size:.75rem;font-weight:600;}
.badge-valid{background:rgba(0,255,136,.1);color:var(--green);border:1px solid rgba(0,255,136,.25);}
.badge-pending{background:rgba(212,175,55,.1);color:var(--gold);border:1px solid rgba(212,175,55,.25);}
.badge-reject{background:rgba(255,77,109,.1);color:#ff4d6d;border:1px solid rgba(255,77,109,.25);}

/* ── Skeleton ── */
.skeleton{background:linear-gradient(90deg,rgba(255,255,255,.04) 25%,rgba(0,243,255,.06) 50%,rgba(255,255,255,.04) 75%);
    background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:.5rem;}
@keyframes shimmer{0%{background-position:200% 0;}100%{background-position:-200% 0;}}

/* ── Spinner ── */
.spinner{
    width:20px;height:20px;border-radius:50%;
    border:2px solid rgba(0,243,255,.2);border-top-color:var(--cyan);
    animation:spin .7s linear infinite;display:inline-block;
}
@keyframes spin{to{transform:rotate(360deg);}}

/* ── Empty State ── */
.empty-state{
    padding:3.5rem 1rem;text-align:center;
}
.empty-state i{font-size:2.5rem;color:rgba(139,155,180,.3);margin-bottom:1rem;display:block;}
.empty-state p{color:var(--muted);font-size:.95rem;}

/* ── Grid ── */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;}
@media(max-width:900px){.grid-4{grid-template-columns:1fr 1fr;}.grid-3{grid-template-columns:1fr 1fr;}}
@media(max-width:600px){.grid-2,.grid-3,.grid-4{grid-template-columns:1fr;}}

/* ── Schedule Card ── */
.jadwal-card{
    background:var(--card-bg);
    border:1px solid var(--border2);
    border-left:3px solid var(--cyan);
    border-radius:.85rem;padding:1.25rem;
    transition:all .25s;
}
.jadwal-card:hover{border-left-color:var(--purple);box-shadow:var(--glow-p);transform:translateY(-3px);}
.jadwal-meta{display:flex;flex-wrap:wrap;gap:.5rem;margin:1rem 0;}
.jadwal-meta-item{
    display:flex;align-items:center;gap:.35rem;
    background:rgba(255,255,255,.04);border:1px solid var(--border2);
    border-radius:.4rem;padding:.3rem .65rem;font-size:.8rem;color:var(--muted);
}
.jadwal-meta-item i{color:var(--cyan);font-size:.75rem;}

/* ── Animations ── */
@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
@keyframes slideUp{from{opacity:0;transform:translateY(24px);}to{opacity:1;transform:translateY(0);}}

/* ── Overflow table ── */
.table-wrap{overflow-x:auto;border-radius:.85rem;border:1px solid var(--border2);}

/* ── Error box ── */
.error-box{
    background:rgba(255,77,109,.07);border:1px solid rgba(255,77,109,.25);
    border-radius:.85rem;padding:2rem;text-align:center;
}
.error-box i{font-size:2.5rem;color:#ff4d6d;margin-bottom:1rem;display:block;}
.error-box h3{color:#ff4d6d;margin-bottom:.5rem;font-size:1rem;}
.error-box p{color:var(--muted);font-size:.85rem;margin-bottom:1rem;}
.error-detail{
    background:rgba(0,0,0,.3);border-radius:.5rem;padding:.75rem;
    text-align:left;font-size:.78rem;color:var(--muted);margin-bottom:1rem;
    line-height:1.6;
}

/* ── Alert ── */
.alert{display:flex;align-items:flex-start;gap:.75rem;padding:.85rem 1rem;border-radius:.65rem;font-size:.88rem;margin-bottom:1rem;}
.alert-warn{background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.2);color:var(--gold);}
.alert-info{background:rgba(0,243,255,.06);border:1px solid rgba(0,243,255,.15);color:var(--cyan);}
</style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════════
     TOAST
     ══════════════════════════════════════════════════════════ -->
<div id="toast"></div>

<!-- ══════════════════════════════════════════════════════════
     PAGE: LOGIN
     ══════════════════════════════════════════════════════════ -->
<div id="page-login" class="page active" style="display:flex;">
    <div class="login-card">
        <div class="login-logo">🎓</div>
        <h1 class="login-title">PORTAL MAGANG</h1>
        <p class="login-sub">Sistem Informasi Pemagangan · UNAS PASIM</p>

        <div class="form-group">
            <label class="form-label" style="color:var(--muted);">
                <i class="fa-solid fa-key" style="color:var(--cyan);margin-right:.35rem;"></i>
                Masukkan Key Akses
            </label>
            <input id="loginKey" class="input-neon"
                   type="text" placeholder="DOSEN001 · MHS001 · ADMIN999"
                   autocomplete="off"
                   onkeydown="if(event.key==='Enter')doLogin()"/>
        </div>

        <!-- Quick fill pills -->
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;">
            <span class="key-pill pill-cyan" onclick="fillKey('DOSEN001')"><i class="fa-solid fa-chalkboard-user"></i> DOSEN001</span>
            <span class="key-pill pill-purple" onclick="fillKey('MHS001')"><i class="fa-solid fa-user-graduate"></i> MHS001</span>
            <span class="key-pill pill-gold" onclick="fillKey('ADMIN999')"><i class="fa-solid fa-shield-halved"></i> ADMIN999</span>
        </div>

        <button class="btn-login" onclick="doLogin()">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>&nbsp;&nbsp;AKSES DASHBOARD
        </button>

        <div class="info-box">
            <i class="fa-solid fa-circle-info" style="color:var(--cyan);"></i>
            &nbsp;<strong style="color:var(--text);">Format Key:</strong><br>
            Awalan <code>DOSEN</code> → Dashboard Dosen<br>
            Awalan <code>MHS</code> → Dashboard Pemagang<br>
            Awalan <code>ADMIN</code> → Dashboard Admin
        </div>

        <p style="text-align:center;font-size:.72rem;color:rgba(139,155,180,.4);margin-top:1.25rem;">
            © <?= date('Y') ?> Portal Magang · UNAS PASIM · Sistem Terintegrasi
        </p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PAGE: DASHBOARD
     ══════════════════════════════════════════════════════════ -->
<div id="page-dashboard" class="page">
    <div class="dashboard-wrap">

        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="sidebar-brand-text">
                    <div class="sidebar-brand-title">PORTAL MAGANG</div>
                    <div id="sidebarRole" class="sidebar-brand-sub">—</div>
                </div>
            </div>
            <nav id="sidebarNav"></nav>
            <div class="sidebar-user">
                <div class="user-card">
                    <div class="user-label">Masuk sebagai</div>
                    <div id="sidebarUser" class="user-key">—</div>
                    <button class="btn-logout" onclick="doLogout()">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                    </button>
                </div>
            </div>
        </aside>

        <!-- Sidebar overlay (mobile) -->
        <div id="sidebarOverlay" class="sidebar-overlay" onclick="closeSidebar()"></div>

        <!-- Main -->
        <main class="main-content">
            <!-- Topbar -->
            <header class="topbar">
                <button class="hamburger" onclick="openSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div style="flex:1;">
                    <div id="topbarTitle" class="topbar-title">—</div>
                    <div id="topbarSub" class="topbar-sub"></div>
                </div>
                <button class="btn-topbar" onclick="refreshTab()">
                    <i id="refreshIcon" class="fa-solid fa-rotate-right"></i> Refresh
                </button>
                <a href="https://vcreed.my.id/universitas/" target="_blank"
                   style="display:flex;align-items:center;gap:.4rem;padding:.5rem .9rem;border-radius:.5rem;
                          background:rgba(157,78,221,.07);border:1px solid rgba(157,78,221,.25);
                          color:var(--purple);font-size:.8rem;font-weight:600;text-decoration:none;
                          font-family:'Rajdhani',sans-serif;transition:all .2s;"
                   onmouseover="this.style.background='rgba(157,78,221,.15)'"
                   onmouseout="this.style.background='rgba(157,78,221,.07)'">
                    <i class="fa-solid fa-house"></i> Portal
                </a>
            </header>

            <!-- Tab Content -->
            <div id="tabContent" class="tab-content"></div>
        </main>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL
     ══════════════════════════════════════════════════════════ -->
<div id="modalOverlay" class="modal-overlay" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;padding:1rem;">
    <div id="modalBox" style="width:100%;max-width:520px;animation:slideUp .3s ease-out;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border2);">
            <h3 id="modalTitle" style="font-size:.9rem;letter-spacing:.5px;"></h3>
            <button onclick="closeModal()" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.1rem;padding:.25rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div id="modalBody" style="padding:1.25rem;max-height:70vh;overflow-y:auto;"></div>
    </div>
</div>

<script>
'use strict';

/* ================================================================
   ⚙️  KONFIGURASI UTAMA
   ================================================================
   LANGKAH WAJIB:
   1. Buka https://script.google.com → Buat project → paste google-apps-script.js
   2. Deploy → Web App → Execute as: Me → Who has access: Anyone
   3. Salin URL deployment → paste di bawah ini (ganti seluruh string)
   ================================================================ */
const SCRIPT_URL = 'https://script.google.com/macros/s/AKfycbz5MK2jbf-SkPuO0sXYYGgCmCIvVdZpMGjCecmC46TnkIM8cEyr5Ha2i1v6v40ngE-UkQ/exec';

/* ================================================================
   STATE
   ================================================================ */
const S = {
    user:  null,  // { key, role, name }
    tab:   null,
    cache: {},
};

/* ================================================================
   ROLE CONFIG
   ================================================================ */
const ROLES = {
    DOSEN: {
        label: 'Dosen Pembimbing',
        color: 'var(--cyan)',
        nav: [
            { id:'buat-jadwal',  icon:'fa-calendar-plus',   label:'Buat Jadwal'      },
            { id:'daftar-tugas', icon:'fa-list-check',       label:'Daftar Laporan'   },
            { id:'validasi',     icon:'fa-circle-check',     label:'Validasi Laporan' },
        ],
    },
    MHS: {
        label: 'Mahasiswa Pemagang',
        color: 'var(--purple)',
        nav: [
            { id:'lihat-jadwal', icon:'fa-calendar-days',   label:'Jadwal Magang'   },
            { id:'pilih-jadwal', icon:'fa-calendar-check',  label:'Pilih Jadwal'    },
            { id:'form-laporan', icon:'fa-file-pen',         label:'Kirim Laporan'   },
            { id:'riwayat',      icon:'fa-clock-rotate-left',label:'Riwayat Saya'    },
        ],
    },
    ADMIN: {
        label: 'Administrator',
        color: 'var(--gold)',
        nav: [
            { id:'rekap',      icon:'fa-chart-bar',          label:'Rekap Aktivitas' },
            { id:'jadwal-all', icon:'fa-table-list',          label:'Semua Jadwal'   },
            { id:'info',       icon:'fa-circle-info',         label:'Info Sistem'    },
        ],
    },
};

/* ================================================================
   PAGE TRANSITION
   ================================================================ */
function showPage(id) {
    document.querySelectorAll('.page').forEach(p => {
        if (p.classList.contains('active') && p.id !== 'page-' + id) {
            p.classList.add('leaving');
            setTimeout(() => { p.classList.remove('active','leaving'); p.style.display = 'none'; }, 240);
        }
    });
    const tgt = document.getElementById('page-' + id);
    if (!tgt) return;
    setTimeout(() => {
        tgt.style.display = tgt.id === 'page-login' ? 'flex' : 'block';
        requestAnimationFrame(() => tgt.classList.add('active'));
    }, 60);
}

/* ================================================================
   TOAST
   ================================================================ */
function toast(msg, type='success') {
    const t = document.getElementById('toast');
    const cfg = {
        success: { bg:'rgba(0,255,136,.12)', color:'var(--green)', border:'rgba(0,255,136,.3)', icon:'✅' },
        error:   { bg:'rgba(255,77,109,.12)', color:'#ff4d6d',     border:'rgba(255,77,109,.3)', icon:'❌' },
        info:    { bg:'rgba(0,243,255,.1)',   color:'var(--cyan)', border:'var(--border)',        icon:'ℹ️' },
        warn:    { bg:'rgba(212,175,55,.1)',  color:'var(--gold)', border:'rgba(212,175,55,.3)', icon:'⚠️' },
    };
    const c = cfg[type] || cfg.info;
    Object.assign(t.style, { background:c.bg, color:c.color, borderColor:c.border });
    t.textContent = c.icon + '  ' + msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

/* ================================================================
   MODAL
   ================================================================ */
function openModal(title, html) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalBody').innerHTML = html;
    const el = document.getElementById('modalOverlay');
    el.style.display = 'flex';
}
function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
}
document.getElementById('modalOverlay').addEventListener('click', e => {
    if (e.target.id === 'modalOverlay') closeModal();
});

/* ================================================================
   SIDEBAR MOBILE
   ================================================================ */
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}

/* ================================================================
   API WRAPPER
   ================================================================ */
async function apiGet(action, params = {}) {
    const url = new URL(SCRIPT_URL);
    url.searchParams.set('action', action);
    Object.entries(params).forEach(([k,v]) => url.searchParams.set(k, v));
    const r = await fetch(url.toString(), { method:'GET' });
    if (!r.ok) throw new Error('Server error HTTP ' + r.status);
    return r.json();
}

async function apiPost(action, data = {}) {
    const r = await fetch(SCRIPT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'text/plain;charset=utf-8' },
        body: JSON.stringify({ action, ...data }),
    });
    if (!r.ok) throw new Error('Server error HTTP ' + r.status);
    return r.json();
}

/* ================================================================
   AUTH
   ================================================================ */
function fillKey(k) { document.getElementById('loginKey').value = k; }

function doLogin() {
    const raw = document.getElementById('loginKey').value.trim().toUpperCase();
    const inp = document.getElementById('loginKey');
    if (!raw) { inp.classList.add('error'); toast('Masukkan Key Akses!', 'warn'); setTimeout(()=>inp.classList.remove('error'),1500); return; }

    let role = null;
    if (raw.startsWith('DOSEN'))      role = 'DOSEN';
    else if (raw.startsWith('MHS'))   role = 'MHS';
    else if (raw.startsWith('ADMIN')) role = 'ADMIN';

    if (!role) {
        inp.classList.add('error');
        toast('Key tidak valid. Gunakan awalan DOSEN / MHS / ADMIN', 'error');
        setTimeout(()=>inp.classList.remove('error'),1500);
        return;
    }

    S.user = { key: raw, role };
    sessionStorage.setItem('pmUser', JSON.stringify(S.user));
    initDashboard();
    toast('Selamat datang, ' + raw + ' 👋', 'success');
}

function doLogout() {
    sessionStorage.removeItem('pmUser');
    S.user = null; S.tab = null; S.cache = {};
    document.getElementById('loginKey').value = '';
    showPage('login');
    toast('Berhasil keluar. Sampai jumpa!', 'info');
}

/* ================================================================
   INIT DASHBOARD
   ================================================================ */
function initDashboard() {
    const { role, key } = S.user;
    const def = ROLES[role];

    document.getElementById('sidebarRole').textContent = def.label;
    document.getElementById('sidebarUser').textContent = key;

    // Build nav
    const nav = document.getElementById('sidebarNav');
    nav.innerHTML = '';
    def.nav.forEach(item => {
        const btn = document.createElement('button');
        btn.id = 'nav-' + item.id;
        btn.className = 'nav-item';
        btn.innerHTML = `<i class="fa-solid ${item.icon} nav-icon"></i> ${item.label}`;
        btn.onclick = () => loadTab(item.id);
        nav.appendChild(btn);
    });

    showPage('dashboard');
    loadTab(def.nav[0].id);
}

/* ================================================================
   LOAD TAB
   ================================================================ */
const TAB_HANDLERS = {
    'buat-jadwal':  tabBuatJadwal,
    'daftar-tugas': tabDaftarTugas,
    'validasi':     tabDaftarTugas,   // reuse dengan mode validasi
    'lihat-jadwal': tabLihatJadwal,
    'pilih-jadwal': tabPilihJadwal,
    'form-laporan': tabFormLaporan,
    'riwayat':      tabRiwayat,
    'rekap':        tabRekap,
    'jadwal-all':   tabJadwalAll,
    'info':         tabInfo,
};

function loadTab(id) {
    S.tab = id;
    closeSidebar();
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('nav-' + id);
    if (btn) btn.classList.add('active');

    // Re-animate content
    const tc = document.getElementById('tabContent');
    tc.style.animation = 'none';
    requestAnimationFrame(() => { tc.style.animation = 'fadeIn .3s ease-out'; });

    const fn = TAB_HANDLERS[id];
    if (fn) fn();
}

function refreshTab() {
    const ri = document.getElementById('refreshIcon');
    ri.style.animation = 'spin .7s linear infinite';
    delete S.cache[S.tab];
    loadTab(S.tab);
    setTimeout(() => ri.style.animation = '', 900);
}

/* ================================================================
   TOPBAR HELPER
   ================================================================ */
function setTop(title, sub='') {
    document.getElementById('topbarTitle').textContent = title;
    document.getElementById('topbarSub').textContent = sub;
}

/* ================================================================
   SKELETON LOADING
   ================================================================ */
function skeleton(rows=4) {
    let h = '<div style="display:flex;flex-direction:column;gap:.75rem;max-width:800px;">';
    for(let i=0;i<rows;i++){
        const w = ['100%','80%','65%','90%'][i%4];
        h += `<div class="skeleton" style="height:18px;width:${w};"></div>`;
    }
    h += '</div>';
    document.getElementById('tabContent').innerHTML = `<div style="padding:.5rem;">${h}</div>`;
}

function emptyHTML(msg='Belum ada data.') {
    return `<div class="empty-state"><i class="fa-solid fa-folder-open"></i><p>${msg}</p></div>`;
}

/* ================================================================
   BADGE
   ================================================================ */
function mkBadge(s) {
    const m = {
        'Tervalidasi': `<span class="badge badge-valid"><i class="fa-solid fa-check"></i> Tervalidasi</span>`,
        'Menunggu':    `<span class="badge badge-pending"><i class="fa-regular fa-clock"></i> Menunggu</span>`,
        'Ditolak':     `<span class="badge badge-reject"><i class="fa-solid fa-xmark"></i> Ditolak</span>`,
    };
    return m[s] || `<span class="badge" style="background:rgba(255,255,255,.06);color:var(--muted);">${s||'-'}</span>`;
}

/* ================================================================
   GET VALUE
   ================================================================ */
function gv(id) { const e = document.getElementById(id); return e ? e.value.trim() : ''; }
function fmtDate(d) { try { return new Date(d).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}); } catch{ return d||'-'; } }

/* ================================================================
   ██ TAB: BUAT JADWAL (DOSEN)
   ================================================================ */
function tabBuatJadwal() {
    setTop('Buat Jadwal Magang', 'Tambahkan jadwal baru untuk para pemagang');
    document.getElementById('tabContent').innerHTML = `
    <div style="max-width:680px;display:flex;flex-direction:column;gap:1.25rem;">

        <div class="neon-card" style="animation:slideUp .35s ease-out;">
            <div class="card-header-cyan">
                <div class="card-h-title" style="color:var(--cyan);">
                    <i class="fa-solid fa-calendar-plus"></i> &nbsp;Formulir Jadwal Magang
                </div>
                <div class="card-h-sub">Isi semua field dengan benar sebelum menyimpan</div>
            </div>
            <div class="card-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Judul Jadwal <span class="req">*</span></label>
                        <input id="jJudul" class="form-input" placeholder="Mis: Magang Semester Ganjil 2025">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ruang Magang <span class="req">*</span></label>
                        <select id="jRuang" class="form-input">
                            <option value="">— Pilih Ruang —</option>
                            <option>Lab Komputer A</option>
                            <option>Lab Komputer B</option>
                            <option>Ruang Rapat 1</option>
                            <option>Ruang Rapat 2</option>
                            <option>Aula Utama</option>
                            <option>Perpustakaan</option>
                        </select>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai <span class="req">*</span></label>
                        <input id="jMulai" type="date" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Selesai <span class="req">*</span></label>
                        <input id="jSelesai" type="date" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Tugas <span class="req">*</span></label>
                    <textarea id="jTugas" class="form-input" rows="3" placeholder="Deskripsikan tugas yang harus dikerjakan pemagang..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Kuota Pemagang (orang) <span class="req">*</span></label>
                    <input id="jKuota" type="number" min="1" max="50" class="form-input" placeholder="Contoh: 5">
                </div>
                <button id="btnSimpanJadwal" class="btn-neon-cyan" style="width:100%;justify-content:center;padding:.85rem;" onclick="submitJadwal()">
                    <i class="fa-solid fa-floppy-disk"></i> &nbsp;Simpan Jadwal ke Google Sheets
                </button>
            </div>
        </div>

        <div class="alert alert-info" style="animation:slideUp .45s ease-out;">
            <i class="fa-solid fa-circle-info"></i>
            <span>Jadwal yang tersimpan akan langsung muncul di dashboard pemagang untuk dipilih.</span>
        </div>
    </div>`;
}

async function submitJadwal() {
    const judul   = gv('jJudul');
    const ruang   = gv('jRuang');
    const mulai   = gv('jMulai');
    const selesai = gv('jSelesai');
    const tugas   = gv('jTugas');
    const kuota   = gv('jKuota');

    if (!judul||!ruang||!mulai||!selesai||!tugas||!kuota) {
        toast('Semua field wajib diisi!', 'warn'); return;
    }
    if (new Date(mulai) > new Date(selesai)) {
        toast('Tanggal selesai harus setelah tanggal mulai!', 'warn'); return;
    }

    setLoading('btnSimpanJadwal', true, 'Menyimpan...');
    try {
        const res = await apiPost('buatJadwal', {
            dosenKey: S.user.key, judul, ruang,
            tanggal: mulai, selesai, tugas, kuota,
            timestamp: new Date().toISOString(),
        });
        if (res.status === 'ok') {
            toast('Jadwal berhasil disimpan! 🎉', 'success');
            ['jJudul','jRuang','jMulai','jSelesai','jTugas','jKuota'].forEach(id => {
                const el = document.getElementById(id); if(el) el.value='';
            });
        } else {
            toast('Gagal: ' + (res.message||'unknown'), 'error');
        }
    } catch(e) { toast('Koneksi gagal: ' + e.message, 'error'); }
    finally { setLoading('btnSimpanJadwal', false, '<i class="fa-solid fa-floppy-disk"></i> &nbsp;Simpan Jadwal ke Google Sheets'); }
}

/* ================================================================
   ██ TAB: DAFTAR TUGAS + VALIDASI (DOSEN)
   ================================================================ */
async function tabDaftarTugas() {
    const isValidasi = S.tab === 'validasi';
    setTop(
        isValidasi ? 'Validasi Laporan' : 'Daftar Laporan Pemagang',
        isValidasi ? 'Approve atau tolak laporan yang masuk' : 'Semua laporan yang dikirim pemagang'
    );
    skeleton(5);
    try {
        const res = await apiGet('getLaporan', { dosenKey: S.user.key });
        const rows = res.data || [];

        const tbody = rows.length ? rows.map(r => `
            <tr>
                <td>${fmtDate(r.tanggal||r.timestamp)}</td>
                <td><span style="color:var(--cyan);font-family:'Orbitron',sans-serif;font-size:.75rem;">${r.mahasiswaKey||'-'}</span></td>
                <td>${r.ruangMagang||'-'}</td>
                <td style="max-width:200px;"><span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${r.detailTugas||''}">${r.detailTugas||'-'}</span></td>
                <td>${r.durasi ? r.durasi+' jam' : '-'}</td>
                <td>${mkBadge(r.status)}</td>
                <td>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        ${r.status !== 'Tervalidasi' ? `
                        <button class="btn-neon-green" onclick="aksiValidasi('${r.id}','Tervalidasi')">
                            <i class="fa-solid fa-check"></i> Setuju
                        </button>
                        <button class="btn-neon-red" onclick="aksiValidasi('${r.id}','Ditolak')">
                            <i class="fa-solid fa-xmark"></i> Tolak
                        </button>` : `<span style="color:var(--muted);font-size:.8rem;">Selesai</span>`}
                        <button class="btn-neon-purple" onclick="lihatLaporan(${JSON.stringify(r).replace(/"/g,'&quot;')})">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>`).join('') : `<tr><td colspan="7">${emptyHTML('Belum ada laporan masuk')}</td></tr>`;

        document.getElementById('tabContent').innerHTML = `
        <div style="animation:slideUp .35s ease-out;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;">
                <span style="font-family:'Orbitron',sans-serif;font-size:.8rem;color:var(--muted);">
                    Total: <span style="color:var(--cyan);">${rows.length}</span> laporan
                </span>
                <span style="font-family:'Orbitron',sans-serif;font-size:.75rem;color:var(--muted);">
                    Menunggu: <span style="color:var(--gold);">${rows.filter(r=>r.status==='Menunggu').length}</span>
                    &nbsp;|&nbsp; Tervalidasi: <span style="color:var(--green);">${rows.filter(r=>r.status==='Tervalidasi').length}</span>
                </span>
            </div>
            <div class="table-wrap">
                <table class="neon-table">
                    <thead><tr>
                        <th>Tanggal</th><th>Pemagang</th><th>Ruang</th>
                        <th>Tugas</th><th>Durasi</th><th>Status</th><th>Aksi</th>
                    </tr></thead>
                    <tbody>${tbody}</tbody>
                </table>
            </div>
        </div>`;
    } catch(e) { renderErr(e); }
}

async function aksiValidasi(id, status) {
    try {
        const res = await apiPost('validasiLaporan', { id, status, dosenKey: S.user.key });
        if (res.status === 'ok') {
            toast(status === 'Tervalidasi' ? 'Laporan disetujui ✅' : 'Laporan ditolak ❌', status === 'Tervalidasi' ? 'success' : 'warn');
            delete S.cache[S.tab];
            loadTab(S.tab);
        } else { toast('Gagal: ' + res.message, 'error'); }
    } catch(e) { toast('Koneksi gagal', 'error'); }
}

function lihatLaporan(r) {
    openModal('Detail Laporan — ' + (r.mahasiswaKey||''), `
        <div style="display:flex;flex-direction:column;gap:.75rem;font-size:.9rem;">
            ${detailRow('Pemagang', r.mahasiswaKey, 'var(--cyan)')}
            ${detailRow('Tanggal', fmtDate(r.tanggal))}
            ${detailRow('Ruang', r.ruangMagang)}
            ${detailRow('Durasi', (r.durasi||'-') + ' jam')}
            ${detailRow('Status', r.status)}
            <div style="background:rgba(0,243,255,.05);border:1px solid var(--border);border-radius:.65rem;padding:.85rem;">
                <div style="font-size:.75rem;color:var(--muted);margin-bottom:.4rem;">Detail Tugas</div>
                <div>${r.detailTugas||'-'}</div>
            </div>
            ${r.kendala ? `<div style="background:rgba(212,175,55,.05);border:1px solid rgba(212,175,55,.2);border-radius:.65rem;padding:.85rem;">
                <div style="font-size:.75rem;color:var(--gold);margin-bottom:.4rem;">Kendala</div>
                <div>${r.kendala}</div>
            </div>` : ''}
        </div>`);
}

function detailRow(label, val, color='var(--text)') {
    return `<div style="display:flex;justify-content:space-between;padding:.5rem .75rem;background:rgba(255,255,255,.03);border-radius:.5rem;">
        <span style="color:var(--muted);font-size:.82rem;">${label}</span>
        <span style="color:${color};font-weight:600;">${val||'-'}</span>
    </div>`;
}

/* ================================================================
   ██ TAB: LIHAT JADWAL (MHS)
   ================================================================ */
async function tabLihatJadwal() {
    setTop('Jadwal Magang', 'Daftar jadwal yang tersedia dari dosen pembimbing');
    skeleton(4);
    try {
        const res = await apiGet('getJadwal');
        const rows = res.data || [];
        const cards = rows.length ? rows.map(r => `
            <div class="jadwal-card" style="animation:slideUp .35s ease-out;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;flex-wrap:wrap;">
                    <div>
                        <div style="font-family:'Orbitron',sans-serif;font-size:.9rem;color:var(--text);margin-bottom:.3rem;">${r.judul||'—'}</div>
                        <div style="font-size:.8rem;color:var(--muted);">oleh <span style="color:var(--cyan);">${r.dosenKey||'—'}</span></div>
                    </div>
                    <span style="background:rgba(0,243,255,.1);border:1px solid rgba(0,243,255,.25);color:var(--cyan);
                                 padding:.3rem .75rem;border-radius:2rem;font-size:.75rem;font-weight:600;white-space:nowrap;">
                        Kuota: ${r.kuota||'—'}
                    </span>
                </div>
                <div class="jadwal-meta">
                    <div class="jadwal-meta-item"><i class="fa-solid fa-calendar-day"></i> Mulai: ${fmtDate(r.tanggal)}</div>
                    <div class="jadwal-meta-item"><i class="fa-solid fa-calendar-check"></i> Selesai: ${fmtDate(r.selesai)}</div>
                    <div class="jadwal-meta-item"><i class="fa-solid fa-door-open"></i> ${r.ruang||'—'}</div>
                </div>
                <div style="background:rgba(0,243,255,.04);border:1px solid var(--border2);border-radius:.5rem;padding:.75rem;font-size:.85rem;color:var(--muted);line-height:1.5;">
                    <i class="fa-solid fa-file-lines" style="color:var(--cyan);margin-right:.35rem;"></i>
                    ${r.tugas||'—'}
                </div>
                <div style="margin-top:1rem;">
                    <button class="btn-neon-cyan" onclick="S.user&&(document.getElementById('nav-pilih-jadwal')&&document.getElementById('nav-pilih-jadwal').click())">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Daftar ke Jadwal Ini
                    </button>
                </div>
            </div>`).join('') : emptyHTML('Belum ada jadwal dari dosen');

        document.getElementById('tabContent').innerHTML = `
        <div style="display:flex;flex-direction:column;gap:1rem;">${cards}</div>`;
    } catch(e) { renderErr(e); }
}

/* ================================================================
   ██ TAB: PILIH JADWAL (MHS)
   ================================================================ */
async function tabPilihJadwal() {
    setTop('Pilih Jadwal Magang', 'Daftarkan diri ke jadwal yang tersedia');
    skeleton(3);
    try {
        const res = await apiGet('getJadwal');
        const rows = res.data || [];
        const opts = rows.map(r =>
            `<option value="${r.id}">[${fmtDate(r.tanggal)} → ${fmtDate(r.selesai)}] ${r.judul} | ${r.ruang}</option>`
        ).join('');

        document.getElementById('tabContent').innerHTML = `
        <div style="max-width:600px;animation:slideUp .35s ease-out;">
            <div class="neon-card">
                <div class="card-header-purple">
                    <div class="card-h-title" style="color:var(--purple);">
                        <i class="fa-solid fa-calendar-check"></i> &nbsp;Pendaftaran Jadwal
                    </div>
                    <div class="card-h-sub">Pilih jadwal yang sesuai jadwal Anda</div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Pilih Jadwal <span class="req">*</span></label>
                        <select id="pJadwal" class="form-input">
                            <option value="">— Pilih Jadwal Tersedia —</option>
                            ${opts||'<option disabled>Belum ada jadwal</option>'}
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Catatan / Alasan (opsional)</label>
                        <textarea id="pCatatan" class="form-input" rows="3" placeholder="Tuliskan catatan jika perlu..."></textarea>
                    </div>
                    <button id="btnPilih" class="btn-neon-cyan" style="width:100%;justify-content:center;padding:.85rem;" onclick="submitPilihJadwal()">
                        <i class="fa-solid fa-paper-plane"></i> &nbsp;Kirim Pendaftaran
                    </button>
                </div>
            </div>
        </div>`;
    } catch(e) { renderErr(e); }
}

async function submitPilihJadwal() {
    const jadwalId = gv('pJadwal');
    const catatan  = gv('pCatatan');
    if (!jadwalId) { toast('Pilih jadwal terlebih dahulu!', 'warn'); return; }
    setLoading('btnPilih', true, 'Mengirim...');
    try {
        const res = await apiPost('pilihJadwal', {
            mahasiswaKey: S.user.key, jadwalId, catatan,
            timestamp: new Date().toISOString(),
        });
        if (res.status === 'ok') {
            toast('Berhasil mendaftar! Tunggu konfirmasi dosen. 🎉', 'success');
            document.getElementById('pJadwal').value = '';
            document.getElementById('pCatatan').value = '';
        } else { toast('Gagal: ' + (res.message||''), 'error'); }
    } catch(e) { toast('Koneksi gagal: ' + e.message, 'error'); }
    finally { setLoading('btnPilih', false, '<i class="fa-solid fa-paper-plane"></i> &nbsp;Kirim Pendaftaran'); }
}

/* ================================================================
   ██ TAB: FORM LAPORAN (MHS)
   ================================================================ */
function tabFormLaporan() {
    setTop('Kirim Laporan Magang', 'Laporan harian aktivitas magang Anda');
    document.getElementById('tabContent').innerHTML = `
    <div style="max-width:680px;animation:slideUp .35s ease-out;">
        <div class="neon-card">
            <div class="card-header-green">
                <div class="card-h-title" style="color:var(--green);">
                    <i class="fa-solid fa-file-pen"></i> &nbsp;Form Laporan Magang Harian
                </div>
                <div class="card-h-sub">Isi laporan dengan jujur dan lengkap</div>
            </div>
            <div class="card-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Ruang Magang <span class="req">*</span></label>
                        <select id="lRuang" class="form-input">
                            <option value="">— Pilih Ruang —</option>
                            <option>Lab Komputer A</option>
                            <option>Lab Komputer B</option>
                            <option>Ruang Rapat 1</option>
                            <option>Ruang Rapat 2</option>
                            <option>Aula Utama</option>
                            <option>Perpustakaan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Magang <span class="req">*</span></label>
                        <input id="lTanggal" type="date" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Detail Tugas yang Dikerjakan <span class="req">*</span></label>
                    <textarea id="lDetail" class="form-input" rows="4"
                        placeholder="Deskripsikan secara detail tugas yang Anda kerjakan hari ini..."></textarea>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Durasi Kerja (jam) <span class="req">*</span></label>
                        <input id="lDurasi" type="number" min="1" max="12" class="form-input" placeholder="Contoh: 4">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kendala (opsional)</label>
                        <input id="lKendala" class="form-input" placeholder="Kendala yang dihadapi...">
                    </div>
                </div>
                <button id="btnLaporan" class="btn-neon-cyan"
                        style="width:100%;justify-content:center;padding:.85rem;border-color:rgba(0,255,136,.3);color:var(--green);background:rgba(0,255,136,.08);"
                        onmouseover="this.style.background='rgba(0,255,136,.16)'"
                        onmouseout="this.style.background='rgba(0,255,136,.08)'"
                        onclick="submitLaporan()">
                    <i class="fa-solid fa-paper-plane"></i> &nbsp;Kirim Laporan
                </button>
            </div>
        </div>
    </div>`;
}

async function submitLaporan() {
    const ruang   = gv('lRuang');
    const tanggal = gv('lTanggal');
    const detail  = gv('lDetail');
    const durasi  = gv('lDurasi');
    const kendala = gv('lKendala');

    if (!ruang||!tanggal||!detail||!durasi) { toast('Lengkapi semua field wajib!','warn'); return; }

    setLoading('btnLaporan', true, 'Mengirim...');
    try {
        const res = await apiPost('kirimLaporan', {
            mahasiswaKey: S.user.key,
            ruangMagang: ruang, tanggal, detailTugas: detail,
            durasi, kendala, status: 'Menunggu',
            timestamp: new Date().toISOString(),
        });
        if (res.status === 'ok') {
            toast('Laporan berhasil dikirim! Menunggu validasi dosen. 📨', 'success');
            ['lRuang','lTanggal','lDetail','lDurasi','lKendala'].forEach(id => { const e=document.getElementById(id);if(e)e.value=''; });
        } else { toast('Gagal: '+(res.message||''), 'error'); }
    } catch(e) { toast('Koneksi gagal: '+e.message, 'error'); }
    finally {
        setLoading('btnLaporan',false,'<i class="fa-solid fa-paper-plane"></i> &nbsp;Kirim Laporan');
    }
}

/* ================================================================
   ██ TAB: RIWAYAT (MHS)
   ================================================================ */
async function tabRiwayat() {
    setTop('Riwayat Laporan Saya', 'Semua laporan yang pernah Anda kirimkan');
    skeleton(4);
    try {
        const res = await apiGet('getLaporanByMhs', { mahasiswaKey: S.user.key });
        const rows = (res.data||[]).filter(r => r.mahasiswaKey === S.user.key);

        const tbody = rows.length ? rows.map(r => `
            <tr>
                <td>${fmtDate(r.tanggal)}</td>
                <td>${r.ruangMagang||'-'}</td>
                <td><span style="display:block;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.detailTugas||'-'}</span></td>
                <td>${r.durasi||'-'} jam</td>
                <td>${mkBadge(r.status)}</td>
                <td>${r.dosenKey||'—'}</td>
            </tr>`).join('') : `<tr><td colspan="6">${emptyHTML('Belum ada laporan')}</td></tr>`;

        document.getElementById('tabContent').innerHTML = `
        <div style="animation:slideUp .35s ease-out;">
            <div class="table-wrap">
                <table class="neon-table">
                    <thead><tr>
                        <th>Tanggal</th><th>Ruang</th><th>Tugas</th>
                        <th>Durasi</th><th>Status</th><th>Dosen</th>
                    </tr></thead>
                    <tbody>${tbody}</tbody>
                </table>
            </div>
        </div>`;
    } catch(e) { renderErr(e); }
}

/* ================================================================
   ██ TAB: REKAP (ADMIN)
   ================================================================ */
async function tabRekap() {
    setTop('Rekap & Monitor Aktivitas', 'Pantau seluruh kegiatan magang secara real-time');
    skeleton(6);
    try {
        const [resL, resJ] = await Promise.all([
            apiGet('getAllLaporan'),
            apiGet('getJadwal'),
        ]);
        const lap = resL.data || [];
        const jad = resJ.data || [];

        const valid   = lap.filter(r=>r.status==='Tervalidasi').length;
        const pending = lap.filter(r=>r.status==='Menunggu').length;
        const tolak   = lap.filter(r=>r.status==='Ditolak').length;

        const tbody = lap.length ? lap.map(r => `
            <tr>
                <td>${fmtDate(r.tanggal||r.timestamp)}</td>
                <td style="color:var(--cyan);font-family:'Orbitron',sans-serif;font-size:.72rem;">${r.mahasiswaKey||'-'}</td>
                <td>${r.ruangMagang||'-'}</td>
                <td>${r.durasi ? r.durasi+' jam' : '-'}</td>
                <td><span style="display:block;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.detailTugas||'-'}</span></td>
                <td>${mkBadge(r.status)}</td>
                <td style="font-size:.78rem;color:var(--muted);">${r.dosenKey||'—'}</td>
            </tr>`).join('') : `<tr><td colspan="7">${emptyHTML('Belum ada data laporan')}</td></tr>`;

        document.getElementById('tabContent').innerHTML = `
        <div style="display:flex;flex-direction:column;gap:1.25rem;animation:slideUp .35s ease-out;">

            <!-- Stat Cards -->
            <div class="grid-4">
                ${mkStat('Jadwal Aktif',  jad.length, 'fa-calendar',      'stat-cyan',   'var(--cyan)')}
                ${mkStat('Total Laporan', lap.length, 'fa-file-lines',    'stat-purple', 'var(--purple)')}
                ${mkStat('Tervalidasi',   valid,      'fa-circle-check',  'stat-green',  'var(--green)')}
                ${mkStat('Menunggu',      pending,    'fa-clock',         'stat-gold',   'var(--gold)')}
            </div>

            <!-- Table -->
            <div class="neon-card">
                <div class="card-header-cyan">
                    <div class="card-h-title" style="color:var(--cyan);">
                        <i class="fa-solid fa-table"></i> &nbsp;Rekapitulasi Laporan Magang
                    </div>
                    <div class="card-h-sub">Menampilkan ${lap.length} total laporan · Ditolak: ${tolak}</div>
                </div>
                <div class="table-wrap" style="border-radius:0;border:none;">
                    <table class="neon-table">
                        <thead><tr>
                            <th>Tanggal</th><th>Pemagang</th><th>Ruang</th>
                            <th>Durasi</th><th>Tugas</th><th>Status</th><th>Dosen</th>
                        </tr></thead>
                        <tbody>${tbody}</tbody>
                    </table>
                </div>
            </div>
        </div>`;
    } catch(e) { renderErr(e); }
}

function mkStat(label, val, icon, cls, color) {
    return `<div class="stat-card ${cls}">
        <div class="stat-icon" style="color:${color};"><i class="fa-solid ${icon}"></i></div>
        <div class="stat-value" style="color:${color};">${val}</div>
        <div class="stat-label">${label}</div>
    </div>`;
}

/* ================================================================
   ██ TAB: JADWAL ALL (ADMIN)
   ================================================================ */
async function tabJadwalAll() {
    setTop('Semua Jadwal Magang', 'Monitor dan kelola seluruh jadwal dari dosen');
    skeleton(4);
    try {
        const res = await apiGet('getJadwal');
        const rows = res.data || [];

        const tbody = rows.length ? rows.map(r => `
            <tr>
                <td style="font-weight:600;color:var(--text);">${r.judul||'-'}</td>
                <td style="color:var(--cyan);font-size:.78rem;">${r.dosenKey||'-'}</td>
                <td>${fmtDate(r.tanggal)}</td>
                <td>${fmtDate(r.selesai)}</td>
                <td>${r.ruang||'-'}</td>
                <td style="color:var(--gold);font-weight:600;">${r.kuota||'-'}</td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <button class="btn-neon-purple" onclick='lihatJadwalAdmin(${JSON.stringify(r).replace(/"/g,"&quot;")})'>
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button class="btn-neon-red" onclick="hapusJadwalAdmin('${r.id}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`).join('') : `<tr><td colspan="7">${emptyHTML('Belum ada jadwal')}</td></tr>`;

        document.getElementById('tabContent').innerHTML = `
        <div style="animation:slideUp .35s ease-out;">
            <div class="table-wrap">
                <table class="neon-table">
                    <thead><tr>
                        <th>Judul</th><th>Dosen</th><th>Mulai</th>
                        <th>Selesai</th><th>Ruang</th><th>Kuota</th><th>Aksi</th>
                    </tr></thead>
                    <tbody>${tbody}</tbody>
                </table>
            </div>
        </div>`;
    } catch(e) { renderErr(e); }
}

function lihatJadwalAdmin(r) {
    openModal('Detail Jadwal — ' + r.judul, `
        <div style="display:flex;flex-direction:column;gap:.65rem;font-size:.9rem;">
            ${detailRow('Judul', r.judul, 'var(--cyan)')}
            ${detailRow('Dosen', r.dosenKey, 'var(--cyan)')}
            ${detailRow('Mulai', fmtDate(r.tanggal))}
            ${detailRow('Selesai', fmtDate(r.selesai))}
            ${detailRow('Ruang', r.ruang)}
            ${detailRow('Kuota', r.kuota + ' orang')}
            <div style="background:rgba(0,243,255,.04);border:1px solid var(--border);border-radius:.65rem;padding:.85rem;">
                <div style="font-size:.75rem;color:var(--muted);margin-bottom:.4rem;">Deskripsi Tugas</div>
                <div>${r.tugas||'-'}</div>
            </div>
        </div>`);
}

async function hapusJadwalAdmin(id) {
    if (!confirm('Yakin ingin menghapus jadwal ini?')) return;
    try {
        const res = await apiPost('hapusJadwal', { id, adminKey: S.user.key });
        if (res.status === 'ok') { toast('Jadwal dihapus!', 'success'); loadTab('jadwal-all'); }
        else { toast('Gagal: ' + res.message, 'error'); }
    } catch(e) { toast('Koneksi gagal', 'error'); }
}

/* ================================================================
   ██ TAB: INFO SISTEM (ADMIN)
   ================================================================ */
function tabInfo() {
    setTop('Info Sistem', 'Konfigurasi dan panduan Portal Magang');
    document.getElementById('tabContent').innerHTML = `
    <div style="max-width:720px;display:flex;flex-direction:column;gap:1.25rem;animation:slideUp .35s ease-out;">

        <div class="neon-card">
            <div class="card-header-gold">
                <div class="card-h-title" style="color:var(--gold);"><i class="fa-solid fa-gear"></i> &nbsp;Konfigurasi Google Apps Script</div>
            </div>
            <div class="card-body">
                <p style="color:var(--muted);font-size:.88rem;margin-bottom:1rem;">
                    Ganti URL berikut di file <code style="color:var(--cyan);background:rgba(0,243,255,.08);padding:.1rem .4rem;border-radius:.3rem;">index.php</code> baris ~580:
                </p>
                <div style="background:#030810;border:1px solid var(--border);border-radius:.65rem;padding:1rem;font-family:monospace;font-size:.82rem;overflow-x:auto;">
                    <span style="color:var(--muted);">// Ganti nilai SCRIPT_URL:</span><br>
                    <span style="color:var(--cyan);">const</span> <span style="color:var(--text);">SCRIPT_URL</span>
                    <span style="color:var(--gold);"> = </span>
                    <span style="color:#ff9f7f;">'https://script.google.com/macros/s/<span style="color:var(--purple);">AKfycb.../exec</span>'</span><span style="color:var(--text);">;</span>
                </div>
            </div>
        </div>

        <div class="neon-card">
            <div class="card-header-cyan">
                <div class="card-h-title" style="color:var(--cyan);"><i class="fa-solid fa-key"></i> &nbsp;Format Key Akses</div>
            </div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:.65rem;">
                    ${keyInfo('DOSEN', 'fa-chalkboard-user', 'var(--cyan)',   'rgba(0,243,255,.1)',   'rgba(0,243,255,.2)',   'DOSEN001, DOSEN_BUDI')}
                    ${keyInfo('MHS',   'fa-user-graduate',   'var(--purple)', 'rgba(157,78,221,.1)', 'rgba(157,78,221,.2)', 'MHS12345, MHS001')}
                    ${keyInfo('ADMIN', 'fa-shield-halved',   'var(--gold)',   'rgba(212,175,55,.1)', 'rgba(212,175,55,.2)', 'ADMIN999, ADMIN001')}
                </div>
            </div>
        </div>

        <div class="alert alert-warn">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Keamanan:</strong> Bagikan key hanya ke pengguna berwenang.
                Key bersifat case-insensitive — otomatis diubah ke huruf kapital.
            </div>
        </div>

        <div class="neon-card">
            <div class="card-header-purple">
                <div class="card-h-title" style="color:var(--purple);"><i class="fa-brands fa-github"></i> &nbsp;Repository GitHub</div>
            </div>
            <div class="card-body">
                <a href="https://github.com/Zenwhite96/portal-magang" target="_blank"
                   style="display:inline-flex;align-items:center;gap:.5rem;color:var(--purple);
                          text-decoration:none;font-weight:600;font-size:.9rem;">
                    <i class="fa-brands fa-github"></i>
                    github.com/Zenwhite96/portal-magang
                </a>
                <p style="color:var(--muted);font-size:.8rem;margin-top:.5rem;">
                    Push update: <code style="color:var(--cyan);background:rgba(0,243,255,.08);padding:.1rem .4rem;border-radius:.3rem;">git add . && git commit -m "update" && git push</code>
                </p>
            </div>
        </div>
    </div>`;
}

function keyInfo(role, icon, color, bg, border, contoh) {
    return `<div style="display:flex;align-items:center;gap:1rem;padding:.85rem 1rem;
                        background:${bg};border:1px solid ${border};border-radius:.65rem;">
        <i class="fa-solid ${icon}" style="color:${color};font-size:1.25rem;width:24px;text-align:center;"></i>
        <div>
            <div style="font-family:'Orbitron',sans-serif;font-size:.78rem;color:${color};">${role}</div>
            <div style="font-size:.8rem;color:var(--muted);">Contoh: <code style="color:var(--text);">${contoh}</code></div>
        </div>
    </div>`;
}

/* ================================================================
   ERROR STATE
   ================================================================ */
function renderErr(e) {
    document.getElementById('tabContent').innerHTML = `
    <div style="max-width:520px;">
        <div class="error-box">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <h3>Gagal Memuat Data</h3>
            <p>${e.message}</p>
            <div class="error-detail">
                <strong>Kemungkinan penyebab:</strong><br>
                • <code>SCRIPT_URL</code> di index.php belum diisi<br>
                • Google Apps Script belum di-deploy sebagai Web App<br>
                • Akses "Anyone" belum diset di GAS deployment<br>
                • Koneksi internet bermasalah
            </div>
            <button class="btn-neon-cyan" onclick="loadTab(S.tab)">
                <i class="fa-solid fa-rotate-right"></i> Coba Lagi
            </button>
        </div>
    </div>`;
}

/* ================================================================
   LOADING BUTTON
   ================================================================ */
function setLoading(btnId, on, offHtml='') {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    if (on) {
        btn._orig = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span>&nbsp; Menyimpan...';
        btn.disabled = true;
    } else {
        btn.innerHTML = offHtml || btn._orig || '';
        btn.disabled = false;
    }
}

/* ================================================================
   SESSION RESTORE
   ================================================================ */
(function() {
    try {
        const s = sessionStorage.getItem('pmUser');
        if (s) { S.user = JSON.parse(s); initDashboard(); }
    } catch { sessionStorage.removeItem('pmUser'); }
})();
</script>

</body>
</html>
