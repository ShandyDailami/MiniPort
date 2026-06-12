# MiniPort Cloud

MiniPort Cloud adalah platform mini **Infrastructure as a Service (IaaS)** berbasis web yang berfokus pada layanan **Object Storage**. Project ini menggunakan **Laravel** sebagai aplikasi utama dan **MiniStack / LocalStack** sebagai emulator layanan AWS S3. Dengan MiniPort, pengguna dapat membuat bucket, mengunggah file, mengelola object, membuat temporary share link, memantau kuota storage, dan melihat log aktivitas melalui dashboard.

Project ini dibuat untuk project akhir mata kuliah Komputasi Awan.

---

## Fitur Utama

### 1. Authentication

- Register user.
- Login user.
- Logout user.
- Proteksi halaman menggunakan middleware `auth`.

### 2. API Key / Credential Management

- Generate access key dan secret key.
- Menampilkan credential aktif.
- Revoke API key.
- Secret key hanya ditampilkan sekali setelah generate.

### 3. Object Storage Service

MiniPort menyediakan layanan object storage berbasis bucket S3.

Fitur yang tersedia:

- Membuat bucket baru.
- Melihat daftar bucket milik user.
- Membuka detail bucket.
- Melihat daftar object/file di dalam bucket.
- Upload file ke bucket.
- Download file dari bucket.
- Delete file/object dari bucket.
- Delete bucket beserta seluruh object di dalamnya.
- Temporary share link / presigned URL untuk membagikan file sementara.

### 4. Storage Quota

- Setiap user memiliki batas storage.
- Upload ditolak jika ukuran file membuat penggunaan storage melebihi limit.
- Dashboard menampilkan storage terpakai, sisa storage, limit, dan persentase penggunaan.

### 5. Dashboard Monitoring

Dashboard menampilkan:

- Total bucket.
- Total object/file.
- Total storage terpakai.
- Sisa storage.
- Limit storage.
- Persentase penggunaan storage.
- Credential aktif.
- Bucket terbaru.
- Log aktivitas terakhir.

### 6. Activity Log / Audit Trail

Sistem mencatat aktivitas penting user, seperti:

- Generate credential.
- Revoke credential.
- Create bucket.
- Upload object.
- Download object.
- Delete object.
- Share object.
- Delete bucket.

---

## Teknologi yang Digunakan

- Laravel 13
- PHP 8.4 container
- MySQL 8.0
- MiniStack / LocalStack
- AWS SDK for PHP
- Docker Desktop
- Docker Compose
- WSL2 Ubuntu
- Vite
- Tailwind CSS

---

## Arsitektur Docker

Project ini berjalan menggunakan tiga container utama:

| Service | Fungsi | Port Default |
|---|---|---|
| `laravel.test` | Aplikasi Laravel | `8080:8080` |
| `miniport-db` | Database MySQL | `3306:3306` |
| `ministack` | Emulator AWS S3/SQS/SNS | `4566:4566` |

Alur komunikasi internal Docker:

```txt
Laravel Container -> MySQL       : miniport-db:3306
Laravel Container -> MiniStack   : ministack:4566
Browser Host      -> Laravel     : localhost:8080
Browser Host      -> MiniStack   : localhost:4566
```

**Penting:**

- Backend Laravel harus memakai endpoint internal `http://ministack:4566`.
- Browser atau presigned URL harus memakai endpoint public `http://localhost:4566` atau `http://127.0.0.1:4566`.

---

## Prasyarat

Pastikan sudah terinstall:

- Docker Desktop
- WSL2 Ubuntu
- Git
- VS Code (opsional tapi disarankan)

Cek versi WSL:

```bash
wsl -l -v
```

Pastikan Ubuntu berjalan dengan VERSION 2.

Cek Docker dari terminal Ubuntu WSL:

```bash
docker --version
docker compose version
docker run hello-world
```

Jika command Docker tidak terbaca di WSL, aktifkan integrasi WSL di Docker Desktop:

- Docker Desktop > Settings > Resources > WSL Integration > Enable Ubuntu

---

## Cara Menjalankan Project

### 1. Clone Repository

Jalankan dari terminal Ubuntu WSL:

```bash
cd ~
git clone -b tunnel https://github.com/ShandyDailami/MiniPort.git
cd MiniPort
```

Disarankan menyimpan project di filesystem Linux, misalnya `~/MiniPort`, bukan di `/mnt/c` atau `/mnt/d`, agar Docker dan Laravel lebih stabil.

### 2. Buat File .env

```bash
cp .env.example .env
nano .env
```

Atur konfigurasi penting berikut:

```env
APP_NAME=MiniPort
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=miniport-db
DB_PORT=3306
DB_DATABASE=miniport
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

AWS_ACCESS_KEY_ID=test
AWS_SECRET_ACCESS_KEY=test
AWS_DEFAULT_REGION=ap-southeast-1
AWS_USE_PATH_STYLE_ENDPOINT=true

MINISTACK_ENDPOINT=http://ministack:4566
MINISTACK_PUBLIC_ENDPOINT=http://localhost:4566
MINIPORT_DEFAULT_STORAGE_LIMIT_MB=50
```

**Keterangan:**

- `MINISTACK_ENDPOINT` digunakan Laravel container untuk mengakses MiniStack.
- `MINISTACK_PUBLIC_ENDPOINT` digunakan untuk membuat share link yang bisa dibuka dari browser.
- `MINIPORT_DEFAULT_STORAGE_LIMIT_MB` adalah batas storage default per user.

### 3. Install Dependency Laravel

Gunakan Composer dari container agar tidak perlu install Composer lokal:

```bash
docker run --rm -u "$(id -u):$(id -g)" \
  -v "$PWD":/app \
  -w /app \
  composer:2 install
```

### 4. Install dan Build Frontend

```bash
docker run --rm -u "$(id -u):$(id -g)" \
  -v "$PWD":/app \
  -w /app \
  node:22 npm install

docker run --rm -u "$(id -u):$(id -g)" \
  -v "$PWD":/app \
  -w /app \
  node:22 npm run build
```

### 5. Jalankan Container

```bash
docker compose up -d
```

Cek status container:

```bash
docker compose ps
```

Minimal harus ada:

- `laravel.test`
- `miniport-db`
- `ministack`

### 6. Generate Application Key

```bash
docker compose exec laravel.test php artisan key:generate
```

Jika muncul error permission pada `.env` atau `storage/logs`, jalankan:

```bash
sudo chown -R $USER:$USER .
mkdir -p storage/logs bootstrap/cache
touch storage/logs/laravel.log
chmod 666 .env
chmod -R 777 storage bootstrap/cache
```

Lalu ulangi:

```bash
docker compose exec laravel.test php artisan key:generate
```

### 7. Jalankan Migrasi Database

```bash
docker compose exec laravel.test php artisan migrate
```

Jika ingin reset database saat development:

```bash
docker compose exec laravel.test php artisan migrate:fresh
```

### 8. Bersihkan Cache Laravel

```bash
docker compose exec laravel.test php artisan optimize:clear
```

### 9. Akses Aplikasi

Buka browser:

- `http://localhost:8080`

Halaman register:

- `http://localhost:8080/register`

Alur awal penggunaan:

1. Register akun.
2. Login.
3. Buka menu API Keys.
4. Generate API Key.
5. Buat bucket.
6. Upload file ke bucket.
7. Coba download, delete, share link, dan cek dashboard.

---

## Cara Cek MiniStack / S3

Cek health MiniStack:

```bash
curl http://localhost:4566/_localstack/health
```

Melihat daftar bucket:

```bash
docker compose exec ministack awslocal s3 ls
```

Melihat isi bucket:

```bash
docker compose exec ministack awslocal s3 ls s3://NAMA_BUCKET --recursive
```

Upload file manual untuk debug:

```bash
docker compose exec ministack awslocal s3 cp file.txt s3://NAMA_BUCKET/file.txt
```

Hapus isi bucket manual:

```bash
docker compose exec ministack awslocal s3 rm s3://NAMA_BUCKET --recursive
```

Hapus bucket manual:

```bash
docker compose exec ministack awslocal s3 rb s3://NAMA_BUCKET
```

---

## Route Utama

| Method | Path | Fungsi |
|--------|------|--------|
| GET | `/` | Dashboard |
| GET | `/register` | Form register |
| POST | `/register` | Proses register |
| GET | `/login` | Form login |
| POST | `/login` | Proses login |
| POST | `/logout` | Logout |
| GET | `/credentials` | Daftar API key |
| POST | `/credentials` | Generate API key |
| PATCH | `/credentials/{id}/revoke` | Revoke API key |
| GET | `/buckets` | Daftar bucket |
| GET | `/bucket/create` | Form create bucket |
| POST | `/bucket/create` | Proses create bucket |
| GET | `/bucket/{bucket}` | Detail bucket dan daftar object |
| DELETE | `/bucket/{bucket}` | Delete bucket beserta object |
| POST | `/bucket/{bucket}/objects` | Upload object |
| GET | `/bucket/{bucket}/objects/download` | Download object |
| DELETE | `/bucket/{bucket}/objects` | Delete object |
| GET | `/bucket/{bucket}/objects/share` | Generate temporary share link |

Cek route langsung:

```bash
docker compose exec laravel.test php artisan route:list
```

---

## Troubleshooting

### 1. Port 3306 Sudah Dipakai

Error contoh:

```
Bind for 0.0.0.0:3306 failed: port is already allocated
```

Biasanya karena MySQL dari Laragon/XAMPP sedang aktif.

Solusi cepat:

- Matikan MySQL Laragon/XAMPP, atau
- Ubah port host MySQL di `docker-compose.yml`.

Dari:

```yaml
ports:
  - '3306:3306'
```

Menjadi:

```yaml
ports:
  - '3307:3306'
```

Jangan ubah `.env` Laravel. Untuk koneksi antar-container tetap gunakan:

```
DB_HOST=miniport-db
DB_PORT=3306
```

### 2. Port 8080 Sudah Dipakai

Error contoh:

```
Bind for 0.0.0.0:8080 failed: port is already allocated
```

Solusi:

Ubah port Laravel di `docker-compose.yml`.

Dari:

```yaml
ports:
  - '8080:8080'
```

Menjadi:

```yaml
ports:
  - '8081:8080'
```

Lalu ubah `.env`:

```
APP_URL=http://localhost:8081
```

Akses aplikasi lewat:

- `http://localhost:8081`

### 3. Permission Denied pada .env atau storage/logs

Error contoh:

```
storage/logs/laravel.log could not be opened in append mode: Permission denied
file_put_contents(/var/www/html/.env): Permission denied
```

Solusi:

```bash
sudo chown -R $USER:$USER .
mkdir -p storage/logs bootstrap/cache
touch storage/logs/laravel.log
chmod 666 .env
chmod -R 777 storage bootstrap/cache
```

Kemudian:

```bash
docker compose exec laravel.test php artisan optimize:clear
```

### 4. Bucket Ada di Database tetapi Tidak Ada di MiniStack

Error contoh:

```
NoSuchBucket: The specified bucket does not exist
```

Cek daftar bucket:

```bash
docker compose exec ministack awslocal s3 ls
```

Jika bucket tidak ada di MiniStack, hapus record database dari aplikasi atau buat ulang bucket.

Untuk debug via Tinker:

```bash
docker compose exec laravel.test php artisan tinker
App\Models\Bucket::where('bucket_name', 'NAMA_BUCKET')->delete();
```

### 5. Share Link Tidak Bisa Dibuka

Jika link mengarah ke:

```
http://ministack:4566/...
```

itu salah untuk browser host. Pastikan `.env`:

```
MINISTACK_PUBLIC_ENDPOINT=http://localhost:4566
```

Jika muncul `SignatureDoesNotMatch`, coba gunakan:

```
MINISTACK_PUBLIC_ENDPOINT=http://127.0.0.1:4566
```

Lalu bersihkan cache:

```bash
docker compose exec laravel.test php artisan optimize:clear
```

Buat ulang share link.

---

## Perintah Log Debug

Log Laravel:

```bash
docker compose exec laravel.test tail -n 100 storage/logs/laravel.log
```

Follow log Laravel:

```bash
docker compose exec laravel.test tail -f storage/logs/laravel.log
```

Log container Laravel:

```bash
docker compose logs laravel.test --tail=100
```

Log MiniStack:

```bash
docker compose logs ministack --tail=100
```

Log MySQL:

```bash
docker compose logs miniport-db --tail=100
```

---

## Perintah Docker yang Sering Dipakai

Menjalankan container:

```bash
docker compose up -d
```

Melihat status container:

```bash
docker compose ps
```

Menghentikan container:

```bash
docker compose down
```

Menghentikan container dan menghapus volume:

```bash
docker compose down -v
```

> **Catatan:** `docker compose down -v` akan menghapus data MySQL dan MiniStack.

Rebuild / recreate container:

```bash
docker compose up -d --force-recreate
```

Masuk ke container Laravel:

```bash
docker compose exec laravel.test bash
```

---

## Struktur Project Singkat

```
MiniPort/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   ├── BucketController.php
│   │   ├── CredentialController.php
│   │   └── UserController.php
│   └── Models/
│       ├── Bucket.php
│       ├── Credential.php
│       ├── Log.php
│       └── User.php
├── database/migrations/
├── resources/views/
│   └── frontend/
│       ├── bucket/
│       ├── credential/
│       └── dashboard.blade.php
├── routes/
│   └── web.php
├── docker-compose.yml
├── composer.json
├── package.json
└── README.md
```

---

## Status Layanan IaaS

MiniPort saat ini berfokus pada layanan:

- **Storage-as-a-Service / Object Storage Service**

Fitur yang sudah berjalan:

- User dashboard.
- API key management.
- Bucket management.
- Object upload.
- Object listing.
- Object download.
- Object delete.
- Temporary share link.
- Storage quota.
- Global storage monitoring.
- Activity log.

Fitur yang belum termasuk:

- Virtual machine / compute service.
- Network service.
- Load balancer.
- Block storage.
- Database-as-a-Service.
- Billing nyata.

---

## Catatan Development

Untuk local development, permission `777` pada folder `storage` dan `bootstrap/cache` masih bisa diterima. Jangan gunakan konfigurasi permission tersebut untuk server production.

Project ini menggunakan MiniStack/LocalStack sebagai emulator cloud, sehingga data bucket dan object berada di volume Docker lokal, bukan di AWS asli.

---

## Tim dan Konteks

Project ini dikembangkan sebagai platform mini IaaS untuk project akhir. Fokus utama sistem adalah menyediakan layanan cloud storage sederhana yang dapat dijalankan secara lokal menggunakan Docker Desktop, WSL2, Laravel, MySQL, dan MiniStack.