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

## Langkah 3: Upload 2 Zip (File Manager, tanpa FileZilla!)

File zip **sudah jadi** di folder `deploy/` lokal kamu:

```
deploy/
├── code.zip          (0.2 MB)  ← app, public/build, artisan, .htaccess
└── vendor-part-1.zip (6.5 MB)  ← vendor production (tanpa dev tools)
```

> Limit InfinityFree: **10 MB per file** (berlaku di File Manager & FTP).
> Karena itu semua dipecah jadi zip < 10 MB. File `.php` juga max 1 MB (semua aman).

Cara upload:

1. Buka **File Manager** dari dashboard InfinityFree (folder `htdocs/`)
2. **Drag & drop** `code.zip` dan `vendor-part-1.zip` dari Explorer ke File Manager
3. Klik kanan `code.zip` → **Extract** → OK
4. Klik kanan `vendor-part-1.zip` → **Extract** → OK
5. **Hapus** kedua zip setelah extract (hemat quota)

Hasil setelah extract, `htdocs/` berisi:

```
htdocs/
├── app/  bootstrap/  config/  database/  public/  resources/  routes/  storage/
├── vendor/
├── .htaccess      ← otomatis ikut dari code.zip
├── artisan
├── composer.json
└── composer.lock
```

---

## Langkah 4: Verify .htaccess

`.htaccess` otomatis ikut dari `code.zip` (sudah di-test lokal di Apache). Pastikan ada di **root `htdocs/`** (bukan di `public/`), isinya:

```apache
RewriteEngine On
RewriteCond %{DOCUMENT_ROOT}/public%{REQUEST_URI} -f
RewriteRule ^(.*)$ public/$1 [L]
RewriteCond %{REQUEST_URI} !^/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [L]
```

> Kalau File Manager bikin file `.htaccess` hidden, aktifkan **Show Hidden Files** di setting File Manager.

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
| Class not found | `vendor/` belum ke-extract — extract ulang `vendor-part-1.zip` |
| CSS/JS hilang | `public/build/` belum ada — jalankan `npm run build` lokal, zip ulang `code.zip` |
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
