# ANALISIS SISTEM PENGAJUAN DAN TOPSIS

## 1. ANALISIS ALUR PENGAJUAN (Role: Pengurus)

### 1.1 Alur Pengajuan Barang

#### **Lokasi File:**
- Controller: `app/Http/Controllers/Pengurus/PengajuanController.php`
- Model: `app/Models/Pengajuan.php`
- Routes: `routes/web.php` (lines 217-219)

#### **Alur Proses:**

```
┌─────────────────────────────────────────────────────────────┐
│                    PENGAJUAN BARANG                         │
│                   (Role: Pengurus)                          │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────��───────────────┐
        │  1. CREATE PENGAJUAN (GET)            │
        │  - Tampilkan form pengajuan           │
        │  - Route: pengurus.pengajuan.create   │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  2. STORE PENGAJUAN (POST)            │
        │  - Validasi input                     │
        │  - Generate kode pengajuan otomatis   │
        │  - Set status = 'pending'             │
        │  - Upload file (optional)             │
        │  - Hitung K3 otomatis                 │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  3. INDEX PENGAJUAN (GET)             │
        │  - Tampilkan daftar pengajuan user    │
        │  - Filter: search, status, tanggal    │
        │  - Pagination: 10 per halaman         │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  4. SHOW PENGAJUAN (GET)              │
        │  - Tampilkan detail pengajuan         │
        │  - Hanya pemilik yang bisa lihat      │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  5. EDIT PENGAJUAN (GET)              │
        │  - Tampilkan form edit                │
        │  - Hanya jika status = 'pending'      │
        │  - Hanya pemilik yang bisa edit       │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────���───────────────────┐
        │  6. UPDATE PENGAJUAN (PUT)            │
        │  - Update data pengajuan              │
        │  - Update file (optional)             │
        │  - Hanya jika status = 'pending'      │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  7. DELETE PENGAJUAN (DELETE)         │
        │  - Hapus pengajuan                    │
        │  - Hapus file jika ada                │
        │  - Hanya jika status = 'pending'      │
        │  - Support AJAX request               │
        └───────────────────────────────────────┘
```

### 1.2 Validasi Input Pengajuan

**File:** `app/Http/Controllers/Pengurus/PengajuanController.php` (lines 48-57, 88-97)

```php
Validasi yang dilakukan:
├── nama_barang
│   ├── required
│   ├── string
│   └── max:255
├── spesifikasi
│   ├── nullable
│   └── string
├── jumlah
│   ├── required
│   ├── integer
│   └── min:1
├── satuan
│   ├── required
│   ├── string
│   └── max:50
├── alasan
│   ├── required
│   └── string
├── kebutuhan
│   ├── required
│   ├── date
│   └── after_or_equal:today
├── file_pengajuan
│   ├── nullable
│   ├── file
│   ├── mimes:pdf,doc,docx
│   └── max:2048 (KB)
├── urgensi (K1)
│   ├── required
│   ├── integer
│   ├── min:1
│   └── max:10
└── ketersediaan_stok (K2)
    ├── required
    ├── integer
    └── in:2,4,6,8,10
```

### 1.3 Fitur Otomatis

#### **Kode Pengajuan Otomatis**
```php
Format: PNG + YYYYMMDD + NNN
Contoh: PNG202501010001

Lokasi: app/Models/Pengajuan.php (lines 54-60)
```

#### **Ketersediaan Dana (K3) Otomatis**
```php
Lokasi: app/Models/Pengajuan.php (lines 68-82)

Perhitungan berdasarkan Saldo Kas:
├── Saldo > Rp 8.000.000      → K3 = 10 (Sangat tinggi)
├── Rp 6.000.000 - 8.000.000  → K3 = 8  (Tinggi)
├── Rp 4.000.000 - 5.999.999  → K3 = 6  (Sedang)
├── Rp 2.000.000 - 3.999.999  → K3 = 4  (Rendah)
└── < Rp 2.000.000            → K3 = 2  (Sangat rendah)

Trigger:
- Saat pengajuan dibuat (created event)
- Saat status berubah menjadi pending (updated event)
```

### 1.4 Kontrol Akses

| Operasi | Kondisi | Pembatasan |
|---------|---------|-----------|
| CREATE | - | Hanya pengurus |
| STORE | - | Hanya pengurus |
| INDEX | - | Hanya pengurus (lihat milik sendiri) |
| SHOW | - | Hanya pemilik pengajuan |
| EDIT | status = 'pending' | Hanya pemilik |
| UPDATE | status = 'pending' | Hanya pemilik |
| DELETE | status = 'pending' | Hanya pemilik |

### 1.5 Penanganan File

```php
Lokasi: app/Http/Controllers/Pengurus/PengajuanController.php

Upload:
- Disk: 'public'
- Path: 'pengajuan_files'
- Mimes: pdf, doc, docx
- Max size: 2MB

Delete:
- Otomatis saat update file baru
- Otomatis saat delete pengajuan
```

---

## 2. ANALISIS PERHITUNGAN TOPSIS (Role: Bendahara)

### 2.1 Alur Analisis TOPSIS

#### **Lokasi File:**
- Controller: `app/Http/Controllers/Bendahara/AnalisisTopsisController.php`
- Routes: `routes/web.php` (lines 253-257)

#### **Alur Proses:**

```
┌─────────────────────────────────────────────────────────────┐
│                    ANALISIS TOPSIS                          │
│                   (Role: Bendahara)                         │
└───────��─────────────────────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  1. INDEX (GET)                       │
        │  - Ambil semua pengajuan pending      │
        │  - Update K3 otomatis                 │
        │  - Tampilkan data dengan kriteria     │
        │  - Route: bendahara.analisis.index    │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  2. UPDATE NILAI OTOMATIS (POST)      │
        │  - Update K3 semua pengajuan          │
        │  - Berdasarkan saldo kas terkini      │
        │  - Redirect ke index                  │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        ��  3. HASIL (GET)                       │
        │  - Ambil pengajuan dengan kriteria    │
        │  - Update K3 otomatis                 │
        │  - Hitung TOPSIS                      │
        │  - Tampilkan hasil ranking            │
        │  - Route: bendahara.analisis.hasil    │
        └───────────────────────────────────────┘
```

### 2.2 Kriteria TOPSIS

**Lokasi:** `app/Models/Kriteria.php` dan database

| Kode | Nama | Bobot | Tipe | Deskripsi |
|------|------|-------|------|-----------|
| K1 | Tingkat Urgensi Barang | 0.30 | Benefit | Semakin tinggi, semakin prioritas (implementasi saat ini: skala 1..5) |
| K2 | Ketersediaan Stok Barang | 0.25 | Cost | Semakin rendah, semakin prioritas (implementasi saat ini: skala 1..5) |
| K3 | Persentase Biaya Pengadaan | 0.45 | Cost | Dihitung dari (harga_satuan × jumlah) relatif terhadap saldo kas; semakin kecil persentase = lebih prioritas |

**Total Bobot:** 0.30 + 0.25 + 0.45 = 1.00 ✓

### 2.3 Langkah-Langkah Perhitungan TOPSIS

#### **Langkah 1: Matriks Keputusan (X)**

```
Lokasi: AnalisisTopsisController.php (lines 95-105)

Matriks X berisi nilai kriteria untuk setiap alternatif (pengajuan):
X = [
    [K1_1, K2_1, K3_1],
    [K1_2, K2_2, K3_2],
    ...
    [K1_n, K2_n, K3_n]
]

Sumber data (implementasi saat ini):
- K1 (Urgensi): `pengajuan.urgensi` (skala 1..5)
- K2 (Stok): `pengajuan.ketersediaan_stok` (skala 1..5)
- K3 (Persentase Biaya): dihitung dinamis di `hitungTopsis()` sebagai:

```
persentaseBiaya = (harga_satuan * jumlah) / saldoKas * 100
```

Catatan: Dokumen lama yang menyebut `ketersediaan_dana` (2/4/6/8/10) atau menganggap K3 sebagai Benefit
tidak sesuai dengan implementasi kode saat ini.
```

#### **Langkah 2: Normalisasi Matriks (R)**

```
Lokasi: AnalisisTopsisController.php (lines 107-125)

Rumus: r_ij = x_ij / √(Σ x_ij²)

Proses:
1. Hitung jumlah kuadrat setiap kolom (kriteria)
   jumlahKuadrat[j] = √(Σ x_ij²) untuk j = 1..3

2. Normalisasi setiap elemen
   r_ij = x_ij / jumlahKuadrat[j]

Hasil: Matriks R dengan nilai 0 ≤ r_ij ≤ 1
```

#### **Langkah 3: Matriks Normalisasi Terbobot (Y)**

```
Lokasi: AnalisisTopsisController.php (lines 127-137)

Rumus: y_ij = r_ij × w_j

Proses:
Kalikan setiap elemen matriks normalisasi dengan bobot kriteria:
- y_ij = r_ij × w_j

Bobot:
- w_1 (K1) = 0.30
- w_2 (K2) = 0.25
- w_3 (K3) = 0.45
```

#### **Langkah 4: Solusi Ideal Positif (A+) dan Negatif (A-)**

```
Lokasi: AnalisisTopsisController.php (lines 139-157)

Untuk setiap kriteria j:
- Jika tipe = 'benefit':
  A+_j = max(y_ij)  → nilai tertinggi
  A-_j = min(y_ij)  → nilai terendah

- Jika tipe = 'cost':
  A+_j = min(y_ij)  → nilai terendah
  A-_j = max(y_ij)  → nilai tertinggi

Dalam sistem ini (sesuai kode saat ini):
- K1 (Benefit): A+ = max, A- = min
- K2 (Cost):    A+ = min, A- = max
- K3 (Cost):    A+ = min, A- = max  // karena K3 adalah persentase biaya (lebih kecil lebih baik)
```

#### **Langkah 5: Menghitung Jarak**

```
Lokasi: AnalisisTopsisController.php (lines 159-177)

Jarak ke Solusi Ideal Positif (D+):
D+_i = √(Σ(y_ij - A+_j)²)

Jarak ke Solusi Ideal Negatif (D-):
D-_i = √(Σ(y_ij - A-_j)²)

Proses:
1. Untuk setiap alternatif i:
   - Hitung selisih kuadrat dengan A+ dan A-
   - Jumlahkan semua selisih kuadrat
   - Ambil akar kuadrat
```

#### **Langkah 6: Nilai Preferensi (V)**

```
Lokasi: AnalisisTopsisController.php (lines 179-191)

Rumus: V_i = D-_i / (D+_i + D-_i)

Proses:
1. Untuk setiap alternatif i:
   - Hitung total jarak: D+_i + D-_i
   - Tambahkan epsilon (0.000001) untuk menghindari pembagian 0
   - V_i = D-_i / (D+_i + D-_i + epsilon)

Hasil: 0 ≤ V_i ≤ 1
- V_i mendekati 1 → alternatif lebih baik
- V_i mendekati 0 → alternatif lebih buruk
```

#### **Langkah 7: Perankingan**

```
Lokasi: AnalisisTopsisController.php (lines 193-210)

Proses:
1. Urutkan hasil berdasarkan V_i (descending)
2. Simpan ke database (AnalisisTopsis)
3. Tampilkan ranking dari tertinggi ke terendah
```

### 2.4 Struktur Data yang Dikembalikan

```php
return [
    'hasil' => [
        [
            'pengajuan' => Pengajuan object,
            'nilai_preferensi' => float (0-1),
            'd_plus' => float,
            'd_minus' => float
        ],
        ...
    ],
    'matriksKeputusan' => array 2D,
    'matriksNormalisasi' => array 2D,
    'matriksTerbobot' => array 2D,
    'solusiIdealPositif' => array,
    'solusiIdealNegatif' => array,
    'jarakPositif' => array,
    'jarakNegatif' => array,
    'kriterias' => Collection
];
```

---

## 3. VERIFIKASI PERHITUNGAN TOPSIS

### 3.1 Analisis Kebenaran Perhitungan

#### ✅ **BENAR - Langkah 1: Matriks Keputusan**
```
Status: BENAR
Alasan: 
- Mengambil nilai K1, K2, K3 dari pengajuan dengan benar
- Urutan kolom konsisten: [K1, K2, K3]
```

#### ✅ **BENAR - Langkah 2: Normalisasi Matriks**
```
Status: BENAR
Alasan:
- Rumus normalisasi: r_ij = x_ij / √(Σ x_ij²) ✓
- Perhitungan jumlah kuadrat per kolom benar
- Pembagian setiap elemen dengan akar jumlah kuadrat benar
```

#### ✅ **BENAR - Langkah 3: Matriks Normalisasi Terbobot**
```
Status: BENAR
Alasan:
- Rumus: y_ij = r_ij × w_j ✓
- Bobot diambil dari database dengan benar
- Perkalian dilakukan untuk setiap elemen
```

#### ✅ **BENAR - Langkah 4: Solusi Ideal**
```
Status: BENAR
Alasan:
- Pengecekan tipe kriteria (benefit/cost) benar
- Untuk benefit: A+ = max, A- = min ✓
- Untuk cost: A+ = min, A- = max ✓
- Implementasi sesuai standar TOPSIS
```

#### ✅ **BENAR - Langkah 5: Menghitung Jarak**
```
Status: BENAR
Alasan:
- Rumus D+ = √(Σ(y_ij - A+_j)²) ✓
- Rumus D- = √(Σ(y_ij - A-_j)²) ✓
- Menggunakan sqrt() untuk akar kuadrat
- Perhitungan untuk setiap alternatif benar
```

#### ✅ **BENAR - Langkah 6: Nilai Preferensi**
```
Status: BENAR
Alasan:
- Rumus: V_i = D-_i / (D+_i + D-_i) ✓
- Menambahkan epsilon untuk menghindari pembagian 0 ✓
- Hasil dalam range 0-1 ✓
```

#### ✅ **BENAR - Langkah 7: Perankingan**
```
Status: BENAR
Alasan:
- Mengurutkan berdasarkan nilai preferensi (descending) ✓
- Menyimpan ranking ke database ✓
- Menampilkan hasil dengan urutan yang benar
```

### 3.2 Potensi Masalah dan Rekomendasi

#### **1. Epsilon dalam Pembagian**
```php
// Kode saat ini (BAIK):
$epsilon = 0.000001;
$totalJarak = $jarakPositif[$i] + $jarakNegatif[$i] + $epsilon;
$nilaiV = $jarakNegatif[$i] / $totalJarak;

Status: ✓ BAIK
Alasan: Mencegah pembagian dengan 0 jika kedua jarak = 0
```

#### **2. Penanganan Data Kosong**
```php
// Kode saat ini (BAIK):
if ($pengajuans->isEmpty() || $kriterias->isEmpty()) {
    return redirect()->route('bendahara.analisis.index')
        ->with('error', 'Tidak ada data untuk dianalisis');
}

Status: ✓ BAIK
Alasan: Mencegah error saat tidak ada data
```

#### **3. Update K3 Otomatis**
```php
// Kode saat ini (BAIK):
foreach ($pengajuans as $pengajuan) {
    $pengajuan->updateKetersediaanDanaOtomatis();
}

Status: ✓ BAIK
Alasan: Memastikan K3 selalu up-to-date dengan saldo kas terkini
```

#### **4. Penyimpanan Hasil ke Database**
```php
// Kode saat ini (BAIK):
AnalisisTopsis::updateOrCreate(
    ['pengajuan_id' => $item['pengajuan']->id],
    [
        'nilai_preferensi' => $item['nilai_preferensi'],
        'ranking' => $index + 1
    ]
);

Status: ✓ BAIK
Alasan: Menggunakan updateOrCreate untuk insert/update otomatis
```

---

## 4. INTEGRASI SISTEM

### 4.1 Alur Lengkap Pengajuan hingga Verifikasi

```
┌──────────────────────────────────────────────────────────────┐
│                    ALUR LENGKAP SISTEM                       │
└──────────────────────────────────────────────────────────────┘

FASE 1: PENGAJUAN (Pengurus)
├── Pengurus membuat pengajuan barang
├── Input: nama_barang, spesifikasi, jumlah, satuan, alasan, 
│          kebutuhan, urgensi (K1), ketersediaan_stok (K2)
├── Sistem otomatis:
│   ├── Generate kode pengajuan
│   ├── Set status = 'pending'
│   └── Hitung K3 berdasarkan saldo kas
└── Pengajuan tersimpan di database

FASE 2: ANALISIS (Bendahara)
├── Bendahara membuka halaman analisis
├── Sistem:
│   ├── Ambil semua pengajuan pending
│   ├── Update K3 otomatis
│   └── Tampilkan data dengan kriteria
├── Bendahara klik "Lihat Hasil Analisis"
├── Sistem:
│   ├── Hitung TOPSIS (7 langkah)
│   ├─��� Simpan hasil ke database
│   └── Tampilkan ranking
└── Hasil ditampilkan dengan detail perhitungan

FASE 3: VERIFIKASI (Bendahara)
├── Bendahara melihat hasil ranking
├── Bendahara klik "Setujui" atau "Tolak"
├── Sistem:
│   ├── Update status pengajuan
│   ├── Simpan keterangan
│   └── Redirect ke verifikasi
└── Pengajuan diproses sesuai keputusan
```

### 4.2 Database Relations

```
Pengajuan
├── user_id → User (many-to-one)
├── kategori_id → Kategori (many-to-one)
├── analisisTopsis → AnalisisTopsis (one-to-one)
└── nilaiPengadaanKriterias → NilaiPengadaanKriteria (one-to-many)

Kriteria
└── nilaiPengadaanKriterias → NilaiPengadaanKriteria (one-to-many)

AnalisisTopsis
└── pengajuan_id → Pengajuan (many-to-one)

Kas
└── Digunakan untuk menghitung K3 otomatis
```

---

## 5. KESIMPULAN

### ✅ **PERHITUNGAN TOPSIS SUDAH BENAR**

**Ringkasan Verifikasi:**

| Aspek | Status | Catatan |
|-------|--------|---------|
| Matriks Keputusan | �� BENAR | Data diambil dengan benar |
| Normalisasi | ✅ BENAR | Rumus dan implementasi benar |
| Pembobotan | ✅ BENAR | Bobot total = 1.0 |
| Solusi Ideal | ✅ BENAR | Pengecekan benefit/cost benar |
| Jarak | ✅ BENAR | Rumus Euclidean benar |
| Preferensi | ✅ BENAR | Rumus dan epsilon handling benar |
| Perankingan | ✅ BENAR | Urutan descending benar |
| Penyimpanan | ✅ BENAR | updateOrCreate benar |

### 📋 **REKOMENDASI PERBAIKAN**

1. **Dokumentasi Kode**
   - Tambahkan docblock untuk metode `hitungTopsis()`
   - Jelaskan parameter dan return value

2. **Validasi Input**
   - Pastikan semua pengajuan memiliki K1, K2, K3 sebelum analisis
   - Tambahkan validasi di `hasil()` method

3. **Error Handling**
   - Tambahkan try-catch di `hitungTopsis()`
   - Log error jika ada masalah perhitungan

4. **Performance**
   - Pertimbangkan caching hasil TOPSIS
   - Gunakan query optimization untuk pengajuan besar

5. **Testing**
   - Buat unit test untuk perhitungan TOPSIS
   - Buat test case dengan data sample

---

## 6. REFERENSI TOPSIS

**Metode TOPSIS (Technique for Order Preference by Similarity to Ideal Solution)**

Langkah-langkah standar:
1. Membuat matriks keputusan
2. Normalisasi matriks
3. Membuat matriks terbobot
4. Menentukan solusi ideal positif dan negatif
5. Menghitung jarak ke solusi ideal
6. Menghitung nilai preferensi
7. Perankingan

**Referensi:**
- Hwang, C. L., & Yoon, K. (1981). Multiple Attribute Decision Making: Methods and Applications
- Behzadian, M., et al. (2012). A state-of the-art survey of TOPSIS applications

---

**Dokumen ini dibuat untuk analisis sistem pengajuan dan TOPSIS pada aplikasi Gereja**
**Tanggal: 2025**
