/* ==========================================================================
   UI — helper tampilan: ikon, escape HTML, toast, modal, form, tanggal, dll.
   ========================================================================== */
const Actions = {};   // registri handler untuk atribut data-act="..." (event delegation)

const UI = (() => {
    /* ---------- Ikon (Feather, inline SVG) ---------- */
    const ICONS = {
        home: '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        calendar: '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        users: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        user: '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        check: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        x: '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        clock: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        plus: '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        trash: '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
        logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        menu: '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
        refresh: '<polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>',
        download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        image: '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
        pin: '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        eye: '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        list: '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        chart: '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>',
        camera: '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
        activity: '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        key: '<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>',
        search: '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        briefcase: '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        alert: '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        info: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        send: '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
        server: '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>',
        arrowLeft: '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
    };
    const icon = (name, cls = 'w-4 h-4') =>
        `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name] || ''}</svg>`;

    /* ---------- Escape HTML (WAJIB untuk semua data dari Sheets) ---------- */
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    /* ---------- Tanggal & jam ---------- */
    const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const pad = (n) => String(n).padStart(2, '0');
    function parseDate(s) {
        const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(s || '');
        return m ? new Date(+m[1], +m[2] - 1, +m[3]) : null;
    }
    const fmtDate = (s) => { const d = parseDate(s); return d ? `${pad(d.getDate())} ${MONTHS[d.getMonth()]} ${d.getFullYear()}` : '-'; };
    const fmtDateLong = (s) => { const d = parseDate(s); return d ? `${DAYS[d.getDay()]}, ${pad(d.getDate())} ${MONTHS[d.getMonth()]} ${d.getFullYear()}` : '-'; };
    function fmtStamp(s) {
        const m = /^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2})/.exec(s || '');
        return m ? `${fmtDate(m[1])}, ${m[2]}` : '-';
    }
    function today() { const d = new Date(); return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`; }
    function nowMin() { const d = new Date(); return d.getHours() * 60 + d.getMinutes(); }
    const toMin = (t) => { const [h, m] = String(t).split(':').map(Number); return h * 60 + m; };
    const isTime = (t) => /^([01]\d|2[0-3]):[0-5]\d$/.test(t || '');
    function durasiText(hours) {
        const total = Math.round(Number(hours || 0) * 60);
        const h = Math.floor(total / 60), m = total % 60;
        if (!total) return '0 menit';
        return [h ? `${h} jam` : '', m ? `${m} menit` : ''].filter(Boolean).join(' ');
    }
    const jam = (hours) => (Math.round(Number(hours || 0) * 10) / 10).toLocaleString('id-ID');

    /* ---------- Badge status ---------- */
    const STATUS = {
        Menunggu: ['warn', 'Menunggu validasi'], Tervalidasi: ['ok', 'Tervalidasi'], Ditolak: ['err', 'Ditolak'],
        Diajukan: ['warn', 'Menunggu persetujuan'], Disetujui: ['ok', 'Disetujui'], Dibatalkan: ['gray', 'Dibatalkan'],
        Buka: ['ok', 'Dibuka'], Tutup: ['gray', 'Ditutup'],
    };
    function badge(status) {
        const [tone, label] = STATUS[status] || ['gray', status || '-'];
        return `<span class="badge badge-${tone}">${esc(label)}</span>`;
    }

    /* ---------- Blok umum ---------- */
    const empty = (title, desc = '', ic = 'file') =>
        `<div class="empty">${icon(ic, 'w-9 h-9 mx-auto')}<p class="t">${esc(title)}</p>${desc ? `<p class="text-sm mt-1">${esc(desc)}</p>` : ''}</div>`;

    function skeleton(rows = 4) {
        return `<div class="stack" aria-busy="true"><div class="skeleton" style="height:88px"></div>` +
            Array.from({ length: rows }, (_, i) => `<div class="skeleton" style="height:${i ? 56 : 120}px"></div>`).join('') + '</div>';
    }

    function errorState(err) {
        const hints = err.code === 'CONFIG'
            ? '<li>Buka <code>assets/js/config.js</code> lalu isi <code>SCRIPT_URL</code> dengan URL /exec dari Apps Script.</li>'
            : `<li>Deployment: <b>Jalankan sebagai Saya</b> &amp; <b>Akses Siapa saja</b>.</li>
               <li>Setelah mengubah kode di Apps Script, pilih <b>Terapkan → Kelola deployment → Versi baru</b>.</li>
               <li>Jalankan fungsi <code>setup</code> sekali di editor Apps Script untuk memberi izin Sheets &amp; Drive.</li>`;
        return `<div class="card" style="max-width:640px;margin:24px auto"><div class="card-body">
            <div class="alert alert-err">${icon('alert', 'w-5 h-5')}<div><b>Gagal memuat data</b><div class="mt-1">${esc(err.message)}</div></div></div>
            <ul class="mt-4 text-sm list-disc pl-5 space-y-1" style="color:var(--text-2)">${hints}</ul>
            <div class="mt-4"><button class="btn btn-primary" data-act="retry">${icon('refresh')}Coba lagi</button></div></div></div>`;
    }

    /* ---------- Toast ---------- */
    function toast(message, type = 'ok', ms = 4200) {
        const root = $('#toasts');
        const ic = { ok: 'check', err: 'alert', warn: 'alert', info: 'info' }[type] || 'info';
        const color = { ok: '#12b76a', err: '#f04438', warn: '#f79009', info: '#1d4ed8' }[type];
        const el = document.createElement('div');
        el.className = `toast ${type}`;
        el.setAttribute('role', type === 'err' ? 'alert' : 'status');
        el.innerHTML = `<span style="color:${color};margin-top:2px">${icon(ic, 'w-4 h-4')}</span><span>${esc(message)}</span>`;
        root.appendChild(el);
        setTimeout(() => { el.classList.add('out'); setTimeout(() => el.remove(), 260); }, ms);
    }

    /* ---------- Modal ---------- */
    let confirmResolver = null;
    function openModal({ title, body, footer = '', size = '' }) {
        $('#modal-root').innerHTML = `
        <div class="modal-backdrop" data-modal-backdrop>
          <div class="modal ${size ? 'modal-' + size : ''}" role="dialog" aria-modal="true" aria-label="${esc(title)}">
            <div class="modal-head"><h3>${esc(title)}</h3><button class="icon-btn" data-act="modal-close" aria-label="Tutup">${icon('x', 'w-5 h-5')}</button></div>
            <div class="modal-body">${body}</div>
            ${footer ? `<div class="modal-foot">${footer}</div>` : ''}
          </div>
        </div>`;
        document.body.classList.add('modal-open');
        const first = $('#modal-root input, #modal-root textarea, #modal-root select');
        if (first) first.focus();
    }
    function closeModal() {
        $('#modal-root').innerHTML = '';
        document.body.classList.remove('modal-open');
        if (confirmResolver) { confirmResolver(false); confirmResolver = null; }
    }
    function confirmDialog({ title, message, confirmText = 'Ya, lanjutkan', danger = false }) {
        return new Promise((resolve) => {
            openModal({
                title, size: 'sm',
                body: `<p style="color:var(--text-2)">${esc(message)}</p>`,
                footer: `<button class="btn btn-outline" data-act="modal-close">Batal</button>
                         <button class="btn ${danger ? 'btn-danger-solid' : 'btn-primary'}" data-act="confirm-yes">${esc(confirmText)}</button>`,
            });
            confirmResolver = resolve;
        });
    }
    Actions['modal-close'] = () => closeModal();
    Actions['confirm-yes'] = () => { const r = confirmResolver; confirmResolver = null; closeModal(); if (r) r(true); };

    /* ---------- Form ---------- */
    function field({ id, label, type = 'text', placeholder = '', required = false, hint = '', attrs = '', value = '', cls = '' }) {
        return `<div class="field ${cls}"><label class="label" for="${id}">${esc(label)}${required ? '<span class="req">*</span>' : ''}</label>
            <input class="input" id="${id}" type="${type}" placeholder="${esc(placeholder)}" value="${esc(value)}" ${attrs}>
            ${hint ? `<p class="hint">${hint}</p>` : ''}<p class="field-error" data-err="${id}" role="alert"></p></div>`;
    }
    function textarea({ id, label, placeholder = '', required = false, hint = '', rows = 4, max = 0, cls = '' }) {
        return `<div class="field ${cls}"><label class="label" for="${id}">${esc(label)}${required ? '<span class="req">*</span>' : ''}</label>
            <textarea class="input" id="${id}" rows="${rows}" placeholder="${esc(placeholder)}" ${max ? `maxlength="${max}" data-counter="${max}"` : ''}></textarea>
            ${max ? `<p class="counter" data-counter-for="${id}">0 / ${max}</p>` : ''}
            ${hint ? `<p class="hint">${hint}</p>` : ''}<p class="field-error" data-err="${id}" role="alert"></p></div>`;
    }
    function select({ id, label, options = [], placeholder = '— Pilih —', required = false, hint = '', cls = '' }) {
        return `<div class="field ${cls}"><label class="label" for="${id}">${esc(label)}${required ? '<span class="req">*</span>' : ''}</label>
            <select class="input" id="${id}"><option value="">${esc(placeholder)}</option>${options.map((o) => `<option value="${esc(o.v)}">${esc(o.l)}</option>`).join('')}</select>
            ${hint ? `<p class="hint">${hint}</p>` : ''}<p class="field-error" data-err="${id}" role="alert"></p></div>`;
    }
    const val = (id) => (document.getElementById(id)?.value ?? '').trim();
    function setErr(id, msg) {
        const err = document.querySelector(`[data-err="${id}"]`);
        const input = document.getElementById(id);
        if (err) err.textContent = msg || '';
        if (input) { input.classList.toggle('is-invalid', !!msg); input.setAttribute('aria-invalid', msg ? 'true' : 'false'); }
    }
    function clearErrs(root = document) {
        $$('[data-err]', root).forEach((e) => { e.textContent = ''; });
        $$('.is-invalid', root).forEach((e) => e.classList.remove('is-invalid'));
    }
    /** errs = { idInput: 'pesan' } -> true bila TIDAK ada error */
    function showErrs(errs) {
        const ids = Object.keys(errs);
        ids.forEach((id) => setErr(id, errs[id]));
        if (ids.length) { document.getElementById(ids[0])?.focus(); toast(errs[ids[0]], 'warn'); }
        return ids.length === 0;
    }
    /** Jalankan aksi async pada tombol: nonaktifkan + spinner. */
    async function busy(btn, fn) {
        if (!btn || btn.disabled) return;
        const html = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span><span>Memproses…</span>';
        try { return await fn(); }
        finally { if (btn.isConnected) { btn.disabled = false; btn.innerHTML = html; } }
    }
    function bindCounters(root = document) {
        $$('[data-counter]', root).forEach((ta) => {
            const out = document.querySelector(`[data-counter-for="${ta.id}"]`);
            const upd = () => { if (out) out.textContent = `${ta.value.length} / ${ta.dataset.counter}`; };
            ta.addEventListener('input', upd); upd();
        });
    }

    /* ---------- Foto: kompres di browser sebelum diunggah ---------- */
    async function compressImage(file, maxDim = 1280, quality = 0.8) {
        if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) throw new Error(`"${file.name}" bukan gambar JPG/PNG/WEBP.`);
        if (file.size > 20 * 1024 * 1024) throw new Error(`"${file.name}" terlalu besar (maks 20 MB).`);
        const url = URL.createObjectURL(file);
        try {
            const img = await new Promise((res, rej) => {
                const i = new Image();
                i.onload = () => res(i);
                i.onerror = () => rej(new Error(`"${file.name}" tidak dapat dibaca sebagai gambar.`));
                i.src = url;
            });
            const scale = Math.min(1, maxDim / Math.max(img.naturalWidth, img.naturalHeight));
            const c = document.createElement('canvas');
            c.width = Math.max(1, Math.round(img.naturalWidth * scale));
            c.height = Math.max(1, Math.round(img.naturalHeight * scale));
            c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
            const dataUrl = c.toDataURL('image/jpeg', quality);
            return { name: file.name.replace(/\.[^.]+$/, '') + '.jpg', mime: 'image/jpeg', data: dataUrl.split(',')[1], preview: dataUrl };
        } finally { URL.revokeObjectURL(url); }
    }
    const photoUrl = (id, w = 900) => `https://drive.google.com/thumbnail?id=${encodeURIComponent(id)}&sz=w${w}`;
    const photoLink = (id) => `https://drive.google.com/file/d/${encodeURIComponent(id)}/view`;

    /* ---------- CSV (aman dari CSV-injection, terbuka di Excel) ---------- */
    function downloadCSV(filename, rows) {
        const safe = (c) => {
            let s = String(c ?? '');
            if (/^[=+\-@\t\r]/.test(s)) s = "'" + s;
            return `"${s.replace(/"/g, '""')}"`;
        };
        const csv = '\ufeff' + rows.map((r) => r.map(safe).join(',')).join('\r\n');
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        a.download = filename;
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(() => URL.revokeObjectURL(a.href), 2000);
    }

    return {
        icon, esc, $, $$, fmtDate, fmtDateLong, fmtStamp, today, nowMin, toMin, isTime, durasiText, jam, parseDate,
        badge, empty, skeleton, errorState, toast, openModal, closeModal, confirmDialog,
        field, textarea, select, val, setErr, clearErrs, showErrs, busy, bindCounters,
        compressImage, photoUrl, photoLink, downloadCSV,
    };
})();
