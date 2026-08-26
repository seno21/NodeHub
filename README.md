# WebVNC Portal - Panduan Instalasi & Deployment

**WebVNC Portal** adalah aplikasi manajemen remote desktop VNC berbasis web yang modern dan responsif (dioptimalkan untuk tampilan Desktop & Mobile). Aplikasi ini mengintegrasikan Laravel, Alpine.js, TailwindCSS, MariaDB, dan Websockify Gateway.

---

## Prasyarat Sistem

Sebelum melakukan instalasi, pastikan sistem Anda memenuhi kebutuhan berikut:

- **Sistem Operasi:** Linux (Ubuntu, Debian, Arch Linux, CentOS, RHEL, dll.) atau macOS.
- **Docker & Docker Compose** _(Direkomendasikan untuk Cara A)_.
- **PHP >= 8.2** dengan ekstensi (`pdo_mysql`, `intl`, `opcache`, `dom`, `xml`) _(Untuk Cara B)_.
- **Composer 2.x**, **Node.js >= 18**, **NPM**, **MariaDB / MySQL**, dan **Python 3 + websockify** _(Untuk Cara B)_.

---

## Cara A: Instalasi Menggunakan Docker (Direkomendasikan)

Metode ini adalah cara paling cepat dan mudah. Seluruh layanan (_App Laravel, Websockify Bridge Gateway, MariaDB Database, dan Scheduler_) akan langsung berjalan secara terisolasi dan terkonfigurasi otomatis.

### 1. Jalankan Container

Cukup jalankan satu perintah berikut di direktori proyek:

```bash
docker compose up -d --build
```

### 2. Akses Aplikasi

Setelah proses build dan startup selesai, aplikasi dapat diakses di:

- **Portal Utama (Web):** `http://localhost:8000`
- **Websockify Gateway:** `ws://localhost:6080`
- **Kredensial Default Admin:**
    - **Email:** `admin@example.com`
    - **Password:** `password`

> **Catatan:** Database MariaDB Docker secara otomatis di-migrate dan di-seed admin saat pertama kali container di-boot. Data tersimpan secara permanen di Docker volume (`webvnc_db-data` & `webvnc_app-storage`).

### 3. Kustomisasi Port & Environment Docker

Anda dapat mengubah port atau kredensial default tanpa perlu mengubah file konfigurasi:

```bash
APP_PORT=9000 BRIDGE_PORT=6090 WEBVNC_ADMIN_PASSWORD=rahasia docker compose up -d
```

### Perintah Penting Docker:

```bash
# Melihat log aktivitas aplikasi
docker compose logs -f app

# Melihat status container yang berjalan
docker compose ps

# Menghentikan container (data tersimpan aman di volume)
docker compose down

# Menghentikan container & menghapus seluruh data database
docker compose down -v
```

---

## Cara B: Instalasi Manual Tanpa Docker (Host Native)

Gunakan metode ini jika Anda ingin menjalankan aplikasi langsung di server/komputer host tanpa Docker.

### 1. Install Paket Dependensi OS

**Di Arch Linux:**

```bash
sudo pacman -S php php-fpm composer nodejs npm mariadb python-pip
pip install --user websockify
```

**Di Ubuntu / Debian:**

```bash
sudo apt update
sudo apt install php-cli php-mysql php-intl php-xml php-curl composer nodejs npm mariadb-server python3-pip
pip3 install --user websockify
```

### 2. Clone & Setup Proyek

```bash
# Clone repositori & masuk ke direktori proyek
git clone <url-repository-anda>
cd handons

# Install dependensi PHP
composer install

# Salin file konfigurasi environment
cp .env.example .env

# Generate Application Encryption Key
php artisan key:generate
```

### 3. Konfigurasi Database & Jalankan Migration

Buka file `.env` dan sesuaikan koneksi database MariaDB/MySQL Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webvnc
DB_USERNAME=root
DB_PASSWORD=password_db_anda
```

Jalankan perintah berikut untuk membuat tabel & seed admin pertama:

```bash
php artisan migrate --seed
```

### 4. Build Asset Frontend (CSS & JavaScript)

```bash
npm install
npm run build
```

### 5. Atur Izin Folder Storage

```bash
chmod -R ug+w storage bootstrap/cache
```

### 6. Install & Jalankan Websockify Bridge Service

Websockify bertindak sebagai bridge antara koneksi WebSocket dari browser ke target VNC.

**Menggunakan Service systemd (Otomatis & Direkomendasikan):**

```bash
sudo ./scripts/install-bridge-service.sh
```

Service bernama `webvnc-bridge` akan otomatis dibuat dan berjalan tiap kali server di-boot.
Perintah pengelolaan service:

```bash
systemctl status webvnc-bridge
sudo systemctl restart webvnc-bridge
journalctl -u webvnc-bridge -e
```

**Atau Jalankan Manual (Mode Development):**

```bash
php artisan vnc:bridge
```

### 7. Jalankan Task Scheduler & Server

Jalankan scheduler untuk pembersihan token VNC otomatis:

```bash
php artisan schedule:work
```

Jalankan server aplikasi web (untuk mode development):

```bash
php artisan serve
```

---

## Ringkasan Variabel `.env` Penting

| Variabel                | Deskripsi                                     | Default                            |
| :---------------------- | :-------------------------------------------- | :--------------------------------- |
| `DB_HOST`               | Host database MySQL/MariaDB                   | `db` (Docker) / `127.0.0.1` (Host) |
| `DB_PORT`               | Port database MySQL/MariaDB                   | `3306`                             |
| `DB_DATABASE`           | Nama database                                 | `webvnc`                           |
| `VNC_WEBSOCKIFY_LISTEN` | Alamat listen service Websockify              | `0.0.0.0:6080`                     |
| `VNC_WS_URL`            | URL WebSocket yang diakses oleh browser       | `ws://localhost:6080`              |
| `VNC_TOKEN_TTL`         | Masa berlaku token sesi VNC (detik)           | `120`                              |
| `VNC_STATUS_TIMEOUT`    | Timeout pengecekan port status device (detik) | `1`                                |
| `ADMIN_EMAIL`           | Email default akun administrator              | `admin@example.com`                |
| `ADMIN_PASSWORD`        | Password default akun administrator           | `password`                         |

---

## Lisensi

Proyek ini dirilis di bawah lisensi [MIT](LICENSE).
