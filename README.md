# 📦 Sistem Inventori Gereja HKBP Setia Mekar

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

**Sistem manajemen inventori berbasis web dengan Sistem Pendukung Keputusan (SPK) menggunakan metode TOPSIS untuk pengelolaan barang gereja secara digital, efisien, dan terstruktur.**

[Fitur](#-fitur-utama) • [Instalasi](#-instalasi) • [Teknologi](#️-teknologi-yang-digunakan) • [Struktur Proyek](#-struktur-proyek)

</div>

---

## 📋 Deskripsi

**Sistem Inventori Gereja HKBP Setia Mekar** adalah aplikasi web berbasis Laravel yang dirancang khusus untuk mengelola inventori barang gereja secara terintegrasi. Sistem ini dilengkapi dengan modul **Sistem Pendukung Keputusan (SPK)** menggunakan metode **TOPSIS (Technique for Order Preference by Similarity to Ideal Solution)** untuk membantu bendahara dalam menentukan prioritas pengadaan barang secara objektif dan terukur.

Sistem ini mendukung tiga role pengguna (Admin, Pengurus, Bendahara) dengan hak akses yang berbeda, memungkinkan pengelolaan inventori yang efisien, transparan, dan akuntabel.

---

## ✨ Fitur Utama

### 🔐 Autentikasi & Keamanan
- ✅ Login berbasis email dan password
- ✅ Multi-role access control (Admin, Pengurus, Bendahara)
- ✅ Session-based authentication
- ✅ Role-based authorization untuk setiap fitur

### 📊 Dashboard Informatif
- ✅ Statistik real-time inventori
- ✅ Grafik transaksi harian menggunakan Chart.js
- ✅ Quick actions untuk akses cepat
- ✅ Overview ringkas untuk setiap role

### 📦 Manajemen Inventori
- ✅ **CRUD Barang** dengan upload gambar
- ✅ Sistem kode barang otomatis
- ✅ Kategori barang terorganisir
- ✅ Tracking stok masuk/keluar otomatis
- ✅ Filter dan pencarian advanced
- ✅ Soft delete untuk arsip barang

### 📝 Sistem Pengajuan & Verifikasi
- ✅ **Pengajuan Pengadaan Barang** (Role: Pengurus)
  - Form pengajuan lengkap dengan validasi
  - Input kriteria K1 (Urgensi) dan K2 (Stok)
  - Upload file pendukung (PDF/DOC/DOCX)
  - Generate kode pengajuan otomatis (PNG+YYYYMMDD+NNN)
  - Perhitungan K3 (Persentase Biaya) otomatis

- ✅ **Analisis TOPSIS** (Role: Bendahara)
  - Perhitungan prioritas pengadaan secara objektif
  - Tiga kriteria: Urgensi (K1), Stok (K2), Persentase Biaya (K3)
  - Ranking pengajuan berdasarkan nilai preferensi
  - Detail perhitungan lengkap untuk transparansi

- ✅ **Verifikasi Pengajuan** (Role: Bendahara)
  - Setujui/Tolak/Proses pengajuan
  - Keterangan verifikasi
  - Tracking status pengajuan

### 💰 Manajemen Kas
- ✅ Pencatatan kas masuk/keluar
- ✅ Perhitungan saldo kas otomatis
- ✅ Laporan keuangan lengkap
- ✅ Integrasi dengan perhitungan TOPSIS (K3)

### 📈 Transaksi & Tracking
- ✅ **Barang Masuk**: Pencatatan dan update stok otomatis
- ✅ **Barang Keluar**: Pencatatan dan pengurangan stok
- ✅ **Peminjaman Barang**: Tracking peminjaman dan pengembalian
- ✅ **Perawatan Barang**: Jadwal dan tracking perawatan
- ✅ **Audit Barang**: Pencatatan hasil audit berkala

### 📄 Laporan & Export
- ✅ Laporan inventori lengkap
- ✅ Laporan transaksi (masuk/keluar, peminjaman, perawatan)
- ✅ Laporan pengadaan dan verifikasi
- ✅ Laporan keuangan (kas)
- ✅ **Export ke Excel** (.xlsx) menggunakan Maatwebsite Excel
- ✅ **Export ke PDF** (.pdf) menggunakan Laravel DomPDF

### 🎯 Sistem Pendukung Keputusan (TOPSIS)
- ✅ Perhitungan TOPSIS otomatis dengan 7 langkah algoritma
- ✅ Tiga kriteria dengan bobot:
  - K1 (Urgensi): 0.30 - Benefit
  - K2 (Stok): 0.25 - Cost
  - K3 (Persentase Biaya): 0.45 - Cost
- ✅ Ranking pengajuan berdasarkan nilai preferensi (0-1)
- ✅ Detail perhitungan lengkap (matriks, solusi ideal, jarak)

---

## 🛠️ Teknologi yang Digunakan

### Backend
- **Framework**: Laravel 11.x
- **PHP**: 8.2+
- **Database**: MySQL / SQLite (untuk pengembangan)

### Frontend
- **CSS Framework**: Bootstrap 5, Tailwind CSS
- **JavaScript**: Vanilla JS, Chart.js
- **Build Tool**: Vite 7.x
- **Template Engine**: Blade (Laravel)

### Third-Party Packages
- **barryvdh/laravel-dompdf** (^3.1) - Generate PDF reports
- **maatwebsite/excel** (^3.1) - Export/Import Excel files

### Development Tools
- **Composer** - PHP dependency management
- **NPM** - JavaScript package management
- **Git** - Version control

---

## 📋 Persyaratan Sistem

### Server Requirements
- **PHP**: >= 8.2
- **Composer**: Latest version
- **Database**: MySQL 8.0+ atau SQLite 3.x
- **Web Server**: Apache/Nginx
- **Extensions**: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### Development Requirements (Opsional)
- **Node.js**: >= 18.x
- **NPM**: >= 9.x

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/ricchrstn/ricchrstn-HKBP-GEREJA-SETIA-MEKAR-INVENTORY-V4.git
cd ricchrstn-HKBP-GEREJA-SETIA-MEKAR-INVENTORY-V4
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies (opsional, untuk development)
npm install
```

### 3. Konfigurasi Environment

```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username
DB_PASSWORD=password
```

Atau untuk pengembangan menggunakan SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 5. Migrasi Database

```bash
# Jalankan migrasi dan seeder
php artisan migrate --seed
```

### 6. Buat Storage Link

```bash
# Buat symbolic link untuk storage
php artisan storage:link
```

### 7. Build Assets (Opsional)

```bash
# Build assets untuk production
npm run build

# Atau jalankan dev server untuk development
npm run dev
```

### 8. Jalankan Aplikasi

```bash
# Jalankan development server
php artisan serve
```

Akses aplikasi di: **http://localhost:8000**

---

## 👥 Default User Credentials

Setelah menjalankan seeder, Anda dapat login dengan kredensial berikut:

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| **Admin** | admin@gereja.com | password | Akses penuh sistem |
| **Pengurus** | pengurus@gereja.com | password | Manajemen inventori & pengajuan |
| **Bendahara** | bendahara@gereja.com | password | Analisis TOPSIS & verifikasi |

> ⚠️ **PENTING**: Ganti password default setelah instalasi pertama!

---

## 📁 Struktur Proyek

```
gereja/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Admin/          # Controller untuk role Admin
│   │       ├── Pengurus/       # Controller untuk role Pengurus
│   │       ├── Bendahara/      # Controller untuk role Bendahara
│   │       └── Auth/           # Controller autentikasi
│   ├── Models/                 # Model Eloquent
│   └── Helpers/                # Helper functions
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── resources/
│   ├── views/                  # Blade templates
│   │   ├── admin/              # Views untuk Admin
│   │   ├── pengurus/           # Views untuk Pengurus
│   │   └── bendahara/          # Views untuk Bendahara
│   ├── css/                    # Stylesheet
│   └── js/                     # JavaScript
├── routes/
│   └── web.php                 # Route definitions
├── public/                     # Public assets
├── storage/                    # Storage files
└── tests/                      # Test files
```

---

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test dengan coverage
php artisan test --coverage
```

---

## 🔧 Troubleshooting

### Error 500 atau Blank Page

```bash
# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Permission Error (Linux/Mac)

```bash
# Set permission untuk storage dan cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Permission Error (Windows)

Pastikan folder `storage/` dan `bootstrap/cache/` dapat ditulis oleh web server.

### Database Connection Error

1. Pastikan database sudah dibuat
2. Periksa konfigurasi di file `.env`
3. Pastikan kredensial database benar
4. Pastikan MySQL service berjalan

### Storage Link Error

```bash
# Hapus link lama jika ada
rm public/storage

# Buat link baru
php artisan storage:link
```

---

## 📖 Dokumentasi

### Alur Pengajuan & TOPSIS

1. **Pengurus** membuat pengajuan dengan input K1 (Urgensi) dan K2 (Stok)
2. Sistem menghitung K3 (Persentase Biaya) otomatis berdasarkan saldo kas
3. **Bendahara** mengakses halaman Analisis TOPSIS
4. Sistem melakukan perhitungan TOPSIS dan menampilkan ranking
5. **Bendahara** melakukan verifikasi berdasarkan ranking TOPSIS

### Kriteria TOPSIS

- **K1 (Tingkat Urgensi Barang)**: Benefit, Bobot 0.30, Skala 1-5
- **K2 (Ketersediaan Stok Barang)**: Cost, Bobot 0.25, Skala 1-5
- **K3 (Persentase Biaya Pengadaan)**: Cost, Bobot 0.45, Dihitung otomatis

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan buat issue atau pull request untuk perbaikan dan fitur baru.

---

## 📝 Lisensi

Proyek ini dikembangkan untuk Gereja HKBP Setia Mekar.

---

## 👨‍💻 Developer

Dikembangkan dengan ❤️ untuk Gereja HKBP Setia Mekar

---

## 📞 Kontak & Support

Untuk pertanyaan atau dukungan, silakan hubungi tim pengembang.

---

<div align="center">

**© 2025 Gereja HKBP Setia Mekar. All rights reserved.**

Made with [Laravel](https://laravel.com) & ❤️

</div>
