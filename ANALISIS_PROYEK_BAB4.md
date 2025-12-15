# ANALISIS PROYEK SISTEM INVENTORI GEREJA HKBP SETIA MEKAR
## Untuk Naskah Bab 4 Skripsi

---

## DAFTAR ISI

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Arsitektur Sistem](#2-arsitektur-sistem)
3. [Teknologi yang Digunakan](#3-teknologi-yang-digunakan)
4. [Struktur Database](#4-struktur-database)
5. [Alur Proses Bisnis](#5-alur-proses-bisnis)
6. [Implementasi Metode TOPSIS](#6-implementasi-metode-topsis)
7. [Fitur-Fitur Utama](#7-fitur-fitur-utama)
8. [Keamanan dan Kontrol Akses](#8-keamanan-dan-kontrol-akses)
9. [Antarmuka Pengguna](#9-antarmuka-pengguna)

---

## 1. GAMBARAN UMUM SISTEM

### 1.1 Deskripsi Sistem

Sistem Inventori Gereja HKBP Setia Mekar adalah sistem manajemen inventori berbasis web yang dirancang untuk mengelola barang-barang gereja secara terintegrasi. Sistem ini memiliki fitur utama berupa sistem pengajuan pengadaan barang dengan metode TOPSIS (Technique for Order Preference by Similarity to Ideal Solution) untuk membantu pengambilan keputusan prioritas pengadaan barang.

### 1.2 Tujuan Sistem

1. **Manajemen Inventori Terpusat**: Mengelola data barang, kategori, stok, dan transaksi secara terpusat
2. **Sistem Pengajuan Terstruktur**: Memfasilitasi proses pengajuan pengadaan barang dengan validasi dan tracking
3. **Analisis Keputusan**: Menggunakan metode TOPSIS untuk menentukan prioritas pengadaan barang secara objektif
4. **Multi-Role Management**: Mendukung tiga peran pengguna (Admin, Pengurus, Bendahara) dengan akses berbeda
5. **Laporan dan Audit**: Menyediakan laporan lengkap dan sistem audit untuk transparansi

### 1.3 Stakeholder Sistem

| Role | Deskripsi | Hak Akses |
|------|-----------|-----------|
| **Admin** | Administrator sistem dengan akses penuh | Manajemen user, inventori, kategori, jadwal audit, laporan lengkap |
| **Pengurus** | Pengelola inventori harian | CRUD barang, transaksi (masuk/keluar), peminjaman, perawatan, pengajuan, audit |
| **Bendahara** | Pengelola keuangan dan verifikasi | Verifikasi pengajuan, analisis TOPSIS, manajemen kas, laporan keuangan |

---

## 2. ARSITEKTUR SISTEM

### 2.1 Arsitektur Aplikasi

Sistem dibangun menggunakan arsitektur **MVC (Model-View-Controller)** dengan framework Laravel 11:

```
┌─────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                    │
│  (Blade Templates - Bootstrap 5, Chart.js, Tailwind)  │
└──────────────────────┬──────────────────────────────────┘
                      │
┌──────────────────────▼──────────────────────────────────┐
│                   CONTROLLER LAYER                      │
│  (Admin, Pengurus, Bendahara Controllers)               │
└──────────────────────┬──────────────────────────────────┘
                      │
┌──────────────────────▼──────────────────────────────────┐
│                    MODEL LAYER                           │
│  (Eloquent ORM - Database Abstraction)                  │
└──────────────────────┬──────────────────────────────────┘
                      │
┌──────────────────────▼──────────────────────────────────┐
│                   DATABASE LAYER                         │
│  (MySQL/SQLite - Relational Database)                    │
└──────────────────────────────────────────────────────────┘
```

### 2.2 Struktur Direktori Proyek

```
gereja/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Admin/          # Controller untuk role Admin
│   │       ├── Pengurus/       # Controller untuk role Pengurus
│   │       ├── Bendahara/      # Controller untuk role Bendahara
│   │       └── Auth/           # Controller autentikasi
│   ├── Models/                 # Model Eloquent (User, Barang, Pengajuan, dll)
│   └── Helpers/                # Helper functions
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── resources/
│   ├── views/                  # Blade templates
│   │   ├── admin/              # Views untuk Admin
│   │   ├── pengurus/           # Views untuk Pengurus
│   │   └── bendahara/           # Views untuk Bendahara
│   ├── css/                    # Stylesheet
│   └── js/                     # JavaScript
├── routes/
│   └── web.php                 # Route definitions
├── public/                     # Public assets
└── config/                     # Configuration files
```

### 2.3 Alur Request-Response

```
1. User Request → routes/web.php
2. Route → Middleware (auth, role check)
3. Controller Method
4. Model Query (Eloquent ORM)
5. Database Query
6. Data Processing (Business Logic)
7. View Rendering (Blade Template)
8. Response ke Browser
```

---

## 3. TEKNOLOGI YANG DIGUNAKAN

### 3.1 Backend Technologies

| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| **PHP** | ^8.2 | Bahasa pemrograman server-side |
| **Laravel** | ^11.0 | Framework PHP MVC |
| **MySQL/SQLite** | - | Database management system |

### 3.2 Frontend Technologies

| Teknologi | Fungsi |
|-----------|--------|
| **Bootstrap 5** | Framework CSS untuk UI responsive |
| **Tailwind CSS** | Utility-first CSS framework |
| **Chart.js** | Library untuk visualisasi data (grafik) |
| **Blade Template** | Template engine Laravel |
| **JavaScript (Vanilla)** | Interaktivitas client-side |

### 3.3 Third-Party Packages

| Package | Versi | Fungsi |
|---------|-------|--------|
| **barryvdh/laravel-dompdf** | ^3.1 | Generate PDF reports |
| **maatwebsite/excel** | ^3.1 | Export/Import Excel files |

### 3.4 Development Tools

- **Composer**: Dependency management untuk PHP
- **NPM**: Package management untuk JavaScript
- **Vite**: Build tool untuk assets
- **Laravel Tinker**: REPL untuk debugging

---

## 4. STRUKTUR DATABASE

### 4.1 Entity Relationship Diagram (Konseptual)

```
┌──────────┐         ┌──────────────┐         ┌──────────┐
│   User   │────────<│  Pengajuan   │────────>│ Kategori │
└──────────┘         └──────────────┘         └──────────┘
     │                      │
     │                      │
     │                      ▼
     │              ┌─────────────────┐
     │              │ AnalisisTopsis │
     │              └─────────────────┘
     │
     ▼
┌──────────┐
│   Kas    │
└──────────┘

┌──────────┐         ┌──────────────┐
│  Barang  │────────<│ BarangMasuk  │
└──────────┘         └──────────────┘
     │
     │
     ▼
┌──────────┐
│  Audit   │
└──────────┘
```

### 4.2 Tabel-Tabel Utama

#### 4.2.1 Tabel `users`
**Fungsi**: Menyimpan data pengguna sistem

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| name | varchar(255) | Nama pengguna |
| email | varchar(255) | Email (unique) |
| password | varchar(255) | Password (hashed) |
| role | enum | 'admin', 'pengurus', 'bendahara' |
| is_active | boolean | Status aktif user |
| phone | varchar(20) | Nomor telepon |
| address | text | Alamat |

#### 4.2.2 Tabel `pengajuan`
**Fungsi**: Menyimpan data pengajuan pengadaan barang

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| kode_pengajuan | varchar(50) | Kode unik pengajuan (PNG+YYYYMMDD+NNN) |
| nama_barang | varchar(255) | Nama barang yang diajukan |
| spesifikasi | text | Spesifikasi barang |
| jumlah | integer | Jumlah barang |
| harga_satuan | decimal | Harga per satuan |
| satuan | varchar(50) | Satuan (unit, buah, dll) |
| alasan | text | Alasan pengajuan |
| kebutuhan | date | Tanggal kebutuhan |
| user_id | bigint | Foreign key ke users |
| status | enum | 'pending', 'disetujui', 'ditolak' |
| urgensi | integer | K1: Tingkat urgensi (1-5) |
| ketersediaan_stok | integer | K2: Ketersediaan stok (1-5) |
| file_pengajuan | varchar(255) | Path file dokumen pendukung |

#### 4.2.3 Tabel `kriterias`
**Fungsi**: Menyimpan definisi kriteria untuk TOPSIS

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| nama | varchar(255) | Nama kriteria (K1, K2, K3) |
| bobot | decimal | Bobot kriteria (0-1) |
| tipe | enum | 'benefit' atau 'cost' |

**Data Default:**
- K1 (Urgensi): bobot = 0.30, tipe = 'benefit'
- K2 (Stok): bobot = 0.25, tipe = 'cost'
- K3 (Biaya): bobot = 0.45, tipe = 'cost'

#### 4.2.4 Tabel `analisis_topsis`
**Fungsi**: Menyimpan hasil perhitungan TOPSIS

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| pengajuan_id | bigint | Foreign key ke pengajuan |
| nilai_preferensi | decimal | Nilai preferensi (0-1) |
| ranking | integer | Peringkat pengajuan |

#### 4.2.5 Tabel `kas`
**Fungsi**: Menyimpan transaksi kas (masuk/keluar)

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| kode_transaksi | varchar(50) | Kode transaksi (KM/KK+YYYYMMDD+NNNN) |
| jenis | enum | 'masuk' atau 'keluar' |
| jumlah | decimal | Jumlah transaksi |
| tanggal | date | Tanggal transaksi |
| keterangan | text | Keterangan transaksi |
| user_id | bigint | Foreign key ke users |

#### 4.2.6 Tabel `barang`
**Fungsi**: Master data barang inventori

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| id | bigint | Primary key |
| kode_barang | varchar(50) | Kode unik barang |
| nama | varchar(255) | Nama barang |
| kategori_id | bigint | Foreign key ke kategori |
| stok | integer | Jumlah stok |
| harga | decimal | Harga barang |
| status | enum | Status barang |
| gambar | varchar(255) | Path gambar |

### 4.3 Relasi Antar Tabel

```php
// User → Pengajuan (One-to-Many)
User::hasMany(Pengajuan::class)

// Pengajuan → AnalisisTopsis (One-to-One)
Pengajuan::hasOne(AnalisisTopsis::class)

// Barang → BarangMasuk (One-to-Many)
Barang::hasMany(BarangMasuk::class)

// User → Kas (One-to-Many)
User::hasMany(Kas::class)
```

---

## 5. ALUR PROSES BISNIS

### 5.1 Alur Pengajuan Pengadaan Barang

```
┌─────────────────────────────────────────────────────────────┐
│                    ALUR PENGAJUAN BARANG                     │
└─────────────────────────────────────────────────────────────┘

FASE 1: PENGAJUAN (Role: Pengurus)
├── 1. Pengurus login ke sistem
├── 2. Akses menu "Pengajuan" → "Tambah Pengajuan"
├── 3. Isi form pengajuan:
│   ├── Data barang (nama, spesifikasi, jumlah, harga_satuan, satuan)
│   ├── Alasan pengajuan
│   ├── Tanggal kebutuhan
│   ├── Kriteria K1 (Urgensi: 1-5)
│   ├── Kriteria K2 (Ketersediaan Stok: 1-5)
│   └── Upload file pendukung (opsional)
├── 4. Sistem validasi input
├── 5. Sistem generate kode pengajuan otomatis (PNG+YYYYMMDD+NNN)
├── 6. Sistem set status = 'pending'
├── 7. Sistem hitung K3 (Persentase Biaya) otomatis:
│   └── K3 = (harga_satuan × jumlah) / saldoKas × 100
└── 8. Data tersimpan di database

FASE 2: ANALISIS TOPSIS (Role: Bendahara)
├── 1. Bendahara login ke sistem
├── 2. Akses menu "Analisis TOPSIS"
├── 3. Sistem menampilkan daftar pengajuan dengan status 'pending'
├── 4. Sistem update K3 otomatis berdasarkan saldo kas terkini
├── 5. Bendahara klik "Lihat Hasil Analisis"
├── 6. Sistem menjalankan perhitungan TOPSIS:
│   ├── Langkah 1: Membentuk matriks keputusan
│   ├── Langkah 2: Normalisasi matriks
│   ├── Langkah 3: Matriks terbobot
│   ├── Langkah 4: Solusi ideal positif & negatif
│   ├── Langkah 5: Hitung jarak Euclidean
│   ├── Langkah 6: Hitung nilai preferensi
│   └── Langkah 7: Perankingan
├── 7. Sistem simpan hasil ke tabel analisis_topsis
└── 8. Sistem tampilkan ranking pengajuan

FASE 3: VERIFIKASI (Role: Bendahara)
├── 1. Bendahara melihat hasil ranking TOPSIS
├── 2. Bendahara akses menu "Verifikasi Pengadaan"
├── 3. Bendahara review detail pengajuan
├── 4. Bendahara pilih aksi:
│   ├── "Setujui" → status = 'disetujui'
│   └── "Tolak" → status = 'ditolak' + keterangan
└── 5. Sistem update status pengajuan
```

### 5.2 Alur Manajemen Inventori

```
PENGURUS:
├── Barang Masuk → Update stok (+)
├── Barang Keluar → Update stok (-)
├── Peminjaman → Tracking peminjaman
└── Perawatan → Jadwal perawatan barang

ADMIN:
├── Master Barang → CRUD barang
├── Kategori → CRUD kategori
├── Jadwal Audit → Buat jadwal audit
└── Laporan → Generate laporan lengkap
```

### 5.3 Alur Manajemen Kas

```
BENDAHARA:
├── Kas Masuk → Tambah saldo
├── Kas Keluar → Kurangi saldo
├── Laporan Kas → Lihat laporan keuangan
└── Saldo Kas → Digunakan untuk hitung K3
```

---

## 6. IMPLEMENTASI METODE TOPSIS

### 6.1 Konsep TOPSIS

**TOPSIS (Technique for Order Preference by Similarity to Ideal Solution)** adalah metode pengambilan keputusan multi-kriteria yang memilih alternatif terbaik berdasarkan jarak terdekat dengan solusi ideal positif dan terjauh dari solusi ideal negatif.

### 6.2 Kriteria yang Digunakan

| Kode | Nama Kriteria | Tipe | Bobot | Deskripsi |
|------|---------------|------|-------|-----------|
| **K1** | Tingkat Urgensi Barang | Benefit | 0.30 | Semakin tinggi nilai (1-5), semakin prioritas |
| **K2** | Ketersediaan Stok Barang | Cost | 0.25 | Semakin rendah nilai (1-5), semakin prioritas |
| **K3** | Persentase Biaya Pengadaan | Cost | 0.45 | Dihitung: (harga_satuan × jumlah) / saldoKas × 100 |

**Total Bobot**: 0.30 + 0.25 + 0.45 = 1.00 ✓

### 6.3 Algoritma Perhitungan TOPSIS

#### **Langkah 1: Membentuk Matriks Keputusan (X)**

```php
// Lokasi: AnalisisTopsisController.php (lines 81-97)

$matriksKeputusan = [];
foreach ($pengajuans as $pengajuan) {
    $totalHarga = $pengajuan->harga_satuan * $pengajuan->jumlah;
    $persentaseBiaya = ($totalHarga / $anggaran) * 100; // K3
    
    $row = [
        $pengajuan->urgensi,           // K1
        $pengajuan->ketersediaan_stok, // K2
        $persentaseBiaya               // K3
    ];
    $matriksKeputusan[] = $row;
}
```

**Contoh Matriks Keputusan:**
```
X = [
    [4, 3, 5.00],  // Pengajuan 1: Urgensi=4, Stok=3, Biaya=5%
    [3, 5, 4.00],  // Pengajuan 2: Urgensi=3, Stok=5, Biaya=4%
    [5, 2, 12.00]  // Pengajuan 3: Urgensi=5, Stok=2, Biaya=12%
]
```

#### **Langkah 2: Normalisasi Matriks (R)**

**Rumus**: `r_ij = x_ij / √(Σ x_ij²)`

```php
// Lokasi: AnalisisTopsisController.php (lines 99-118)

// Hitung akar jumlah kuadrat untuk setiap kolom
for ($j = 0; $j < 3; $j++) {
    $sumSquares = 0;
    for ($i = 0; $i < count($pengajuans); $i++) {
        $sumSquares += pow($matriksKeputusan[$i][$j], 2);
    }
    $jumlahKuadrat[$j] = sqrt($sumSquares);
}

// Normalisasi
for ($i = 0; $i < count($pengajuans); $i++) {
    for ($j = 0; $j < 3; $j++) {
        $matriksNormalisasi[$i][$j] = $matriksKeputusan[$i][$j] / $jumlahKuadrat[$j];
    }
}
```

#### **Langkah 3: Matriks Normalisasi Terbobot (Y)**

**Rumus**: `y_ij = r_ij × w_j`

```php
// Lokasi: AnalisisTopsisController.php (lines 120-129)

for ($i = 0; $i < count($pengajuans); $i++) {
    for ($j = 0; $j < 3; $j++) {
        $matriksTerbobot[$i][$j] = $matriksNormalisasi[$i][$j] * $kriterias[$j]->bobot;
    }
}
```

#### **Langkah 4: Solusi Ideal Positif (A+) dan Negatif (A-)**

```php
// Lokasi: AnalisisTopsisController.php (lines 131-147)

for ($j = 0; $j < 3; $j++) {
    $kolom = array_column($matriksTerbobot, $j);
    
    if ($kriterias[$j]->tipe == 'benefit') {
        $solusiIdealPositif[$j] = max($kolom);  // A+ = max
        $solusiIdealNegatif[$j] = min($kolom);  // A- = min
    } else { // cost
        $solusiIdealPositif[$j] = min($kolom);  // A+ = min
        $solusiIdealNegatif[$j] = max($kolom);  // A- = max
    }
}
```

#### **Langkah 5: Menghitung Jarak Euclidean**

**Rumus**:
- `D+_i = √(Σ(y_ij - A+_j)²)` → Jarak ke solusi ideal positif
- `D-_i = √(Σ(y_ij - A-_j)²)` → Jarak ke solusi ideal negatif

```php
// Lokasi: AnalisisTopsisController.php (lines 149-165)

for ($i = 0; $i < count($pengajuans); $i++) {
    $dPlus = 0;
    $dMinus = 0;
    
    for ($j = 0; $j < 3; $j++) {
        $dPlus += pow($matriksTerbobot[$i][$j] - $solusiIdealPositif[$j], 2);
        $dMinus += pow($matriksTerbobot[$i][$j] - $solusiIdealNegatif[$j], 2);
    }
    
    $jarakPositif[$i] = sqrt($dPlus);
    $jarakNegatif[$i] = sqrt($dMinus);
}
```

#### **Langkah 6: Nilai Preferensi (V)**

**Rumus**: `V_i = D-_i / (D+_i + D-_i)`

```php
// Lokasi: AnalisisTopsisController.php (lines 167-175)

for ($i = 0; $i < count($pengajuans); $i++) {
    $epsilon = 0.000001; // Mencegah pembagian 0
    $totalJarak = $jarakPositif[$i] + $jarakNegatif[$i] + $epsilon;
    $preferensi[$i] = $jarakNegatif[$i] / $totalJarak;
}
```

**Interpretasi**:
- `V_i` mendekati 1 → Alternatif lebih baik (lebih dekat ke A+, jauh dari A-)
- `V_i` mendekati 0 → Alternatif lebih buruk

#### **Langkah 7: Perankingan**

```php
// Lokasi: AnalisisTopsisController.php (lines 177-202)

// Urutkan berdasarkan nilai preferensi (descending)
usort($hasil, function ($a, $b) {
    return $b['nilai_preferensi'] <=> $a['nilai_preferensi'];
});

// Simpan ke database
foreach ($hasil as $index => $item) {
    AnalisisTopsis::updateOrCreate(
        ['pengajuan_id' => $item['pengajuan']->id],
        [
            'nilai_preferensi' => $item['nilai_preferensi'],
            'ranking' => $index + 1
        ]
    );
}
```

### 6.4 Contoh Perhitungan TOPSIS

Lihat file `CONTOH_PERHITUNGAN_TOPSIS.md` untuk contoh perhitungan lengkap dengan data numerik.

### 6.5 Verifikasi Implementasi

| Aspek | Status | Keterangan |
|-------|--------|------------|
| Matriks Keputusan | ✅ BENAR | Data diambil dari database dengan benar |
| Normalisasi | ✅ BENAR | Rumus sesuai standar TOPSIS |
| Pembobotan | ✅ BENAR | Bobot total = 1.0, sesuai database |
| Solusi Ideal | ✅ BENAR | Pengecekan benefit/cost benar |
| Jarak Euclidean | ✅ BENAR | Rumus perhitungan benar |
| Nilai Preferensi | ✅ BENAR | Epsilon handling untuk mencegah pembagian 0 |
| Perankingan | ✅ BENAR | Urutan descending sesuai standar |
| Penyimpanan | ✅ BENAR | Hasil disimpan ke database |

---

## 7. FITUR-FITUR UTAMA

### 7.1 Fitur Admin

| Fitur | Deskripsi | Lokasi File |
|-------|-----------|-------------|
| **Dashboard** | Statistik inventori, grafik transaksi | `Admin/DashboardController.php` |
| **Manajemen User** | CRUD pengguna, reset password | `Admin/UserController.php` |
| **Master Barang** | CRUD barang, kategori, soft delete | `Admin/BarangController.php` |
| **Jadwal Audit** | Buat dan kelola jadwal audit | `Admin/JadwalAuditController.php` |
| **Laporan** | Generate laporan Excel/PDF | `Admin/LaporanController.php` |
| **Notifikasi** | Manajemen notifikasi sistem | `Admin/NotificationController.php` |

### 7.2 Fitur Pengurus

| Fitur | Deskripsi | Lokasi File |
|-------|-----------|-------------|
| **Dashboard** | Overview inventori dan statistik | `Pengurus/DashboardController.php` |
| **Barang Masuk** | Pencatatan barang masuk, update stok | `Pengurus/BarangMasukController.php` |
| **Barang Keluar** | Pencatatan barang keluar, update stok | `Pengurus/BarangKeluarController.php` |
| **Peminjaman** | Manajemen peminjaman barang | `Pengurus/PeminjamanController.php` |
| **Perawatan** | Jadwal dan tracking perawatan | `Pengurus/PerawatanController.php` |
| **Pengajuan** | Buat dan kelola pengajuan pengadaan | `Pengurus/PengajuanController.php` |
| **Audit** | Lakukan audit barang | `Pengurus/AuditController.php` |

### 7.3 Fitur Bendahara

| Fitur | Deskripsi | Lokasi File |
|-------|-----------|-------------|
| **Dashboard** | Overview keuangan dan pengajuan | `Bendahara/DashboardController.php` |
| **Analisis TOPSIS** | Analisis dan ranking pengajuan | `Bendahara/AnalisisTopsisController.php` |
| **Verifikasi** | Setujui/tolak pengajuan | `Bendahara/VerifikasiPengadaanController.php` |
| **Manajemen Kas** | CRUD transaksi kas | `Bendahara/KasController.php` |
| **Laporan** | Laporan keuangan dan pengadaan | `Bendahara/LaporanController.php` |

### 7.4 Fitur Umum

- **Autentikasi**: Login/logout dengan role-based access
- **Export Data**: Excel dan PDF untuk berbagai laporan
- **File Upload**: Upload dokumen pendukung pengajuan
- **Pencarian & Filter**: Fitur search dan filter di berbagai modul
- **Pagination**: Pagination untuk daftar data yang panjang

---

## 8. KEAMANAN DAN KONTROL AKSES

### 8.1 Autentikasi

**Lokasi**: `app/Http/Controllers/Auth/LoginController.php`

- Password di-hash menggunakan bcrypt
- Session-based authentication
- Middleware `auth` untuk proteksi route

### 8.2 Authorization (Role-Based Access Control)

**Middleware**: Custom middleware untuk pengecekan role

```php
// routes/web.php
Route::group(['middleware' => ['auth', 'role:admin']], function() {
    // Routes untuk Admin
});

Route::group(['middleware' => ['auth', 'role:pengurus']], function() {
    // Routes untuk Pengurus
});

Route::group(['middleware' => ['auth', 'role:bendahara']], function() {
    // Routes untuk Bendahara
});
```

### 8.3 Kontrol Akses Per Data

**Contoh**: Pengajuan hanya bisa di-edit oleh pemilik dan jika status masih 'pending'

```php
// PengajuanController.php (line 80-82)
if ($pengajuan->user_id !== auth()->id() || $pengajuan->status !== 'pending') {
    abort(403);
}
```

### 8.4 Validasi Input

- **Server-side validation**: Menggunakan Laravel Validation
- **File upload validation**: Mime type dan size limit
- **SQL Injection Protection**: Menggunakan Eloquent ORM (parameterized queries)
- **XSS Protection**: Blade template auto-escape

### 8.5 Keamanan File

- File upload disimpan di `storage/app/public/pengajuan_files`
- Validasi mime type: pdf, doc, docx
- Max file size: 2MB
- File dihapus otomatis saat pengajuan dihapus

---

## 9. ANTARMUKA PENGGUNA

### 9.1 Desain UI/UX

- **Framework**: Bootstrap 5 + Tailwind CSS
- **Responsive Design**: Mobile-friendly
- **Color Scheme**: Konsisten dengan tema gereja
- **Icons**: Font Awesome
- **Charts**: Chart.js untuk visualisasi data

### 9.2 Layout Struktur

```
┌─────────────────────────────────────────┐
│           NAVBAR (Top)                   │
├──────────┬──────────────────────────────┤
│          │                              │
│ SIDEBAR  │      MAIN CONTENT            │
│ (Menu)   │      (Dynamic Content)       │
│          │                              │
│          │                              │
├──────────┴──────────────────────────────┤
│           FOOTER                        │
└─────────────────────────────────────────┘
```

### 9.3 Halaman-Halaman Penting

#### **Halaman Login**
- Form login dengan validasi
- Redirect berdasarkan role setelah login

#### **Dashboard**
- Statistik cards (total barang, stok kritis, dll)
- Grafik transaksi (Chart.js)
- Quick actions

#### **Form Pengajuan**
- Multi-step form (data barang + kriteria)
- File upload dengan preview
- Real-time validation

#### **Hasil Analisis TOPSIS**
- Tabel ranking pengajuan
- Detail perhitungan (matriks, jarak, preferensi)
- Visualisasi ranking

#### **Laporan**
- Filter berdasarkan tanggal/kategori
- Export ke Excel/PDF
- Preview sebelum download

---

## 10. KESIMPULAN

### 10.1 Ringkasan Implementasi

Sistem Inventori Gereja HKBP Setia Mekar telah berhasil diimplementasikan dengan fitur-fitur utama:

1. ✅ **Sistem Manajemen Inventori Lengkap**: CRUD barang, transaksi, peminjaman, perawatan
2. ✅ **Sistem Pengajuan Terstruktur**: Dengan validasi dan tracking status
3. ✅ **Implementasi TOPSIS**: Perhitungan otomatis untuk prioritas pengadaan
4. ✅ **Multi-Role System**: Admin, Pengurus, Bendahara dengan akses berbeda
5. ✅ **Laporan dan Export**: Excel dan PDF untuk berbagai kebutuhan
6. ✅ **Keamanan**: Autentikasi, authorization, validasi input

### 10.2 Kelebihan Sistem

- **Terstruktur**: Menggunakan arsitektur MVC yang rapi
- **Scalable**: Mudah ditambah fitur baru
- **User-Friendly**: Interface yang mudah digunakan
- **Akurat**: Perhitungan TOPSIS sesuai standar akademis
- **Terintegrasi**: Semua modul terhubung dengan baik

### 10.3 Rekomendasi Pengembangan

1. **Testing**: Tambahkan unit test dan integration test
2. **Documentation**: Tambahkan API documentation jika diperlukan
3. **Performance**: Optimasi query untuk data besar
4. **Backup**: Implementasi backup otomatis database
5. **Notification**: Sistem notifikasi real-time (email/push)

---

## REFERENSI

1. Laravel Documentation: https://laravel.com/docs
2. TOPSIS Method: Hwang, C. L., & Yoon, K. (1981). Multiple Attribute Decision Making: Methods and Applications
3. Bootstrap Documentation: https://getbootstrap.com/docs
4. Chart.js Documentation: https://www.chartjs.org/docs

---

**Dokumen ini dibuat untuk keperluan penulisan Bab 4 Skripsi**
**Sistem Inventori Gereja HKBP Setia Mekar**
**Tahun 2025**

