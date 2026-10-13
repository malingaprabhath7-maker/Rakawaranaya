# Rakawaranaya · රැකවරණය

**A bilingual (Sinhala & English) CRM built for insurance advisors in Sri Lanka.**
Clients, policies, premium due dates, renewals, birthdays and follow-ups in one place, with one-tap WhatsApp reminders. Advisors spend less time on paperwork and more time with clients.

[![tests](https://github.com/malingaprabhath7-maker/rakawaranaya/actions/workflows/tests.yml/badge.svg)](https://github.com/malingaprabhath7-maker/rakawaranaya/actions/workflows/tests.yml)
![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Tailwind CSS 4](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?logo=alpinedotjs&logoColor=white)

**Live demo:** _add your Render link here_ — press **"Try the demo"**. No sign-up is needed, and the sample data resets every night.

<!-- After deploying, add screenshots to docs/ and uncomment:
<img src="docs/dashboard.png" alt="Dashboard">
<p><img src="docs/phone-today.png" width="230"> <img src="docs/phone-client.png" width="230"> <img src="docs/phone-pipeline.png" width="230"></p>
-->

## Why

Most advisors still track clients in a notebook, WhatsApp chats and an Excel sheet. Payment dates get missed, policies lapse, and renewals and birthdays are forgotten. Rakawarana turns that notebook into a daily to-do list.

## Features

| | |
|---|---|
| **Today list** | Overdue premiums, dues in the next 30 days, motor/medical renewals, birthdays and follow-ups, most urgent first |
| **Dashboard** | Greeting, KPIs, progress against your monthly target, a 6-month collections and commission chart, and a policy mix doughnut |
| **Clients** | Full profile: NIC, date of birth and age, family, income, source, tags and notes · search, filters and sorting · soft delete |
| **Sales pipeline** | Drag-and-drop kanban: Lead → Contacted → Proposal → Client · selling an active policy promotes the lead automatically |
| **Policies** | All 22 Sri Lankan insurers · life, motor, health, property, education, retirement, travel · monthly/quarterly/half-yearly/yearly/single |
| **Payments** | Record a payment and the next due date moves forward (keeping the 31st across short months) · commission is worked out per payment · undo |
| **WhatsApp** | One-tap messages in Sinhala or English for premium due, renewal, birthday, thank-you and follow-up · edit your own templates |
| **Tasks** | Calls, meetings and follow-ups linked to clients |
| **Documents** | Proposal forms, NIC copies and receipts stored on a private disk and served only to the owner |
| **Reports** | Monthly collections, commission, new business and target · print or save as PDF · CSV export for Excel |
| **Import** | Bring an existing Excel list as CSV (UTF-8 keeps Sinhala names), up to 2,000 rows |
| **Calculators** | Premium/commission, life-cover need (income replacement + loans + education − existing cover) and savings goal |
| **Team view** | A branch manager shares an invite code and sees each advisor's totals, never their client lists |
| **Daily digest** | Optional 7 am e-mail with the day's dues, renewals and birthdays |
| **Bilingual** | Full Sinhala and English UI, switch any time · light/dark theme · phone bottom-nav and desktop sidebar |

## Security

Client data is personal data, so security is built in rather than added at the end:

- **Two-step login** with Google/Microsoft Authenticator (TOTP), recovery codes and password confirmation (Laravel Fortify)
- **NIC numbers encrypted at rest** (AES-256 via Laravel's `encrypted` cast), masked on screen and only revealed on request, with each reveal written to the activity log
- **Strict data isolation.** Every client, policy, task and document is checked by a Policy class on every request. Other people's records return 404, not 403. ULID keys mean IDs can't be guessed.
- **Rate limits** on login (5/min per e-mail+IP), 2FA, demo, import, export, NIC reveal and uploads
- **Security headers:** CSP, HSTS, X-Frame-Options DENY, nosniff, Referrer-Policy, Permissions-Policy, COOP, and `no-store` caching for signed-in pages
- **Encrypted sessions**, a session list with "sign out other devices", and an audit log of logins, failed logins, exports and NIC views
- **Strong passwords:** a minimum of 8 characters with letters and numbers, plus mixed case and a check against breached-password lists in production
- **Safe exports:** CSV cells are protected against spreadsheet formula injection
- **Private uploads:** type and size are validated, files are stored outside the web root, and downloads are checked for ownership
- **A demo account that can't do damage:** it is blocked from password, 2FA, team and account-deletion routes, and reset nightly

## Tech stack

- **Laravel 13** (PHP 8.3) with Fortify for authentication and 2FA
- **Blade + Alpine.js 3**: server-rendered, fast on cheap phones, no SPA build complexity
- **Tailwind CSS 4** with a custom design system (brand teal + lamp marigold, Manrope and Noto Sans Sinhala)
- **Chart.js 4** for charts
- **Vite 8** for the asset build
- SQLite by default, MySQL/MariaDB/PostgreSQL in production
- PHPUnit feature tests run on GitHub Actions; Docker image for Render

```
app/Http/Controllers   one controller per screen
app/Policies           ownership checks (OwnedByUser)
app/Services/Insights  dashboard, today-list and report numbers
app/Support            options (insurers, stages…), money, phone, WhatsApp templates, audit log
app/Http/Middleware    locale, security headers, demo guard
lang/si.json           Sinhala translations
resources/views        Blade views and components
database/seeders       42 fictional demo clients
tests/Feature          auth, isolation, payments, pages, demo, export
```

## Run locally

Requirements: PHP 8.3+, Composer, Node 20+. On Windows, [Laragon](https://laragon.org) installs all of these.

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed      # creates the demo account and sample data
php artisan serve               # http://localhost:8000
```

Run the tests with `php artisan test`.

Full Sinhala instructions, including hosting: **[SETUP-SI.md](SETUP-SI.md)**

## Deploy

- **Free demo:** Render → New → Blueprint → this repo (uses `render.yaml` and the `Dockerfile`). Set `APP_KEY` (from `php artisan key:generate --show`) and `APP_URL`. The free plan's disk is temporary, which is fine for the demo.
- **Real use:** any PHP 8.3 host with MySQL (for example Hostinger shared hosting). Point the domain at `public/`, set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` and `DEMO_ENABLED=false`, then add the cron job `* * * * * php artisan schedule:run`.

## Data note

The demo data is entirely fictional. If you use this with real clients, follow your insurer's data rules and Sri Lanka's Personal Data Protection Act, keep backups, and turn on two-step login.

## License

[MIT](LICENSE) © 2026 Malinga Prabhath
