// Records a walkthrough video and screenshots of the demo for README / LinkedIn.
import { chromium } from 'playwright';
import fs from 'node:fs';

const BASE = 'http://127.0.0.1:8000';
const OUT = 'media-out';
fs.mkdirSync(`${OUT}/shots`, { recursive: true });
const wait = (ms) => new Promise((r) => setTimeout(r, ms));

const fonts = '<link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,600,800|noto-sans-sinhala:400,600,800&display=swap">';
const slide = (inner) => `<!doctype html><html><head><meta charset="utf-8">${fonts}<style>
  body{margin:0;height:100vh;display:grid;place-items:center;font-family:Manrope,'Noto Sans Sinhala',sans-serif;
  background:radial-gradient(circle at 80% 10%,#cfeee7 0,transparent 45%),radial-gradient(circle at 10% 90%,#fde9c4 0,transparent 40%),#f7f5ef;color:#0f172a}
  .wrap{text-align:center;max-width:980px;padding:40px}
  .logo{width:92px;height:92px;border-radius:26px;background:#0f6b60;display:grid;place-items:center;margin:0 auto 26px;box-shadow:0 20px 40px rgba(15,107,96,.3)}
  h1{font-size:64px;margin:0;font-weight:800;letter-spacing:-1px} h1 span{color:#0f6b60}
  p{font-size:26px;color:#475569;margin:14px 0 0;font-weight:600}
  .chips{margin-top:34px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
  .chips b{background:#fff;border:1px solid #e2e8f0;border-radius:999px;padding:8px 16px;font-size:17px;color:#0f172a}
  .url{margin-top:30px;font-size:22px;color:#0f6b60;font-weight:800}
</style></head><body><div class="wrap">
  <div class="logo"><svg viewBox="0 0 32 32" width="56" height="56" fill="none"><path d="M16 4 6 8v7.5C6 21.6 10.3 26.6 16 28c5.7-1.4 10-6.4 10-12.5V8z" fill="#fff" opacity=".25"/><path d="M16 4 6 8v7.5C6 21.6 10.3 26.6 16 28c5.7-1.4 10-6.4 10-12.5V8z" stroke="#fff" stroke-width="2" stroke-linejoin="round"/><path d="M16 11c-1.6 2-2.4 3.6-2.4 4.9a2.4 2.4 0 0 0 4.8 0c0-1.3-.8-2.9-2.4-4.9z" fill="#f6c96f"/><path d="M11.5 20.5h9" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg></div>
  ${inner}</div></body></html>`;

async function caption(page, si, en) {
  await page.evaluate(([si, en]) => {
    let c = document.getElementById('__cap');
    if (!c) {
      c = document.createElement('div');
      c.id = '__cap';
      Object.assign(c.style, { position: 'fixed', left: '50%', bottom: '26px', transform: 'translateX(-50%)', zIndex: 99999,
        background: 'rgba(15,23,42,.9)', color: '#fff', padding: '12px 24px', borderRadius: '16px',
        fontFamily: "Manrope,'Noto Sans Sinhala',sans-serif", fontSize: '21px', fontWeight: '800',
        boxShadow: '0 12px 30px rgba(0,0,0,.3)', textAlign: 'center', maxWidth: '84%', pointerEvents: 'none' });
      document.body.appendChild(c);
    }
    c.innerHTML = `<div>${si}</div><div style="font-size:15px;opacity:.78;font-weight:600;margin-top:3px">${en}</div>`;
  }, [si, en]);
}
async function scrollTo(page, y, ms = 1400) {
  await page.evaluate((y) => window.scrollTo({ top: y, behavior: 'smooth' }), y);
  await wait(ms);
}
async function open(page, path, si, en, ms = 3500) {
  await page.goto(BASE + path, { waitUntil: 'networkidle' });
  await caption(page, si, en);
  await wait(ms);
}
async function demoLogin(page) {
  await page.goto(BASE + '/', { waitUntil: 'networkidle' });
  await page.locator('form[action$="/demo"] button').first().click();
  await page.waitForURL(/dashboard/);
  await page.waitForLoadState('networkidle');
}

// ---------- video ----------
const browser = await chromium.launch();
const vctx = await browser.newContext({ viewport: { width: 1280, height: 720 }, recordVideo: { dir: `${OUT}/raw`, size: { width: 1280, height: 720 } } });
const v = await vctx.newPage();

await v.setContent(slide(`<h1>රැකවරණ · <span>Rakawarana</span></h1><p>රක්ෂණ උපදේශකයන් සඳහා CRM එකක්</p><p style="font-size:21px">A bilingual CRM for insurance advisors in Sri Lanka</p>
  <div class="chips"><b>Laravel 13</b><b>PHP 8.3</b><b>Tailwind CSS 4</b><b>Alpine.js</b><b>සිංහල / English</b></div>`));
await wait(4500);

await v.goto(BASE + '/', { waitUntil: 'networkidle' });
await caption(v, 'සිංහල සහ ඉංග්‍රීසි දෙකෙන්ම', 'Bilingual — Sinhala & English');
await wait(3000);
await scrollTo(v, 700, 2200);
await scrollTo(v, 0, 1200);
await caption(v, 'ලියාපදිංචි නොවී demo එක බලන්න', 'One-click demo, no sign-up');
await wait(1800);
await v.locator('form[action$="/demo"] button').first().click();
await v.waitForURL(/dashboard/);
await v.waitForLoadState('networkidle');
await caption(v, 'දවසේ සාරාංශය එක බැල්මෙන්', 'Your day at a glance: target, collections, commission');
await wait(3800);
await scrollTo(v, 600, 2600);
await scrollTo(v, 0, 900);

await open(v, '/today', 'අද එකතු කළ යුතු වාරික, අලුත් කිරීම්, උපන්දින', "Today's list — overdue premiums, renewals, birthdays", 3000);
const wa = v.locator('a[href*="wa.me"]').first();
if (await wa.count()) { await wa.hover(); await caption(v, 'එක tap එකකින් WhatsApp සිහිකැඳවීම', 'One-tap WhatsApp reminder in Sinhala or English'); await wait(2800); }

await open(v, '/pipeline', 'Lead → Proposal → Client — drag කරලා ගෙනියන්න', 'Drag-and-drop sales pipeline', 1500);
try {
  const card = v.locator('[draggable="true"]').first();
  const cols = v.locator('[data-column]');
  if (await card.count() && await cols.count() > 1) { await card.dragTo(cols.nth(1)); }
} catch (e) { console.log('drag skipped', e.message); }
await wait(2500);

await v.goto(BASE + '/clients', { waitUntil: 'networkidle' });
await caption(v, 'සියලු ගනුදෙනුකරුවන් එකම තැනක', 'All clients, searchable');
await wait(2200);
await v.locator('a[href*="/clients/0"], table a[href*="/clients/"]').first().click();
await v.waitForLoadState('networkidle');
await caption(v, 'පවුල, ඔප්පු, ලේඛන — NIC අංකය සංකේතනය කර ඇත', 'Full profile · NIC encrypted, revealed only on request');
await wait(2200);
const reveal = v.locator('[x-data*="nicReveal"] button').first();
if (await reveal.count()) { await reveal.click(); await wait(1800); }
await scrollTo(v, 500, 2000);

await v.goto(BASE + '/policies', { waitUntil: 'networkidle' });
await v.locator('table a[href*="/policies/"]').first().click();
await v.waitForLoadState('networkidle');
await caption(v, 'ගෙවීම සටහන් කළාම ඊළඟ දිනය සහ කොමිස් ස්වයංක්‍රීයව', 'Record a payment → next due date & commission update automatically');
await wait(2500);
const payForm = v.locator('form[action*="/payments"]').first();
if (await payForm.count()) {
  await payForm.scrollIntoViewIfNeeded();
  await wait(800);
  await payForm.locator('button[type="submit"], button:not([type])').last().click();
  await v.waitForLoadState('networkidle');
  await caption(v, 'ගෙවීම සටහන් විය ✓', 'Payment recorded ✓');
  await wait(2800);
}

await open(v, '/reports', 'මාසික වාර්තා — PDF සහ Excel', 'Monthly reports · PDF & Excel export', 3500);
await open(v, '/tools', 'ගනුදෙනුකරු හමුවීම් සඳහා ගණක', 'Life-cover & savings calculators', 3000);

await v.goto(BASE + '/locale/en', { waitUntil: 'networkidle' });
await v.goto(BASE + '/dashboard', { waitUntil: 'networkidle' });
await caption(v, 'ඕනෑම වෙලාවක English වලට මාරු කරන්න', 'Switch to English any time');
await wait(2600);
await v.evaluate(() => window.setTheme && window.setTheme('dark'));
await caption(v, 'Dark mode', 'Light & dark themes');
await wait(2600);
await open(v, '/settings/security', 'Two-step login, sessions සහ activity log', 'Security: 2FA, sessions, audit log', 3200);
await v.evaluate(() => window.setTheme && window.setTheme('light'));

await v.setContent(slide(`<h1>රැකවරණ · <span>Rakawarana</span></h1><p>Laravel 13 · Tailwind CSS 4 · Alpine.js · 2FA · Encrypted data</p>
  <div class="url">github.com/malingaprabhath7-maker/Rakawaranaya</div><p style="font-size:20px">Built by Malinga Prabhath 🇱🇰</p>`));
await wait(4500);
await vctx.close();

// ---------- screenshots ----------
async function shots(ctxOpts, prefix, list) {
  const ctx = await browser.newContext(ctxOpts);
  const p = await ctx.newPage();
  await demoLogin(p);
  for (const [name, path, theme] of list) {
    if (path === '/') { await ctx.clearCookies(); }
    await p.goto(BASE + path, { waitUntil: 'networkidle' });
    if (theme) { await p.evaluate((t) => window.setTheme && window.setTheme(t), theme); await wait(400); }
    await p.screenshot({ path: `${OUT}/shots/${prefix}-${name}.png` });
    if (theme) { await p.evaluate(() => window.setTheme && window.setTheme('light')); }
  }
  await ctx.close();
}
await shots({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1.5 }, 'desktop', [
  ['dashboard', '/dashboard'], ['today', '/today'], ['pipeline', '/pipeline'], ['clients', '/clients'],
  ['reports', '/reports'], ['dashboard-dark', '/dashboard', 'dark'], ['landing', '/'],
]);
await shots({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 3, isMobile: true, hasTouch: true }, 'phone', [
  ['dashboard', '/dashboard'], ['today', '/today'], ['clients', '/clients'], ['dark', '/today', 'dark'],
]);
await browser.close();
console.log('done');
