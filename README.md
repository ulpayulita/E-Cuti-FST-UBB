# Admin Panel - Sistem Pengajuan Cuti

Panel admin untuk mengelola sistem pengajuan cuti menggunakan PHP.

## Fitur

- ✅ Login/Logout dengan session management
- ✅ Dashboard dengan statistik
- ✅ Manajemen Pegawai (CRUD)
- ✅ Manajemen Izin Cuti (View, Approve/Reject, Delete)
- ✅ Manajemen Izin Ketidakhadiran (View, Approve/Reject, Delete)
- ✅ Manajemen Jenis Cuti (CRUD)
- ✅ Profile Admin

## Instalasi

1. Pastikan API sudah di-hosting dan dapat diakses
2. Edit file `config.php` dan ubah `API_BASE_URL` sesuai dengan URL API yang sudah di-hosting:

```php
define('API_BASE_URL', 'https://yourdomain.com/api');
```

3. Pastikan server PHP memiliki ekstensi `curl` aktif
4. Akses aplikasi melalui browser: `http://yourdomain.com/admin/login.php`

## Struktur File

```
admin/
├── config.php              # Konfigurasi API dan helper functions
├── login.php               # Halaman login
├── logout.php              # Logout handler
├── dashboard.php           # Dashboard utama
├── pegawai.php             # Manajemen pegawai
├── izin_cuti.php           # Manajemen izin cuti
├── izin_ketidakhadiran.php # Manajemen izin ketidakhadiran
├── jenis_cuti.php          # Manajemen jenis cuti
├── profile.php             # Profile admin
├── includes/
│   ├── header.php          # Header navigation
│   └── sidebar.php         # Sidebar navigation
└── assets/
    └── css/
        └── style.css        # Styling
```

## Penggunaan

### Login
- Akses halaman login dengan kredensial admin atau verifikator
- Setelah login, akan diarahkan ke dashboard

### Dashboard
- Menampilkan statistik: Total Pegawai, Izin Cuti, Izin Ketidakhadiran, dan yang Menunggu Persetujuan
- Menampilkan data terbaru dari izin cuti dan izin ketidakhadiran

### Manajemen Pegawai
- Tambah pegawai baru dengan form lengkap
- Edit data pegawai
- Hapus pegawai
- Lihat daftar semua pegawai

### Manajemen Izin Cuti
- Lihat semua pengajuan izin cuti
- Setujui atau tolak pengajuan yang pending
- Lihat detail pengajuan
- Hapus pengajuan

### Manajemen Izin Ketidakhadiran
- Lihat semua pengajuan izin ketidakhadiran
- Setujui atau tolak pengajuan yang pending
- Lihat detail pengajuan
- Hapus pengajuan

### Manajemen Jenis Cuti
- Tambah jenis cuti baru
- Edit jenis cuti
- Hapus jenis cuti

### Profile
- Lihat dan edit profile admin
- Update password

## Catatan

- Pastikan API sudah di-hosting dan dapat diakses dari server web
- Pastikan ekstensi `curl` aktif di PHP
- Session akan otomatis expire jika tidak ada aktivitas
- Hanya user dengan role `admin` atau `verifikator` yang dapat mengakses panel ini

## Troubleshooting

### Error: cURL tidak tersedia
- Install ekstensi curl PHP: `sudo apt-get install php-curl` (Linux) atau aktifkan di php.ini

### Error: Cannot connect to API
- Periksa `API_BASE_URL` di `config.php`
- Pastikan API dapat diakses dari server web
- Periksa firewall dan CORS settings

### Session tidak berfungsi
- Pastikan session directory writable
- Periksa session configuration di php.ini
