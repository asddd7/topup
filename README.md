# DIMTOPUP

DIMTOPUP adalah platform top-up game berbasis Laravel untuk mengelola katalog game, item top-up, bundle, promo, pembayaran, order, dan fulfillment provider.

## Fitur

- Checkout untuk user login dan guest.
- Pemilihan beberapa item sekaligus dengan qty per item.
- Bundle item dengan perhitungan komponen dan stock.
- Validasi player dan server untuk produk yang membutuhkan data MooGold.
- Pembayaran melalui Midtrans Snap.
- Sinkronisasi channel pembayaran aktif dari Midtrans.
- Pengelolaan metode pembayaran manual dan toggle aktif/nonaktif.
- Voucher, promo otomatis, promo metode pembayaran, flash sale, dan promo user baru.
- Batas kuota promo global dan per user.
- Dashboard admin untuk game, item, kategori, stock, banner, order, payment, promo, dan activity log.
- Provider fulfillment MooGold dan Ditusi.
- Riwayat transaksi provider.
- Notifikasi admin untuk order baru dan stock rendah.
- Maintenance mode dengan akses khusus admin.
- REST API versi 1 menggunakan Laravel Sanctum.

## Teknologi

- PHP 8.3+
- Laravel 13
- MySQL
- Laravel Sanctum
- Laravel Queue dengan database driver
- Midtrans Snap
- MooGold API
- Ditusi API
- Bootstrap 5
- Vite
- Pest

## Persyaratan

Pastikan perangkat sudah memiliki:

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- MySQL
- PHP extension yang dibutuhkan Laravel, termasuk `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo`, dan `tokenizer`

## Instalasi Lokal

1. Clone repository dan masuk ke folder proyek.

```bash
git clone <repository-url> topup
cd topup
```

2. Install dependency PHP.

```bash
composer install
```

3. Buat file environment.

```bash
copy .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

4. Generate application key.

```bash
php artisan key:generate
```

5. Buat database MySQL, lalu isi koneksi database pada `.env`.

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=topup
DB_USERNAME=root
DB_PASSWORD=
```

6. Jalankan migration.

```bash
php artisan migrate
```

Jika tersedia seeder:

```bash
php artisan db:seed
```

7. Buat symbolic link storage.

```bash
php artisan storage:link
```

8. Install dan build asset frontend.

```bash
npm install
npm run build
```

## Menjalankan Aplikasi

Untuk server web sederhana:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Untuk development lengkap dengan server, queue, log, dan Vite:

```bash
composer run dev
```

Atau jalankan proses secara terpisah:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
php artisan pail
npm run dev
```

## Konfigurasi Environment

### Aplikasi dan database

```dotenv
APP_NAME=Topup
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=topup
DB_USERNAME=root
DB_PASSWORD=
```

### Session, cache, dan queue

Proyek menggunakan database untuk session, cache, dan queue secara default.

```dotenv
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Pastikan migration tabel `sessions`, `cache`, dan `jobs` sudah dijalankan.

### Midtrans

```dotenv
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_MERCHANT_ID=
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
```

Midtrans digunakan untuk:

- Mengambil channel pembayaran aktif.
- Membuat transaksi Snap.
- Menampilkan halaman pembayaran.
- Menerima webhook status transaksi.
- Mengubah status order setelah pembayaran terverifikasi.

Jangan commit `MIDTRANS_SERVER_KEY` atau credential provider ke repository.

### MooGold

```dotenv
MOOGOLD_BASE_URL=https://moogold.com/wp-json/v1/api
MOOGOLD_PARTNER_ID=
MOOGOLD_SECRET_KEY=
MOOGOLD_TIMEOUT=120
```

MooGold digunakan untuk sinkronisasi produk, mengambil server list, validasi player, membuat order, dan membaca saldo/order detail.

### Ditusi

```dotenv
DITUSI_BASE_URL=https://api.ditusi.co.id/api/dev/v1
DITUSI_CLIENT_ID=
DITUSI_CLIENT_KEY=
DITUSI_WEBHOOK_TOKEN=
DITUSI_TIMEOUT=30
```

Gunakan endpoint sandbox saat development dan credential production hanya di environment production.

## Akun dan Akses

Area admin dilindungi middleware role administrator dan tersedia pada prefix `/admin`.

Login admin:

```text
/admin/login
```

Saat maintenance mode aktif, user biasa tidak dapat masuk ke halaman publik atau checkout. Admin tetap dapat login dan mengakses dashboard.

Role utama:

- `role_id = 1`: administrator
- `role_id = 2`: user

## Alur Order

1. User memilih game.
2. User memilih satu atau beberapa item.
3. User menentukan qty masing-masing item.
4. Sistem memvalidasi item, bundle, stock, player data, dan payment type.
5. Sistem menghitung promo dan total order.
6. Order dibuat dengan status `Waiting Payment`.
7. User diarahkan ke Midtrans Snap.
8. Webhook Midtrans memproses status pembayaran.
9. Order yang sudah `Paid` diproses oleh fulfillment service.
10. Provider MooGold atau Ditusi menjalankan top-up.
11. Stock, promo usage, provider history, dan notifikasi diperbarui.

## Pengaturan Admin

Halaman `/admin/setting` mengatur:

- Nama website
- Logo dan favicon
- WhatsApp, email, dan alamat
- Social media
- Maintenance mode
- Guest checkout
- Pemeriksaan saldo MooGold

Jika `allow_guest_checkout` dimatikan, checkout guest ditolak di server. Jika `maintenance` aktif, hanya admin yang tetap dapat mengakses panel.

## Pembayaran

Channel pembayaran Midtrans dapat diambil dari merchant preferences melalui halaman admin payment.

Gunakan tombol **Sinkron Midtrans** pada halaman:

```text
/admin/payment
```

Status payment dapat diubah dengan toggle aktif/nonaktif. User hanya melihat channel yang aktif, dan server juga menolak payment type yang sudah nonaktif.

## API

API tersedia di prefix:

```text
/api/v1
```

Kelompok endpoint utama:

- User API untuk game, item, dan order.
- Admin API untuk pengelolaan order dan katalog.
- Midtrans webhook dan Snap.
- Integrasi MooGold.
- Integrasi Ditusi.

Gunakan Sanctum sesuai konfigurasi autentikasi API proyek.

## Testing dan Validasi

Jalankan unit/feature test:

```bash
php artisan test
```

Atau melalui script Composer:

```bash
composer run test
```

Validasi Blade:

```bash
php artisan view:cache
```

Build frontend:

```bash
npm run build
```

Pemeriksaan whitespace Git:

```bash
git diff --check
```

## Struktur Direktori Penting

```text
app/
  Http/Controllers/
  Integrations/
    Ditusi/
    Midtrans/
    MooGold/
  Models/
  Observers/
  Services/
    TopUp/
config/
database/
  migrations/
  seeders/
public/
  assets/
resources/
  js/
  views/
routes/
  web.php
  api.php
storage/
tests/
```

## Queue Worker

Fulfillment provider diproses melalui queue. Pada development, jalankan:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

Pada production, gunakan process manager seperti Supervisor atau service worker platform hosting agar queue tetap berjalan.

## Keamanan

- Jangan commit file `.env`.
- Jangan membagikan server key Midtrans, secret MooGold, atau credential Ditusi.
- Gunakan HTTPS di production.
- Pastikan webhook provider memakai signature/token validation.
- Set `APP_DEBUG=false` di production.
- Gunakan database dan queue production yang terpisah dari development.

## Catatan Deployment

Sebelum deployment:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm install
npm run build
```

Pastikan worker queue, scheduler jika digunakan, storage publik, webhook provider, dan environment variables sudah dikonfigurasi di server.
