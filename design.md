# WebVNC Portal Design Document

## 1. Project Overview

Aplikasi web terpusat untuk melakukan remote akses ke komputer klien berbasis VNC (Windows dengan UltraVNC & Xubuntu dengan x11vnc). Bertujuan untuk menggantikan penggunaan klien desktop (RealVNC) menjadi satu portal web terpadu.

## 2. Tech Stack

- **Backend Framework:** Laravel 10/11
- **Authentication:** Laravel Breeze (1 Admin User)
- **Frontend/Styling:** Blade Templates + Tailwind CSS
- **VNC Web Client:** noVNC (HTML5 Canvas + WebSockets)
- **Proxy/Bridge:** Websockify (Python/NodeJS base)

## 3. System Architecture

[Browser/Admin] <--(WebSocket)--> [Websockify Server] <--(TCP)--> [Target VNC (Windows/Xubuntu)]
|
(HTTP/UI)
|
[Laravel Web App]

## 4. Database Schema

**Table: `computers`**
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt | Primary Key |
| `name` | String | Nama perangkat (e.g., "Xubuntu Dev") |
| `ip_address` | String | IP Address target (e.g., 192.168.1.10) |
| `vnc_port` | Integer | Port VNC target (Default: 5900) |
| `os_type` | String | 'windows' atau 'linux' (untuk icon UI) |
| `vnc_password`| String | (Opsional/Encrypted) Jika ingin auto-login |
| `created_at` | Timestamp | |
| `updated_at` | Timestamp | |

## 5. UI/UX Layout (Modern Simple)

### A. Login Page

- Menggunakan default bawaan Laravel Breeze dengan penyesuaian warna (Dark mode / Light mode clean).
- Logo aplikasi di tengah, dengan form email dan password sederhana.

### B. Dashboard (Device List)

- **Header:** Logo "WebVNC", Nama Admin, Tombol Logout.
- **Main Content:**
    - Tombol "+ Add New Device" di pojok kanan atas.
    - Tampilan **Grid (Cards)** untuk list komputer.
    - **Isi Card:**
        - Ikon OS (Logo Windows / Ubuntu).
        - Nama Komputer (Bold, besar).
        - IP Address & Port (Teks abu-abu kecil).
        - Status Indicator (Titik hijau/merah - opsional menggunakan ping).
        - Tombol "Connect" (Biru/Primary color).
        - Tombol "Edit/Delete" berupa icon gear kecil.

### C. VNC Remote View (Viewer Page)

- **Layout:** Hampir Full Screen.
- **Floating/Collapsible Toolbar (Kiri/Atas):**
    - Tombol "Back to Dashboard" (Disconnect).
    - Tombol "Send Ctrl+Alt+Del" (Khusus Windows).
    - Tombol "Toggle Fullscreen".
    - Tombol "Settings" (Scaling mode: Fit to window, Local cursor).
- **Center Canvas:** Area noVNC untuk menampilkan layar target. Background warna hitam pekat (`#000000`).

## 6. Security Considerations

- Aplikasi berada di jaringan internal (Intranet/VPN).
- Halaman hanya bisa diakses oleh `auth` middleware.
- (Future task): Menambahkan SSL/WSS (WebSocket Secure) pada Websockify agar lalu lintas remote terenkripsi.
