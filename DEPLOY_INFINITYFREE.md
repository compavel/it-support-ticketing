# Deploy IT Support Ticketing ke InfinityFree

Cara yang sama dengan ScreenTime (sudah terbukti jalan): shared hosting free, PHP 8.3 + MySQL, tanpa kartu kredit.

**Target:** `http://ticketing.infinityfree.io`

---

## Langkah 1: Buat Hosting Account

1. Login ke https://panel.infinityfree.com
2. **Create Account**
3. Pilih **Free Subdomain** → ketik `ticketing.infinityfree.io`
4. **Create** → catat:
   - Username (misal `if0_12345678`)
   - Domain: `ticketing.infinityfree.io`

---

## Langkah 2: Buat Database

1. Di dashboard → **MySQL Databases**
2. Buat database baru, misal:
   - Database: `if0_12345678_ticketing`
   - Username: `if0_12345678_admin`
   - Password: bikin sendiri (catat!)
3. **Catat DB Host** dari dashboard (misal `sql309.infinityfree.com`) — **bukan localhost!**

---

## Langkah 3: Siapkan File di Lokal

Dari folder project lokal `it-support-ticketing/` (yang sudah ada `vendor/` dan `public/build/`):

1. Pastikan assets ter-build:
```bash
npm run build
```

2. Yang akan di-upload ke `htdocs/` (via FileZilla ke `ftpupload.net`):

```
htdocs/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← termasuk public/build/
├── resources/
├── routes/
├── storage/
├── vendor/          ← WAJIB, 9000+ file
├── artisan
├── composer.json
└── composer.lock
```

**JANGAN di-upload:** `node_modules/`, `.git/`, `tests/`, `.env`, `Dockerfile`, `render.yaml`

> Upload `vendor/` via **FileZilla/FTP**, bukan File Manager (lebih stabil).

---

## Langkah 4: Buat .htaccess di Root htdocs

Buat file `.htaccess` di `htdocs/` (satu level DI ATAS `public/`):

```apache
RewriteEngine On
RewriteRule ^storage/(.*)$ /storage/app/public/$1 [END]
RewriteRule (.*) /public/$1 [L]
```

---

## Langkah 5: Buat .env di htdocs

Buat file `htdocs/.env`:

```env
APP_NAME="IT Support Ticketing"
APP_ENV=production
APP_KEY=base64:GENERATE_SENDIRI_LIHAT_BAWAH
APP_DEBUG=false
APP_URL=http://ticketing.infinityfree.io

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=sql309.infinityfree.com
DB_PORT=3306
DB_DATABASE=if0_12345678_ticketing
DB_USERNAME=if0_12345678_admin
DB_PASSWORD=password_db_kamu

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SETUP_KEY=acak-panjang-biar-aman
```

**Ganti:**
- `APP_KEY`: jalankan `php artisan key:generate --show` di lokal (output-nya `base64:...`)
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: dari dashboard InfinityFree
- `SETUP_KEY`: string acak bebas (misal hasil Google "random string generator", 20+ karakter)

---

## Langkah 6: Set Permissions

Di File Manager (atau FTP):
1. Folder `storage/` → permissions **775** (recursive)
2. Folder `bootstrap/cache/` → permissions **775** (recursive)

---

## Langkah 7: Jalankan Setup

Buka di browser:

```
http://ticketing.infinityfree.io/setup/ISI_SETUP_KEY_KAMU
```

Harus muncul: **"Setup complete! ..."** — ini menjalankan migrasi + seed akun demo.

> Gagal/timeout? Coba lagi (migrasi kecil, biasanya < 30 detik).

---

## Langkah 8: AMANKAN (WAJIB!)

Setelah "Setup complete":

1. Edit `htdocs/.env`
2. **Hapus baris `SETUP_KEY=...`** → simpan
3. Buka `http://ticketing.infinityfree.io/login`

Kalau SETUP_KEY masih ada, orang lain bisa menjalankan ulang setup!

---

## Langkah 9: Test Login

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sekolah.sch.id | password |
| Teknisi | budi@sekolah.sch.id | password |
| User | siti@sekolah.sch.id | password |

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| 500 Internal Server Error | Cek `.env` (APP_KEY wajib ada!), `storage/` 775 |
| Class not found | `vendor/` tidak lengkap — upload ulang via FTP |
| CSS/JS hilang | `public/build/` belum ke-upload — `npm run build` dulu |
| SQLSTATE connection error | DB Host bukan `localhost`, cek lagi di dashboard |
| Setup jalan tapi DB kosong | `SETUP_KEY` di .env tidak match dengan URL |
| Halaman kosong | `APP_DEBUG=true` sementara di .env buat lihat error |

---

## Setelah Live

- `APP_DEBUG=false`
- `SETUP_KEY` sudah dihapus dari .env
- Simpan credentials di tempat aman (bukan di git!)

## Limitations (Free Hosting)

- No SSH / no artisan CLI dari server → setup lewat route browser
- MySQL limit ~50MB — cukup buat demo portfolio
- 30,000 hits/day — cukup buat recruiter klik-klik
- Cron jobs tidak tersedia → tidak masalah (queue sync)
