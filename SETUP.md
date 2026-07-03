# Yumpreneur — Setup & Improvement Tracker

**Product:** Eorder v4.0 — Multi-tenant Restaurant/Food Ordering SaaS  
**Developer:** KreativDev (Envato/CodeCanyon)  
**Version:** 4.0 (released 16 Nov 2025)  
**Framework:** Laravel 9.x / PHP 8.2  
**Local URL:** http://yumpreneur.local  
**Admin URL:** http://yumpreneur.local/admin (user: `admin` / pass: `admin` — change immediately)  
**GitHub:** https://github.com/yensmi/yumpreneur  

---

## Table of Contents

1. [Local Setup](#1-local-setup)
2. [GitHub Setup](#2-github-setup)
3. [Production Setup](#3-production-setup)
4. [Improvements Applied](#4-improvements-applied)
5. [TODO List](#5-todo-list)
6. [Notes & Decisions](#6-notes--decisions)

---

## 1. Local Setup

### Environment
- **Laragon** (Apache + PHP 8.2 + MySQL 8.4)
- **Database:** `yumpreneur_local` (MySQL, root user, no password)
- **Virtual host:** auto-configured by Laragon at `X:\laragon\etc\apache2\sites-enabled\auto.yumpreneur.local.conf`
- **Drive note:** X: is exFAT — no NTFS symlinks/junctions. `php artisan storage:link` does not work; app uses `public/assets/` directly for uploads (no issue in practice).

### Steps Completed
- [x] Copied Eorder v4.0 installable files to `X:\laragon\www\yumpreneur.local`
- [x] Created database `yumpreneur_local` in MySQL
- [x] Imported `public/installer/database.sql` (92 tables)
- [x] Updated `.env`:
  - `APP_NAME=Yumpreneur`
  - `APP_URL=http://yumpreneur.local`
  - `WEBSITE_HOST=yumpreneur.local`
  - `DB_DATABASE=yumpreneur_local`
  - `DB_PASSWORD=` (empty)
- [x] Updated `.gitignore` (added `/vendor`, `.env`, logs, cache, IDE files, `__MACOSX`, `*.zip`)
- [x] Ran `php artisan config:clear`
- [x] Apache virtual host confirmed at `http://yumpreneur.local`

### Post-Setup Steps (manual)
- [ ] Visit http://yumpreneur.local/admin and change admin password
- [ ] Delete `public/installer/` folder (remove installer after setup)
- [ ] Configure email settings in Admin → Settings → Email Settings
- [ ] Set currency in Admin → Settings → General Settings
- [ ] Configure payment gateways as needed

---

## 2. GitHub Setup

**Repo:** https://github.com/yensmi/yumpreneur  
**Branch:** `main`

### Steps to complete (run in project root terminal)
```bash
git remote add origin https://github.com/yensmi/yumpreneur.git
git push -u origin main
```

### Branching strategy
- `main` — production-ready code only
- `develop` — integration branch for features/improvements
- Feature branches: `feat/brevo-email`, `feat/field-masking`, etc.

---

## 3. Production Setup

### Infrastructure Plan
| Component | Service |
|-----------|---------|
| App server | Digital Ocean Droplet (Ubuntu 22.04, 2GB RAM min) |
| Database | DO Managed MySQL (separate from app) |
| File storage | DO Spaces (S3-compatible) — set as `s3` disk in filesystems |
| Web server | Nginx (managed by Laravel Forge) |
| SSL | Let's Encrypt via Forge (main domain + wildcard for tenant subdomains) |
| Deployment | Laravel Forge + GitHub auto-deploy on `main` push |

### DO Spaces (S3) Configuration
In `.env` (production):
```
FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=<do_spaces_key>
AWS_SECRET_ACCESS_KEY=<do_spaces_secret>
AWS_DEFAULT_REGION=<region e.g. sgp1>
AWS_BUCKET=<bucket-name>
AWS_URL=https://<bucket-name>.<region>.digitaloceanspaces.com
AWS_ENDPOINT=https://<region>.digitaloceanspaces.com
```

In `config/filesystems.php`, add endpoint to s3 disk:
```php
's3' => [
    'driver' => 's3',
    'key'    => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url'    => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => true,
],
```

### Wildcard Subdomain (tenant subdomains)
- Add `*.yourdomain.com` wildcard DNS record pointing to Droplet IP
- Use Let's Encrypt wildcard cert (requires DNS challenge via Forge or certbot)
- Alternatively: purchase Wildcard SSL from Namecheap/Sectigo

### Laravel Forge Deployment Script
```bash
cd /home/forge/yourdomain.com
git pull origin main
$FORGE_PHP artisan migrate --force
$FORGE_PHP artisan config:cache
$FORGE_PHP artisan route:cache
$FORGE_PHP artisan view:cache
$FORGE_PHP artisan storage:link
```

### Forge Shared Paths
Add `public/assets` to Forge's linked directories so uploads persist across deployments:
- Linked path: `public/assets`
- Ensure `forge:forge` ownership: `chown -R forge:forge /home/forge/[domain]/public/assets`

### Cron Job (via Forge Scheduler)
```
wget {your_website_url}/subcheck
```
Schedule: Once per day (`0 0 * * *`)

### Checklist Before Going Live
- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Set strong `APP_KEY` (generate fresh for production)
- [ ] Configure DO Managed MySQL credentials in `.env`
- [ ] Configure DO Spaces credentials in `.env`
- [ ] Configure Pusher keys for live orders / push notifications
- [ ] Set up Brevo API key for transactional email
- [ ] Set up reCAPTCHA site/secret keys
- [ ] Delete installer folder on server
- [ ] Change admin password from default
- [ ] Set up Forge scheduler for subscription expiry cron
- [ ] Configure wildcard subdomain DNS
- [ ] Test subdomain creation after first tenant signs up

---

## 4. Improvements Applied

Improvements ported from the Webryday/Businesso v6 sister project. Since Eorder (KreativDev) shares a similar structure, improvements are adapted per codebase.

### ✅ Done

*(none yet — see TODO list)*

### 🔄 In Progress

*(see TODO list)*

---

## 5. TODO List

### Local Setup
- [x] Extract Eorder v4.0 to `X:\laragon\www\yumpreneur.local`
- [x] Create & import database (`yumpreneur_local`)
- [x] Update `.env` (APP_URL, WEBSITE_HOST, DB credentials)
- [x] Update `.gitignore`
- [x] Initialize git repo + initial commit
- [ ] Create GitHub repo (`yensmi/yumpreneur`) and push
- [ ] Delete `public/installer/` folder
- [ ] Change admin default password

### Improvements — Email
- [ ] **Brevo transactional email API** — Add Brevo as 3rd mail provider (is_smtp=2)
  - Files: `app/Http/Controllers/Controller.php`, `Admin/EmailController.php`, views/admin/basic/email/mail_from_admin.blade.php
- [ ] **Test Email button** — "Send Test Email" in Admin → Mail From Admin
  - Files: `Admin/EmailController.php`, views, routes/admin.php

### Improvements — Security
- [ ] **Sensitive field masking** — Secret keys render as empty `value=""` in admin forms
  - Fields: Google reCAPTCHA secret, OpenAI key, Gemini key, PayPal client_id
  - Files: views/admin/basic/scripts.blade.php, views/admin/gateways/index.blade.php, Admin/BasicController.php, Admin/GatewayController.php
- [ ] **reCAPTCHA inheritance** — New tenants inherit admin reCAPTCHA keys on registration
  - Files: `Admin/RegisterUserController.php`, `Front/CheckoutController.php`

### Improvements — Frontend Bug Fixes
- [ ] **reCAPTCHA double-load fix** — Wrap `NoCaptcha::renderJs()` with `@once` directive
  - Files: footer.blade.php, contact.blade.php (check which views exist in Eorder)
- [ ] **Nav menu hover shift fix** — Remove padding from `:hover` CSS rules
  - Files: `resources/views/user-front/partials/styles.blade.php` (check Eorder themes)
- [ ] **Contact form button shape fix** — Per-theme CSS override for `.contact-form .main-btn`
  - Files: `resources/views/user-front/partials/styles.blade.php`

### Improvements — Tenant Onboarding
- [ ] **Default preloader & profile photo** — New tenants get branded GIF/PNG defaults
  - Files: `Admin/RegisterUserController.php`, `Front/CheckoutController.php`
  - Assets needed: `public/assets/front/img/defaults/yumpreneur-preloader.gif`, `yumpreneur-photo.png`

### Production
- [ ] Create Digital Ocean Droplet (Ubuntu 22.04, 2GB RAM)
- [ ] Create DO Managed MySQL database
- [ ] Create DO Spaces bucket
- [ ] Add server to Laravel Forge
- [ ] Configure domain + wildcard DNS
- [ ] Set up SSL (Let's Encrypt or Wildcard SSL)
- [ ] Configure Forge deployment script
- [ ] Set Forge shared path for `public/assets`
- [ ] Set up Forge scheduler (subcheck cron)
- [ ] Push `main` branch and trigger first deploy
- [ ] Run post-deploy checklist

---

## 6. Notes & Decisions

### exFAT Drive Limitation
The X: drive is **exFAT** format, which does not support NTFS symlinks or junctions. `php artisan storage:link` silently fails. This is a local-dev-only issue — the app stores uploads in `public/assets/` via `public_path()` directly, so the missing symlink has no functional impact locally. On the production Forge server (Linux ext4/NTFS equivalent), `storage:link` will work normally.

### DO Spaces vs AWS S3
DO Spaces is S3-compatible. The app already has S3 config in `basic_settings` (columns: `aws_access_key_id`, `aws_secret_access_key`, `aws_default_region`, `aws_bucket`). We need to add `endpoint` to `config/filesystems.php` for DO Spaces compatibility (the S3 driver requires explicit endpoint for non-AWS providers).

### Eorder vs Webryday Codebase Differences
Improvements from the Webryday/Businesso v6 reference document need adaptation:
- **Eorder (KreativDev)** — uses `is_smtp` flag in `basic_settings` for mail provider
- **Webryday (UX-Theme)** — similar structure; improvements are portable with minor path changes
- Always check actual file paths before applying — controller names differ between products
- Eorder themes (for CSS fixes) need to be identified before applying nav/button fixes

### Installer Folder
The `public/installer/` folder must be deleted after setup. It contains the `database.sql` and CSS/font files for the web installer. Leaving it in place is a security risk (anyone can re-run the installer).
