// Dashboard 7 Hari Terakhir — dua chart uPlot (canvas) seperti Dashboard
// Eksekutif. Dimuat lewat dynamic import di app.js HANYA bila elemen
// #dashboard-tren-data ada di halaman (yaitu /dashboard).
import uPlot from 'uplot';
import 'uplot/dist/uPlot.min.css';

const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(n || 0));

export function initDashboardTren() {
    const dataEl = document.getElementById('dashboard-tren-data');
    if (!dataEl) return;

    const tren = JSON.parse(dataEl.textContent || '[]');
    if (!tren.length) return;

    const cssVar = (name, fallback) => {
        const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return v || fallback;
    };
    const warnaGrid = cssVar('--color-slate-200', '#e2e8f0');
    const warnaTeks = cssVar('--color-slate-400', '#94a3b8');

    const targets = [
        { el: document.getElementById('tren-order-chart'), kunci: 'order', warna: cssVar('--brand-accent-light', '#006C7B'), format: (v) => v + ' order' },
        { el: document.getElementById('tren-kas-chart'), kunci: 'kas', warna: cssVar('--color-emerald-600', '#059669'), format: rupiah },
    ];

    for (const t of targets) {
        if (!t.el) continue;
        const labels = tren.map((r) => r.label);
        const values = tren.map((r) => Number(r[t.kunci] || 0));
        const maxV = Math.max(1, ...values);

        const opts = {
            width: Math.max(240, t.el.clientWidth),
            height: 140,
            cursor: { drag: { x: false, y: false } },
            legend: { show: false },
            scales: {
                x: { time: false },
                y: { range: () => [0, maxV * 1.12] },
            },
            axes: [
                {
                    stroke: warnaTeks,
                    grid: { stroke: warnaGrid, width: 0.6 },
                    values: (u, splits) => splits.map((i) => labels[Math.round(i)] ?? ''),
                    size: 30,
                    font: '10px system-ui, sans-serif',
                },
                {
                    stroke: warnaTeks,
                    grid: { stroke: warnaGrid, width: 0.6 },
                    values: (u, splits) => splits.map((v) => t.format(v)),
                    size: 46,
                    font: '10px system-ui, sans-serif',
                },
            ],
            series: [
                {},
                {
                    width: 2.25,
                    stroke: t.warna,
                    fill: 'color-mix(in srgb, ' + t.warna + ' 14%, transparent)',
                    points: { show: false },
                },
            ],
        };

        const xs = tren.map((_, i) => i);
        new uPlot(opts, [xs, values], t.el);

        // Tooltip hover sederhana (mouseover pada canvas tidak ada API bawaan).
        t.el.addEventListener('mouseleave', () => { t.el.style.cursor = 'default'; });
    }

    return { tren };
}

export default initDashboardTren;
