# Laravel GUI-Only Deployment Guide (cPanel, no SSH/Terminal)

This guide is for deploying this project using only cPanel GUI tools (Git Version Control, File Manager, phpMyAdmin). No SSH is required.

## 1) One-time local preparation

On your local machine (not cPanel), run:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

This generates production artifacts:
- `vendor/`
- `public/build/`

## 2) Choose your deployment style

### Option A (recommended): Git + File Manager artifacts

Use cPanel Git Version Control to pull code, and upload `vendor/` + `public/build/` from File Manager when they change.

### Option B: Zip upload each release

Build locally, zip the whole project with production artifacts, upload and extract in File Manager.

## 3) cPanel Git setup (GUI only)

1. Open **cPanel -> Git Version Control**.
2. Click **Create**.
3. Use your GitHub repository URL.
4. Choose a path outside `public_html`, for example:
   - `/home/USERNAME/repositories/sopnernir`
5. Select branch `develop` (or your production branch).
6. Finish clone.

For future releases:
1. Push from local to GitHub.
2. In cPanel Git tool, open repo.
3. Click **Pull or Deploy**.

## 4) Domain document root setup

### If host allows changing document root

Point your domain/subdomain document root to:
- `/home/USERNAME/repositories/sopnernir/public`

### If host does not allow document root change

1. Keep app in `/home/USERNAME/repositories/sopnernir`
2. Copy contents of `public/` to `public_html/`
3. Edit `public_html/index.php` and set absolute paths:

```php
require __DIR__.'/../repositories/sopnernir/vendor/autoload.php';
$app = require_once __DIR__.'/../repositories/sopnernir/bootstrap/app.php';
```

Adjust path depth based on your actual folder structure.

**CSS/JS (Vite `public/build`)**

The browser loads `/build/assets/...` from **public_html**, but Git stores build files under the app’s `public/build/`. If those are missing or out of date in `public_html`, the site looks like plain HTML.

**Option A (recommended): symlink**

```bash
rm -rf ~/public_html/build
ln -s ~/repositories/sopnernir/public/build ~/public_html/build
```

**Option B: copy on each deploy**

```bash
rsync -av --delete ~/repositories/sopnernir/public/build/ ~/public_html/build/
```

**Option C: Laravel fallback (no copy)**

If `public_html/build` does **not** exist, missing `/build/...` requests are handled by Laravel and served from `public/build/` in the app (see `ViteBuildAssetController`). Remove a broken `public_html/build` folder if it contains old files:

```bash
rm -rf ~/public_html/build
php artisan route:clear
php artisan route:cache
```

### Profile photos (required when using public_html)

Uploads are stored on disk under `public/media`, but the browser loads `/media/...` from **public_html**. Without fixing this, profile photos look broken.

**Option A (recommended): symlink**

```bash
rm -rf ~/public_html/media
ln -s ~/repositories/sopnernir/public/media ~/public_html/media
chmod -R u+rwX ~/repositories/sopnernir/public/media
```

**Option B: store files directly in public_html**

In `.env`:

```env
WEB_PUBLIC_ROOT=/home/USERNAME/public_html/media
```

Then create the folder and make it writable:

```bash
mkdir -p ~/public_html/media
chmod -R u+rwX ~/public_html/media
```

If you already uploaded photos to the app `public/media` folder, copy them once:

```bash
cp -a ~/repositories/sopnernir/public/media/. ~/public_html/media/
```

## 5) Configure `.env` in File Manager

In your app root, create/edit `.env`:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- database credentials (`DB_*`)
- mail credentials (`MAIL_*`)

Also set:
- `LOG_CHANNEL=stack`

## 6) Database setup in GUI

1. Open **cPanel -> MySQL Databases** and create database/user.
2. Grant all privileges to that user.
3. Open **phpMyAdmin** and import your SQL dump.
4. Update `.env` DB settings to match.

If you cannot run migrations, every schema change must be imported manually via SQL.

## 7) Upload/update build artifacts from File Manager

Because cPanel cannot run Composer/NPM for you:

- Upload `vendor/` when PHP dependencies change.
- Upload `public/build/` when frontend changes.

If you use Git pull on server, these folders are usually ignored and must be uploaded manually.

## 8) Permissions (GUI)

Using File Manager permissions:
- `storage/` -> writable
- `bootstrap/cache/` -> writable
- `public/media/` or `public_html/media/` -> writable (profile and nominee photos; see §4)

Typical baseline:
- folders: `755`
- files: `644`

## 9) Required actions you must request from host support

Without terminal access, ask support to run these once (or per release if needed):

```text
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If they cannot run commands:
- app may still run,
- but storage symlink and migrations must be handled manually.

## 10) Support ticket template

Use this message in your hosting support ticket:

```text
Hello, I am hosting a Laravel application and I do not have SSH/Terminal access.
Please run these commands in my project path:

1) php artisan key:generate
2) php artisan migrate --force
3) php artisan storage:link
4) php artisan config:cache
5) php artisan route:cache
6) php artisan view:cache

Project path: /home/USERNAME/repositories/sopnernir
Domain: https://your-domain.com

Please confirm after execution and share any errors.
Thank you.
```

## 11) Release checklist (GUI-only)

1. Build locally (`composer --no-dev`, `npm run build`)
2. Push code to GitHub
3. cPanel Git -> Pull
4. File Manager -> upload changed `vendor/` and/or `public/build/`
5. Ensure `.env` is correct
6. Ask support to run artisan commands (if needed)
7. Verify:
   - login page loads correctly
   - phone access works
   - investment pages load
   - logs write to `storage/logs/laravel.log`

