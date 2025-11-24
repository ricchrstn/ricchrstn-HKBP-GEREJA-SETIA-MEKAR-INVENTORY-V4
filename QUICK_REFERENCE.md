# QUICK REFERENCE - SISTEM PENGAJUAN & TOPSIS

## 🚀 QUICK START

### Untuk Pengurus (Pengajuan Barang)

```
1. Login sebagai Pengurus
2. Buka menu "Pengajuan" → "Buat Pengajuan"
3. Isi form:
   - Nama Barang: [nama]
   - Spesifikasi: [detail]
   - Jumlah: [angka]
   - Satuan: [unit]
   - Alasan: [penjelasan]
   - Kebutuhan: [tanggal]
   - Urgensi (K1): [1-10]
   - Ketersediaan Stok (K2): [2/4/6/8/10]
   - File: [optional]
4. Klik "Simpan"
5. Sistem otomatis:
   - Generate kode pengajuan
   - Set status = pending
   - Hitung K3 dari saldo kas
```

### Untuk Bendahara (Analisis TOPSIS)

```
1. Login sebagai Bendahara
2. Buka menu "Analisis" → "Analisis TOPSIS"
3. Lihat daftar pengajuan pending dengan kriteria
4. Klik "Lihat Hasil Analisis"
5. Sistem menghitung TOPSIS:
   - Normalisasi data
   - Pembobotan
   - Hitung solusi ideal
   - Hitung jarak
   - Hitung preferensi
   - Ranking
6. Lihat hasil ranking
7. Klik "Setujui" atau "Tolak" untuk setiap pengajuan
```

---

## 📊 TABEL REFERENSI CEPAT

### Kriteria TOPSIS

| K | Nama | Bobot | Tipe | Range | Interpretasi |
|---|------|-------|------|-------|--------------|
| K1 | Urgensi | 0.30 | Benefit | 1-5 | Semakin tinggi = semakin prioritas (implementasi saat ini: 1..5) |
| K2 | Stok | 0.25 | Cost | 1-5 | Semakin rendah = semakin prioritas (implementasi saat ini: 1..5) |
| K3 | Persentase Biaya | 0.45 | Cost | Persentase (%) | Dihitung: (harga_satuan × jumlah) / saldoKas × 100; semakin kecil = semakin prioritas |

### Nilai K3 Otomatis (implementasi proyek)

Pada implementasi proyek saat ini, K3 tidak diambil dari mapping tetap tetapi dihitung sebagai persentase biaya pengadaan:

```
persentaseBiaya = (harga_satuan * jumlah) / saldoKas * 100
```

Nilai ini diperlakukan sebagai **Cost** (semakin kecil lebih baik). Jika Anda memiliki kebutuhan untuk mengembalikan
skema diskret (mis. 2,4,6,8,10), pertimbangkan untuk menambahkan mapping di model atau menyimpan `ketersediaan_dana`.

Catatan: Di `routes/web.php` ada route POST `analisis/update-nilai-otomatis` namun `AnalisisTopsisController` saat ini
tidak menyediakan method `updateNilaiOtomatis()`. Tambahkan method tersebut jika Anda ingin fitur update K3 via route.

### Interpretasi Nilai Preferensi

| Range | Interpretasi | Rekomendasi |
|-------|--------------|-------------|
| 0.7 - 1.0 | Sangat Baik | ✅ SETUJUI |
| 0.4 - 0.7 | Baik | ⚠️ PERTIMBANGKAN |
| 0.0 - 0.4 | Kurang Baik | ❌ TOLAK/TUNDA |

---

## 🔑 KEY FILES

### Controllers
```
app/Http/Controllers/Pengurus/PengajuanController.php
├── index()      → Daftar pengajuan user
├── create()     → Form buat pengajuan
├── store()      → Simpan pengajuan
├── show()       → Detail pengajuan
├── edit()       → Form edit pengajuan
├── update()     → Update pengajuan
└── destroy()    → Hapus pengajuan

app/Http/Controllers/Bendahara/AnalisisTopsisController.php
├── index()      → Daftar pengajuan dengan kriteria
├── hasil()      → Hasil analisis TOPSIS
├── updateNilaiOtomatis() → Update K3 otomatis
└── hitungTopsis() → Hitung TOPSIS (private)
```

### Models
```
app/Models/Pengajuan.php
├── user()       → Relasi ke User
├── analisisTopsis() → Relasi ke AnalisisTopsis
├── generateKode() → Generate kode otomatis
└── updateKetersediaanDanaOtomatis() → Update K3

app/Models/Kriteria.php
├── nilaiPengadaanKriterias() → Relasi ke nilai

app/Models/AnalisisTopsis.php
├── pengajuan()  → Relasi ke Pengajuan
```

### Views
```
resources/views/pengurus/pengajuan/
├── index.blade.php  → Daftar pengajuan
├── create.blade.php → Form buat
├── edit.blade.php   → Form edit
└── show.blade.php   → Detail

resources/views/bendahara/analisis/
├── index.blade.php  → Daftar dengan kriteria
└── hasil.blade.php  → Hasil TOPSIS
```

### Routes
```
routes/web.php
├── pengurus.pengajuan.* → CRUD pengajuan
├── bendahara.analisis.index → Daftar analisis
├── bendahara.analisis.hasil → Hasil TOPSIS
└── bendahara.analisis.update-nilai-otomatis → Update K3
```

---

## 🔐 KONTROL AKSES

### Pengajuan (Pengurus)

| Operasi | Syarat | Pembatasan |
|---------|--------|-----------|
| CREATE | - | Hanya pengurus |
| READ | - | Hanya pemilik |
| UPDATE | status = pending | Hanya pemilik |
| DELETE | status = pending | Hanya pemilik |

### Analisis (Bendahara)

| Operasi | Syarat | Pembatasan |
|---------|--------|-----------|
| VIEW | - | Hanya bendahara |
| ANALISIS | Ada pengajuan pending | Hanya bendahara |
| VERIFIKASI | - | Hanya bendahara |

---

## 📝 VALIDASI INPUT

### Pengajuan

```
nama_barang:        required, string, max:255
spesifikasi:        nullable, string
jumlah:             required, integer, min:1
satuan:             required, string, max:50
alasan:             required, string
kebutuhan:          required, date, after_or_equal:today
file_pengajuan:     nullable, file, mimes:pdf,doc,docx, max:2048
urgensi (K1):       required, integer, min:1, max:10
ketersediaan_stok (K2): required, integer, in:2,4,6,8,10
```

---

## 🧮 RUMUS TOPSIS

### Normalisasi
```
r_ij = x_ij / √(Σ x_ij²)
```

### Pembobotan
```
y_ij = r_ij × w_j
```

### Jarak Positif
```
D+_i = √(Σ(y_ij - A+_j)²)
```

### Jarak Negatif
```
D-_i = √(Σ(y_ij - A-_j)²)
```

### Preferensi
```
V_i = D-_i / (D+_i + D-_i)
```

---

## 🐛 TROUBLESHOOTING

### Masalah: Pengajuan tidak bisa diedit
**Solusi:** Pastikan status = 'pending'. Pengajuan yang sudah diverifikasi tidak bisa diedit.

### Masalah: K3 tidak terupdate
**Solusi:** Klik tombol "Perbarui Nilai K3 Otomatis" di halaman analisis.

### Masalah: Analisis tidak bisa dijalankan
**Solusi:** Pastikan ada minimal 1 pengajuan pending dengan semua kriteria terisi.

### Masalah: Hasil ranking tidak sesuai ekspektasi
**Solusi:** Periksa nilai K1, K2, K3 setiap pengajuan. Bobot K3 (0.45) paling besar.

---

## 📊 CONTOH SKENARIO

### Skenario 1: Pengajuan Meja vs Kursi

```
Meja:
- K1 (Urgensi) = 8 (tinggi)
- K2 (Stok) = 4 (sedang)
- K3 (Dana) = 10 (sangat tinggi)
→ Ranking 1 (V = 0.68)

Kursi:
- K1 (Urgensi) = 6 (sedang)
- K2 (Stok) = 6 (tinggi)
- K3 (Dana) = 8 (tinggi)
→ Ranking 2 (V = 0.28)

Alasan: Meja lebih prioritas karena K3 lebih tinggi
```

### Skenario 2: Pengaruh Saldo Kas

```
Saldo Kas = Rp 9.000.000
→ Semua pengajuan baru: K3 = 10

Saldo Kas = Rp 1.500.000
→ Semua pengajuan baru: K3 = 2

Kesimpulan: K3 otomatis berubah sesuai saldo kas
```

---

## 🔄 WORKFLOW LENGKAP

```
┌─────────────────────────────────────────────────────────┐
│ PENGURUS: Buat Pengajuan                                │
├─────────────────────────────────────────────────────────┤
│ 1. Input data pengajuan                                 │
│ 2. Sistem generate kode & hitung K3                     │
│ 3. Status = pending                                     │
│ 4. Pengajuan tersimpan                                  │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ BENDAHARA: Lihat Pengajuan                              │
├─────────────────────────────────────────────────────────┤
│ 1. Buka halaman Analisis TOPSIS                         │
│ 2. Lihat daftar pengajuan pending                       │
│ 3. Lihat K1, K2, K3 setiap pengajuan                    │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ BENDAHARA: Jalankan Analisis TOPSIS                     │
├─────────────────────────────────────────────────────────┤
│ 1. Klik "Lihat Hasil Analisis"                          │
│ 2. Sistem hitung TOPSIS (7 langkah)                     │
│ 3. Tampilkan ranking & detail perhitungan               │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ BENDAHARA: Verifikasi Pengajuan                         │
├─────────────────────────────────────────────────────────┤
│ 1. Lihat hasil ranking                                  │
│ 2. Klik "Setujui" atau "Tolak"                          │
│ 3. Status pengajuan berubah                             │
│ 4. Pengajuan diproses sesuai keputusan                  │
└─────────────────────────────────────────────────────────┘
```

---

## 📞 INFORMASI PENTING

### Database Tables
- `pengajuan` - Data pengajuan barang
- `kriteria` - Kriteria TOPSIS
- `analisis_topsis` - Hasil analisis
- `kas` - Data saldo kas

### Bobot Kriteria (FIXED)
- K1: 0.30 (Urgensi)
- K2: 0.25 (Stok)
- K3: 0.45 (Dana)
- **Total: 1.00**

### Status Pengajuan
- `pending` - Menunggu analisis
- `disetujui` - Disetujui bendahara
- `ditolak` - Ditolak bendahara

---

## ✅ CHECKLIST PENGGUNAAN

### Sebelum Analisis
- [ ] Ada minimal 1 pengajuan pending
- [ ] Semua pengajuan memiliki K1, K2
- [ ] Saldo kas sudah diinput
- [ ] Kriteria sudah dikonfigurasi

### Saat Analisis
- [ ] Klik "Lihat Hasil Analisis"
- [ ] Tunggu perhitungan selesai
- [ ] Lihat hasil ranking
- [ ] Periksa detail perhitungan

### Setelah Analisis
- [ ] Verifikasi setiap pengajuan
- [ ] Simpan keputusan
- [ ] Update status pengajuan
- [ ] Dokumentasikan keputusan

---

**Quick Reference - Sistem Pengajuan & TOPSIS**
**Versi: 1.0**
**Last Updated: 2025**
