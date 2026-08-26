# NodeHub

**NodeHub Portal** adalah aplikasi manajemen remote desktop VNC berbasis web yang modern dan responsif (dioptimalkan untuk tampilan Desktop & Mobile). Aplikasi ini mengintegrasikan Laravel, Alpine.js, TailwindCSS, MariaDB, dan Websockify Gateway.

---

## Requirement System

Sebelum melakukan instalasi, pastikan sistem Anda memenuhi kebutuhan berikut:

- **Sistem Operasi:** Linux (Ubuntu, Debian, Arch Linux, CentOS, RHEL, dll.) atau macOS.
- **Docker & Docker Compose** _(Direkomendasikan untuk Cara A)_.
- **PHP >= 8.2** dengan ekstensi (`pdo_mysql`, `intl`, `opcache`, `dom`, `xml`) _(Untuk Cara B)_.
- **Composer 2.x**, **Node.js >= 18**, **NPM**, **MariaDB / MySQL**, dan **Python 3 + websockify** _(Untuk Cara B)_.

---

# Installation

## Docker (Recommended)

Metode ini adalah cara paling cepat, mudah, dan aman. Seluruh layanan (_App Laravel, Websockify Bridge Gateway, MariaDB Database, Scheduler, dan Nginx Reverse Proxy dengan HTTPS SSL_) telah dibundel menjadi **1 Single Container All-in-One (`nodehub`)** yang dikelola oleh Supervisord.

> 🛡️ **Keamanan Arsitektur Docker:**
>
> - **Single Container Bundle:** Hanya ada 1 container tunggal bernama `nodehub`.
> - **Database (MariaDB):** Berjalan di dalam jaringan internal container (`127.0.0.1:3306`) tanpa di-expose ke luar host.
> - **SSL/HTTPS & Reverse Proxy Nginx:** Menjadi satu-satunya pintu masuk (entrypoint) publik pada port `8000` (`https://`). Menjelajah melalui HTTPS membuka fitur Secure Context browser untuk sinkronisasi clipboard otomatis 2 arah (`Ctrl + C` dan `Ctrl + V`).

### Run Container

Cukup jalankan satu perintah berikut di direktori proyek:

```bash
docker compose up -d --build
```

### Application Access

Setelah proses build dan startup selesai, aplikasi dapat diakses di:

- **Portal Utama & VNC Gateway:** `https://localhost:8000` (atau `https://IP_SERVER:8000` via HTTPS)
- **Kredensial Default Admin:**
    - **Email:** `admin@mail.com`
    - **Admin:** `admin`
    - **Password:** `qwerty21`

> **Catatan:** Database MariaDB Docker secara otomatis di-migrate dan di-seed admin saat pertama kali container di-boot. Data tersimpan secara permanen di Docker volume (`nodehub-db` & `nodehub-storage`).

### Customize Port & Environment Docker

Anda dapat mengubah port publik proxy atau kredensial default tanpa perlu mengubah file konfigurasi:

```bash
APP_PORT=9000 NODEHUB_ADMIN_PASSWORD=rahasia docker compose up -d
```

### Syntax Docker:

```bash
# Melihat log aktivitas container tunggal nodehub
docker compose logs -f nodehub

# Melihat status container yang berjalan
docker compose ps

# Menghentikan container (data tersimpan aman di volume)
docker compose down

# Menghentikan container & menghapus seluruh data database
docker compose down -v
```

---

## Host Native

Gunakan metode ini jika Anda ingin menjalankan aplikasi langsung di server/komputer host tanpa Docker.

### Install Packet Dependecies OS

**Tested on Ubuntu / Debian:**

```bash
sudo apt update
sudo apt install php-cli php-mysql php-intl php-xml php-curl composer nodejs npm mariadb-server python3-pip
pip3 install --user websockify
```

### Clone & Setup Proyek

```bash
# Clone repositori & masuk ke direktori proyek
git clone https://github.com/seno21/NodeHub.git
cd nodehub

# Install dependensi PHP
composer install

# Salin file konfigurasi environment
cp .env.example .env

# Generate Application Encryption Key
php artisan key:generate
```

### Database Config & Run Migration

Buka file `.env` dan sesuaikan koneksi database MariaDB/MySQL Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nodehub
DB_USERNAME=root
DB_PASSWORD=password_db_anda
```

Jalankan perintah berikut untuk membuat tabel & seed admin pertama:

```bash
php artisan migrate --seed
```

### Build Asset Frontend (CSS & JavaScript)

```bash
npm install
npm run build
```

### Permission Folder Storage

```bash
chmod -R ug+w storage bootstrap/cache
```

### Install & Run Websockify Bridge Service

Websockify bertindak sebagai bridge antara koneksi WebSocket dari browser ke target VNC.

**Using Service systemd (Auto & Recommended):**

```bash
sudo ./scripts/install-bridge-service.sh
```

Service bernama `nodehub-bridge` akan otomatis dibuat dan berjalan tiap kali server di-boot.
Perintah pengelolaan service:

```bash
systemctl status nodehub-bridge
sudo systemctl restart nodehub-bridge
journalctl -u nodehub-bridge -e
```

**Or Run Manual (Mode Development):**

```bash
php artisan vnc:bridge
```

### Run Task Scheduler & Server

Jalankan scheduler untuk pembersihan token VNC otomatis:

```bash
php artisan schedule:work
```

Jalankan server aplikasi web (untuk mode development):

```bash
php artisan serve
```

---

## Summary Variabel `.env` Penting

| Variabel                | Deskripsi                                     | Default                           |
| :---------------------- | :-------------------------------------------- | :-------------------------------- |
| `DB_HOST`               | Host database MySQL/MariaDB                   | `127.0.0.1`                       |
| `DB_PORT`               | Port database MySQL/MariaDB                   | `3306`                            |
| `DB_DATABASE`           | Nama database                                 | `nodehub`                         |
| `VNC_WEBSOCKIFY_LISTEN` | Alamat listen service Websockify              | `127.0.0.1:6080`                  |
| `VNC_WS_URL`            | URL WebSocket yang diakses oleh browser       | `wss://localhost:8000/websockify` |
| `VNC_TOKEN_TTL`         | Masa berlaku token sesi VNC (detik)           | `120`                             |
| `VNC_STATUS_TIMEOUT`    | Timeout pengecekan port status device (detik) | `1`                               |
| `ADMIN_EMAIL`           | Email default akun administrator              | `admin@mail.com`                  |
| `ADMIN_PASSWORD`        | Password default akun administrator           | `qwerty21`                        |

---

## License

Proyek ini dirilis di bawah lisensi [MIT](LICENSE).
