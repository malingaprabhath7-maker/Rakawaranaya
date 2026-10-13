import Alpine from 'alpinejs';
import {
    Chart, BarController, BarElement, DoughnutController, ArcElement, LineController, LineElement, PointElement,
    CategoryScale, LinearScale, Tooltip, Legend, Filler,
} from 'chart.js';

Chart.register(BarController, BarElement, DoughnutController, ArcElement, LineController, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler);

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;
const css = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
const fmt = (n) => 'Rs. ' + Number(n).toLocaleString('en-LK', { maximumFractionDigits: 0 });
const short = (n) => (n >= 1e6 ? (n / 1e6).toFixed(1).replace(/\.0$/, '') + 'M' : n >= 1e3 ? Math.round(n / 1e3) + 'K' : String(n));

/* ---------- theme ---------- */
window.setTheme = (mode) => {
    try { localStorage.setItem('theme', mode); } catch (e) {}
    applyTheme();
    document.dispatchEvent(new Event('themechange'));
};
function applyTheme() {
    let mode = 'system';
    try { mode = localStorage.getItem('theme') || 'system'; } catch (e) {}
    const dark = mode === 'dark' || (mode === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);
}
applyTheme();
matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', applyTheme);

/* ---------- Alpine helpers ---------- */
Alpine.data('nicReveal', (url) => ({
    shown: null,
    async reveal() {
        if (this.shown) { this.shown = null; return; }
        const r = await fetch(url, { headers: { Accept: 'application/json' } });
        if (r.ok) this.shown = (await r.json()).nic;
    },
}));

// mark "contacted" when a WhatsApp / call link is used
Alpine.data('touch', (url) => ({
    ping() { fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' } }).catch(() => {}); },
}));

// drag cards between pipeline columns
Alpine.data('pipeline', () => ({
    dragging: null,
    over: null,
    start(e, id, url) { this.dragging = { id, url }; e.dataTransfer.effectAllowed = 'move'; },
    async drop(stage, e) {
        this.over = null;
        if (!this.dragging) return;
        const card = document.getElementById('card-' + this.dragging.id);
        e.currentTarget.querySelector('[data-cards]').prepend(card);
        const { url } = this.dragging;
        this.dragging = null;
        await fetch(url, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ stage }),
        });
        this.recount();
    },
    recount() {
        document.querySelectorAll('[data-column]').forEach((col) => {
            col.querySelector('[data-count]').textContent = col.querySelectorAll('[data-card]').length;
        });
    },
}));

// Premium / commission / life-cover calculators (Tools page)
Alpine.data('calculators', () => ({
    premium: 5000, freq: 12, rate: 25,
    get annual() { return this.premium * this.freq; },
    get commission() { return this.annual * this.rate / 100; },
    income: 100000, years: 20, existing: 1000000, debts: 2000000, children: 2, edu: 1500000,
    get need() { return Math.max(0, this.income * 12 * this.years + Number(this.debts) + this.children * this.edu - this.existing); },
    target: 3000000, yearsToGoal: 15, growth: 8,
    get monthly() {
        const r = this.growth / 100 / 12, n = this.yearsToGoal * 12;
        return r === 0 ? this.target / n : (this.target * r) / (Math.pow(1 + r, n) - 1);
    },
    fmt, short,
}));

window.Alpine = Alpine;
Alpine.start();

/* ---------- charts (data comes from <script type="application/json"> blocks) ---------- */
const charts = [];
function drawCharts() {
    charts.splice(0).forEach((c) => c.destroy());
    const ink = css('--color-muted') || '#5f6b69';
    const line = css('--color-line') || '#e3e2da';
    const brand = css('--color-brand-500') || '#1a8174';
    const lamp = css('--color-lamp-400') || '#eeb04a';
    const palette = [brand, lamp, '#4f7cac', '#c2577a', '#7a6bb8', '#5aa469', '#b86a3b', '#7b8a88'];
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = ink;

    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const data = JSON.parse(document.getElementById(canvas.dataset.chart).textContent);
        if (data.kind === 'bars') {
            charts.push(new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: data.series.map((s, i) => ({ label: s.label, data: s.values, backgroundColor: i ? lamp : brand, borderRadius: 6, maxBarThickness: 28 })),
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, useBorderRadius: true, borderRadius: 3 } },
                        tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${fmt(c.raw)}` } } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: line }, border: { display: false }, ticks: { callback: short, maxTicksLimit: 5 } } },
                },
            }));
        } else if (data.kind === 'doughnut') {
            charts.push(new Chart(canvas, {
                type: 'doughnut',
                data: { labels: data.labels, datasets: [{ data: data.values, backgroundColor: palette, borderColor: css('--color-surface') || '#fff', borderWidth: 3 }] },
                options: { maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'right', labels: { boxWidth: 10, boxHeight: 10, useBorderRadius: true, borderRadius: 3 } } } },
            }));
        }
    });
}
document.addEventListener('DOMContentLoaded', drawCharts);
document.addEventListener('themechange', drawCharts);
