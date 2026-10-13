# රැකවරණ — Setup උපදෙස් (සිංහලෙන්)

මේ උපදෙස් හතර කොටසකට බෙදලා තියෙනවා:

1. ඔබේ laptop එකේ run කරන හැටි
2. GitHub එකට දාන හැටි (tests තමන්ම run වෙනවා ✅)
3. නොමිලේ online demo එකක් දාන හැටි (Render)
4. LinkedIn එකට දාන හැටි

> **වැදගත්:** demo එකේ තියෙන්නේ කල්පිත (බොරු) නම් සහ අංක පමණයි. සැබෑ ගනුදෙනුකරුවන්ගේ විස්තර දාන්න කලින්, ඔබේ රක්ෂණ සමාගමේ (Arpico) නීති අනුව ගනුදෙනුකරු දත්ත පෞද්ගලික app එකක තියාගන්න අවසර තියෙනවද කියලා අහලා බලන්න.

---

## 1. Laptop එකේ run කිරීම (Windows)

### 1.1 Laragon install කරන්න (එක් වරක් පමණයි)

1. **https://laragon.org/download** → **Laragon Full** download කරලා install කරන්න.
2. Laragon විවෘත කරලා **Start All** ඔබන්න.
3. Laragon එකේ PHP **8.3 හෝ ඊට වැඩි** විය යුතුයි.
   - Menu → PHP → Version එකෙන් බලන්න.
   - පරණ නම්: **https://windows.php.net/download** එකෙන් PHP 8.3 "Thread Safe" zip එක download කරන්න.
   - ඒක `C:\laragon\bin\php\` folder එකට extract කරන්න.
   - Menu → PHP → Version → 8.3 තෝරන්න.
4. **Node.js:** https://nodejs.org → **LTS** version එක install කරන්න.

### 1.2 Project එක සකසන්න

1. මම දුන්න `rakawarana.zip` එක `C:\laragon\www\rakawarana` folder එකට extract කරන්න.
2. Laragon එකේ **Terminal** button එක ඔබන්න.
3. පහත commands එකින් එක type කරන්න:

```bash
cd C:\laragon\www\rakawarana
composer install
npm install
npm run build
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

- `migrate` එක "database.sqlite does not exist… create it?" කියලා ඇහුවොත් **yes** කියන්න.
- අන්තිම command එකෙන් පස්සේ browser එකේ **http://localhost:8000** විවෘත කරන්න.
- **"Demo එක බලන්න"** ඔබලා සාම්පල දත්ත සමඟ බලන්න, නැත්නම් **ලියාපදිංචි වන්න** ඔබලා ඔබේම ගිණුමක් හදාගන්න.

Tests run කරන්න: `php artisan test` (සියල්ල කොළ පාටින් **PASS** විය යුතුයි).

### 1.3 Phone එකෙන් බලන්න (එකම WiFi එකේ නම්)

```bash
php artisan serve --host=0.0.0.0
```

1. Laptop එකේ IP එක සොයාගන්න: `ipconfig` → IPv4 Address (උදා. 192.168.1.5).
2. Phone එකේ browser එකේ `http://192.168.1.5:8000` විවෘත කරන්න.

---

## 2. GitHub එකට දාන්න

1. **github.com → New repository**
   - නම: `rakawarana`
   - **Public**
   - README එකතු කරන්න එපා.
   - **Create repository** ඔබන්න.
2. **"uploading an existing file"** link එක ඔබන්න.
3. Zip එකේ ඇතුළේ තියෙන **files සහ folders සියල්ලම** drag කරලා දාන්න:
   - `app`, `bootstrap`, `config`, `database`, `docker`, `lang`, `public`, `resources`, `routes`, `storage`, `tests`, `.github`, `README.md` …
   - ⚠️ `rakawarana` කියන folder එකම නෙවෙයි — ඒක ඇතුළේ තියෙන දේවල් දාන්න (Viyadam Pota වලදී 404 ආවේ මේ නිසා).
   - ⚠️ `vendor`, `node_modules` සහ `.env` **upload කරන්න එපා.** ඒවා විශාලයි, `.env` එකේ රහස් key එක තියෙනවා.
   - `.github` වගේ තිතකින් පටන් ගන්න folders Windows එකේ නොපෙනුනොත්: File Explorer → View → **Hidden items** ✔
4. **Commit changes** ඔබන්න.
5. **Actions** tab එකට යන්න. විනාඩි 2–3 කින් **tests** workflow එක ✅ කොළ පාට වෙයි.
   - ❌ රතු නම්, ඒක ඔබලා error එකේ screenshot එක මට එවන්න. මම හදලා දෙන්නම්.
6. Repo එකේ **About** (⚙️) එකේ මේවා දාන්න:
   - Description: `Bilingual (Sinhala/English) CRM for insurance advisors — Laravel 13, Tailwind 4, Alpine.js, 2FA`
   - Topics: `laravel` `php` `tailwindcss` `alpinejs` `crm` `insurance` `sri-lanka` `sinhala`

> **README එකේ link එක:** README එකේ `malingaprabhath7-maker` කියලා තියෙන්නේ ඔබේ GitHub username එක. වෙනස් නම් ඒක මාරු කරන්න.

---

## 3. නොමිලේ online demo එක (Render)

GitHub Pages වල PHP run වෙන්නේ නැහැ. ඒ නිසා Laravel සඳහා **Render.com** (free) පාවිච්චි කරමු.

1. **https://render.com** → **Get Started** → **GitHub** වලින් sign up වෙන්න.
2. Laptop එකේ Laragon terminal එකේ මේ command එක run කරන්න:
   ```bash
   php artisan key:generate --show
   ```
   එන `base64:...` කියන පේළිය copy කරගන්න.
3. Render dashboard → **New + → Blueprint** → `rakawarana` repo එක තෝරන්න → **Connect**.
4. එහි ඉල්ලන values දෙක දාන්න:
   - `APP_KEY` = උඩ copy කළ `base64:...` පේළිය
   - `APP_URL` = `https://rakawarana.onrender.com` (Render දෙන නම)
5. **Apply** ඔබන්න. පළමු build එකට විනාඩි 5–10ක් යනවා.
6. ඉවර වුණාම Render දෙන link එක (`https://rakawarana-xxxx.onrender.com`) README එකේ **Live demo** පේළියට දාන්න.

**Free plan එක ගැන දැනගන්න:**

- විනාඩි 15ක් කවුරුත් පාවිච්චි නොකළොත් server එක නිදි යනවා. ඊළඟ වතාවේ විවෘත වෙන්න තත්පර 30–50ක් යනවා.
- Deploy කරන හැම වතාවකම database එක අලුතින් හැදෙනවා. ඒ නිසා මේක **demo එකට පමණයි**.
- සැබෑ වැඩවලට (ඔබ හෝ වෙනත් advisors) Hostinger වගේ **MySQL** තියෙන hosting එකක් ගන්න (මාසෙට රු. 600–1,500 පමණ). උපදෙස් පහළින් තියෙනවා.

### සැබෑ භාවිතයට — Shared hosting (Hostinger වගේ)

1. hPanel → **Databases → MySQL** → database එකක් හදන්න.
2. File Manager එකට project එක upload කරලා, domain එක `public` folder එකට point කරන්න.
3. `.env` එකේ මේවා සකසන්න:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://ඔබේ-domain`
   - `DB_CONNECTION=mysql` සහ DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD
   - `SESSION_SECURE_COOKIE=true`
   - `DEMO_ENABLED=false`
4. SSH: `php artisan migrate --force`
5. **Cron job:** `* * * * * cd ~/rakawarana && php artisan schedule:run`
6. **SSL (https)** සක්‍රිය කරන්න. hPanel එකේ නොමිලේ තියෙනවා.

### Daily digest e-mail (අත්‍යවශ්‍ය නැහැ)

- `.env` එකේ `MAIL_*` settings දාන්න. උදාහරණයක් ලෙස, Gmail App Password එකක් දාලා `MAIL_MAILER=smtp` කරන්න.
- එතකොට සෑම උදේ 7ට, Settings → Profile එකේ "daily e-mail" සක්‍රිය කරපු අයට අද කළ යුතු දේ e-mail එකෙන් එනවා.

---

## 4. LinkedIn

1. **Profile → Add section → Projects:**
   - Name: `Rakawarana — CRM for Insurance Advisors`
   - Skills: Laravel, PHP, Tailwind CSS, Alpine.js, MySQL, Git
   - Project URL: Render demo link එක (නැත්නම් GitHub link එක)
2. **Skills:** Laravel, PHP, Tailwind CSS, Alpine.js
3. **Post:** `docs/LINKEDIN-POST.md` එකේ ඇති text එක copy කරලා, screenshots 3–4ක් සමඟ post කරන්න:
   - Dashboard එක
   - Phone එකේ "අද ලැයිස්තුව"
   - Pipeline එක
   - Dark mode එක

---

## ප්‍රශ්න ආවොත්

| ප්‍රශ්නය | විසඳුම |
|---|---|
| `composer` / `php` not recognized | Laragon terminal එකෙන්ම run කරන්න (සාමාන්‍ය cmd එකෙන් නෙවෙයි) |
| `Your PHP version does not satisfy… 8.3` | 1.1 කොටසේ 3 පියවර බලන්න |
| `Vite manifest not found` | `npm run build` run කරන්න |
| Page එක සුදු / 500 error | `storage/logs/laravel.log` එකේ අන්තිම පේළි මට එවන්න |
| Login වුණාට පස්සේ ආපහු login page එකට එනවා | `.env` එකේ `SESSION_SECURE_COOKIE=false` (localhost එකේදී) |
