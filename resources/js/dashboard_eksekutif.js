// Dashboard Eksekutif (/eksekutif) - chart & animasi halaman itu saja.
// Dimuat HANYA bila elemen #dashboard-eksekutif-chart ada (dynamic import
// di app.js), jadi halaman lain tidak membayar ongkos download modul ini.
//
// Prinsip performa & jangkauan data besar:
//   - Chart dirender ke <canvas> lewat uPlot (bukan SVG), satu-satunya jalur
//     yang masih nyaman pada jutaan titik.
//   - Decimation Largest-Triangle-Three-Buckets aktif otomatis bila poin >
//     LTTB_LIMIT, sehingga jutaan baris tidak membuat frame drop; dataset
//     kecil (6 bulan) dipakai apa adanya.
//   - Tidak ada animasi dekoratif: chart draw-in + count-up angka KPI sekali
//     per sesi (bukan per klik), reveal list "Top" sekali per sesi, semuanya
//     gated prefers-reduced-motion.
import uPlot from 'uplot';
import 'uplot/dist/uPlot.min.css';
import { animate } from 'motion';
import { animate as anime, stagger } from 'animejs';

const LTTB_LIMIT = 1500;
const LTTB_TARGET = 500;
const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const sudahIntro = () => sessionStorage.getItem('eksekutif-intro') === '1';
const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(n || 0));
const juta = (n) => {
    const j = (n || 0) / 1e6;
    return (j >= 100 ? Math.round(j).toLocaleString('id-ID') : j.toFixed(1).replace('.', ',')) + ' jt';
};

// Largest-Triangle-Three-Buckets: memilih titik-titik paling mewakili bentuk
// kurva, supaya jutaan data tidak perlu dirender semuanya.
function lttb(xs, ys, threshold) {
    const n = xs.length;
    if (n <= threshold) return { xs, ys };
    const sampledX = [xs[0]];
    const sampledY = [ys[0]];
    const every = (n - 2) / (threshold - 2);
    let a = 0;
    for (let i = 0; i < threshold - 2; i++) {
        const avgStart = Math.floor((i + 1) * every) + 1;
        const avgEnd = Math.floor((i + 2) * every) + 1;
        let avgX = 0, avgY = 0;
        const avgRange = Math.max(1, avgEnd - avgStart);
        for (let j = avgStart; j < avgEnd && j < n; j++) { avgX += xs[j]; avgY += ys[j]; }
        avgX /= avgRange; avgY /= avgRange;
        const rangeStart = Math.floor(i * every) + 1;
        const rangeEnd = Math.floor((i + 1) * every) + 1;
        let maxArea = -1, nextA = rangeStart;
        for (let j = rangeStart; j < rangeEnd && j < n; j++) {
            const area = Math.abs((xs[a] - avgX) * (ys[j] - ys[a]) - (xs[a] - xs[j]) * (avgY - ys[a]));
            if (area > maxArea) { maxArea = area; nextA = j; }
        }
        sampledX.push(xs[nextA]);
        sampledY.push(ys[nextA]);
        a = nextA;
    }
    sampledX.push(xs[n - 1]);
    sampledY.push(ys[n - 1]);
    return { xs: sampledX, ys: sampledY };
}

function buatChart(el, labels, values) {
    const norm = lttb(labels.map((_, i) => i), values, LTTB_LIMIT);
    const xs = norm.xs, ys = norm.ys;
    // Warna dari token tema (warna tidak pernah di-hardcode di JS; semua
    // diambil dari CSS variable yang didefine UI global / per tema).
    const cssVar = (n, fallback) => {
        const v = getComputedStyle(document.documentElement).getPropertyValue(n).trim();
        return v || fallback;
    };
    const warnaGaris = cssVar('--brand-accent-light', '#006C7B');
    const warnaTeks = cssVar('--color-slate-400', '#94a3b8');
    const warnaGrid = cssVar('--color-slate-200', '#e2e8f0');
    const warnaArea = 'color-mix(in srgb, ' + warnaGaris + ' 12%, transparent)';
    const opts = {
        width: Math.max(240, el.clientWidth),
        height: 190,
        cursor: { drag: { x: false, y: false } },
        legend: { show: false },
        scales: {
            x: { time: false },
            y: { range: (u, dataMin, dataMax) => [0, (dataMax || 1) * 1.12] },
        },
        axes: [
            {
                stroke: warnaTeks,
                grid: { stroke: warnaGrid, width: 0.6 },
                values: (u, splits) => splits.map((i) => labels[Math.round(i)] ?? ''),
                size: 34,
                font: '11px system-ui, sans-serif',
            },
            {
                stroke: warnaTeks,
                grid: { stroke: warnaGrid, width: 0.6 },
                values: (u, splits) => splits.map((v) => juta(v)),
                size: 46,
                font: '11px system-ui, sans-serif',
            },
        ],
        series: [
            {},
            {
                label: 'Omzet',
                width: 2,
                stroke: warnaGaris,
                fill: warnaArea,
                points: { show: false },
            },
        ],
    };
    const plot = new uPlot(opts, [xs, ys], el);

    // Tooltip mengambang sederhana (tanpa lib tambahan).
    const tip = document.createElement('div');
    tip.className = 'eksekutif-tip';
    tip.style.cssText = 'position:absolute;pointer-events:none;display:none;z-index:20;background:var(--color-slate-900);color:var(--color-slate-100);font:11px/1.5 system-ui,sans-serif;padding:6px 9px;border-radius:8px;box-shadow:0 6px 18px color-mix(in srgb, var(--color-slate-900) 30%, transparent);white-space:nowrap';
    el.style.position = 'relative';
    el.appendChild(tip);
    plot.over.addEventListener('mousemove', () => {
        const idx = plot.cursor.idx;
        if (idx == null) { tip.style.display = 'none'; return; }
        tip.style.display = 'block';
        tip.textContent = (labels[Math.round(xs[idx])] ?? '') + ' • ' + rupiah(ys[idx]);
        const { left, top } = plot.cursor.left != null ? { left: plot.cursor.left, top: plot.cursor.top } : { left: 0, top: 0 };
        tip.style.left = Math.min(el.clientWidth - tip.offsetWidth - 4, Math.max(0, left)) + 'px';
        tip.style.top = Math.max(0, top - 34) + 'px';
    });
    plot.over.addEventListener('mouseleave', () => { tip.style.display = 'none'; });

    // Resize responsif tanpa pustaka tambahan.
    let resizeT;
    window.addEventListener('resize', () => {
        clearTimeout(resizeT);
        resizeT = setTimeout(() => plot.setSize({ width: Math.max(240, el.clientWidth), height: 190 }), 100);
    }, { passive: true });

    return { plot, xs, ys };
}

// Chart draw-in: 2 titik -> seluruh seri dalam ~450ms ease-out. Murah (canvas
// digambar ulang beberapa frame saja), gated reduced-motion.
function gambarBertahap(plot, xs, ys) {
    if (reduceMotion()) return;
    const target = xs.length;
    const mulai = performance.now();
    const durasi = 450;
    const kendali = (t) => {
        const k = Math.min(1, (t - mulai) / durasi);
        const e = 1 - Math.pow(2, -10 * k); // ease-out-expo-ish
        const n = Math.max(2, Math.round(2 + (target - 2) * (k === 1 ? 1 : e)));
        plot.setData([xs.slice(0, n), ys.slice(0, n)]);
        if (k < 1) requestAnimationFrame(kendali);
    };
    requestAnimationFrame(kendali);
}

function hitungNaik(el) {
    const target = Number(el.dataset.kpiValue || 0);
    const teksAkhir = el.textContent;
    const durasi = 600;
    const mulai = performance.now();
    const frame = (t) => {
        const k = Math.min(1, (t - mulai) / durasi);
        // ease-out: mulai cepat, mendarat halus (bukan ease-in yang lambat di awal).
        const easeOut = 1 - Math.pow(1 - k, 3);
        el.textContent = rupiah(target * easeOut);
        if (k < 1) requestAnimationFrame(frame);
        else el.textContent = teksAkhir;
    };
    el.textContent = rupiah(0);
    requestAnimationFrame(frame);
}

export function initDashboardEksekutif() {
    const dataEl = document.getElementById('eksekutif-tren-data');
    const chartEl = document.getElementById('dashboard-eksekutif-chart');
    if (!dataEl || !chartEl) return;

    let tren = [];
    try { tren = JSON.parse(dataEl.textContent || '[]'); } catch (e) { tren = []; }
    if (!Array.isArray(tren) || tren.length === 0) return;

    const labels = tren.map((t) => t.label);
    const values = tren.map((t) => Number(t.omzet) || 0);
    const { plot, xs, ys } = buatChart(chartEl, labels, values);
    const first = sudahIntro() ? true : false;
    if (!first && !reduceMotion()) {
        // Masuk: kartu chart fade + naik sedikit, lalu kurva digambar bertahap.
        animate(chartEl.closest('[data-chart-card]') || chartEl, { opacity: [0, 1], transform: ['translateY(10px)', 'translateY(0px)'] }, { duration: 0.5, delay: 0.05, ease: [0.23, 1, 0.32, 1] });
        gambarBertahap(plot, xs, ys);
    }

    // Count-up angka KPI utama (sekali per sesi, ease-out, bukan dekorasi).
    if (!first && !reduceMotion()) {
        document.querySelectorAll('[data-kpi-value]').forEach((el) => hitungNaik(el));
    }

    // Reveal bertahap baris Top 5 (stagger 30ms, sekali per sesi).
    const rows = document.querySelectorAll('[data-top-row]');
    if (rows.length && !first && !reduceMotion()) {
        anime(rows, { opacity: [0, 1], translateY: [8, 0], delay: stagger(30, { start: 120 }), duration: 450, easing: 'easeOutCubic' });
    }

    sessionStorage.setItem('eksekutif-intro', '1');
}
