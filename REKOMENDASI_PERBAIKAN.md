# REKOMENDASI PERBAIKAN SISTEM

## 1. PERBAIKAN KODE

### 1.1 Tambahkan Dokumentasi pada AnalisisTopsisController

**File:** `app/Http/Controllers/Bendahara/AnalisisTopsisController.php`

**Rekomendasi:**
```php
/**
 * Menghitung nilai TOPSIS untuk pengajuan pengadaan barang
 * 
 * Metode TOPSIS (Technique for Order Preference by Similarity to Ideal Solution)
 * melakukan 7 langkah perhitungan:
 * 1. Membuat matriks keputusan dari nilai kriteria
 * 2. Normalisasi matriks dengan rumus: r_ij = x_ij / √(Σ x_ij²)
 * 3. Membuat matriks terbobot: y_ij = r_ij × w_j
 * 4. Menentukan solusi ideal positif dan negatif
 * 5. Menghitung jarak Euclidean ke solusi ideal
 * 6. Menghitung nilai preferensi: V_i = D-_i / (D+_i + D-_i)
 * 7. Perankingan berdasarkan nilai preferensi (descending)
 * 
 * @param Collection $pengajuans Koleksi pengajuan yang akan dianalisis
 * @param Collection $kriterias Koleksi kriteria yang digunakan
 * 
 * @return array Array berisi:
 *   - hasil: Array hasil ranking dengan nilai preferensi
 *   - matriksKeputusan: Matriks keputusan awal
 *   - matriksNormalisasi: Matriks setelah normalisasi
 *   - matriksTerbobot: Matriks setelah pembobotan
 *   - solusiIdealPositif: Array solusi ideal positif
 *   - solusiIdealNegatif: Array solusi ideal negatif
 *   - jarakPositif: Array jarak ke solusi ideal positif
 *   - jarakNegatif: Array jarak ke solusi ideal negatif
 *   - kriterias: Koleksi kriteria yang digunakan
 */
private function hitungTopsis($pengajuans, $kriterias)
{
    // ... kode existing
}
```

### 1.2 Tambahkan Validasi Data Sebelum Perhitungan

**File:** `app/Http/Controllers/Bendahara/AnalisisTopsisController.php`

**Lokasi:** Di awal method `hasil()`

**Kode Tambahan:**
```php
public function hasil()
{
    // Ambil semua pengajuan dengan status pending yang sudah memiliki nilai kriteria
    // NOTE: implementasi proyek saat ini tidak menggunakan kolom `ketersediaan_dana`.
    // Ambil pengajuan yang memiliki data harga_satuan (digunakan untuk menghitung persentase biaya)
    $pengajuans = Pengajuan::where('status', 'pending')
        ->whereNotNull('urgensi')
        ->whereNotNull('ketersediaan_stok')
        ->whereNotNull('harga_satuan')
        ->get();

    // Ambil semua kriteria
    $kriterias = Kriteria::all();

    // TAMBAHAN: Validasi data
    if ($pengajuans->isEmpty() || $kriterias->isEmpty()) {
        return redirect()->route('bendahara.analisis.index')
            ->with('error', 'Tidak ada data untuk dianalisis');
    }

    // TAMBAHAN: Validasi bobot kriteria
    $totalBobot = $kriterias->sum('bobot');
    if (abs($totalBobot - 1.0) > 0.01) { // Toleransi 0.01
        \Log::warning('Total bobot kriteria tidak sama dengan 1.0: ' . $totalBobot);
        return redirect()->route('bendahara.analisis.index')
            ->with('warning', 'Peringatan: Total bobot kriteria tidak sama dengan 1.0');
    }

    // TAMBAHAN: Validasi nilai kriteria
    foreach ($pengajuans as $pengajuan) {
        if ($pengajuan->urgensi < 1 || $pengajuan->urgensi > 5) {
            \Log::error('Nilai urgensi tidak valid untuk pengajuan ' . $pengajuan->id);
            return redirect()->route('bendahara.analisis.index')
                ->with('error', 'Nilai kriteria tidak valid');
        }
    }

    // Hitung TOPSIS
    $topsisData = $this->hitungTopsis($pengajuans, $kriterias);

    return view('bendahara.analisis.hasil', [
        'hasil' => $topsisData['hasil'],
        'kriterias' => $kriterias,
        'matriksKeputusan' => $topsisData['matriksKeputusan'],
        'matriksNormalisasi' => $topsisData['matriksNormalisasi'],
        'matriksTerbobot' => $topsisData['matriksTerbobot'],
        'solusiIdealPositif' => $topsisData['solusiIdealPositif'],
        'solusiIdealNegatif' => $topsisData['solusiIdealNegatif'],
        'jarakPositif' => $topsisData['jarakPositif'],
        'jarakNegatif' => $topsisData['jarakNegatif'],
        'pengajuans' => $pengajuans
    ]);
}
```

### 1.3 Tambahkan Error Handling di hitungTopsis()

**File:** `app/Http/Controllers/Bendahara/AnalisisTopsisController.php`

**Rekomendasi:**
```php
private function hitungTopsis($pengajuans, $kriterias)
{
    try {
        // Langkah 1: Matriks Keputusan (X)
        $matriksKeputusan = [];
        foreach ($pengajuans as $pengajuan) {
            // K3 pada implementasi saat ini dihitung sebagai persentase biaya terhadap saldo kas
            $totalHarga = $pengajuan->harga_satuan * $pengajuan->jumlah;
            $anggaran = \App\Models\Kas::getSaldo();
            $persentaseBiaya = ($anggaran > 0) ? ($totalHarga / $anggaran) * 100 : 0;

            $row = [
                $pengajuan->urgensi,           // K1 - Tingkat Urgensi (Benefit)
                $pengajuan->ketersediaan_stok, // K2 - Ketersediaan Stok (Cost)
                $persentaseBiaya               // K3 - Persentase Biaya (Cost)
            ];
            $matriksKeputusan[] = $row;
        }

        // Validasi matriks keputusan
        if (empty($matriksKeputusan)) {
            throw new \Exception('Matriks keputusan kosong');
        }

        // ... rest of the code ...

        return [
            'hasil' => $hasil,
            'matriksKeputusan' => $matriksKeputusan,
            'matriksNormalisasi' => $matriksNormalisasi,
            'matriksTerbobot' => $matriksTerbobot,
            'solusiIdealPositif' => $solusiIdealPositif,
            'solusiIdealNegatif' => $solusiIdealNegatif,
            'jarakPositif' => $jarakPositif,
            'jarakNegatif' => $jarakNegatif,
            'kriterias' => $kriterias
        ];
    } catch (\Exception $e) {
        \Log::error('Error dalam perhitungan TOPSIS: ' . $e->getMessage());
        throw $e;
    }
}
```

---

## 2. PERBAIKAN FITUR

### 2.1 Tambahkan Fitur Export Hasil TOPSIS

**Deskripsi:** Memungkinkan bendahara mengekspor hasil analisis TOPSIS ke Excel

**File Baru:** `app/Exports/AnalisisTopsisExport.php`

```php
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisisTopsisExport implements FromCollection, WithHeadings, WithStyles
{
    protected $hasil;
    protected $kriterias;

    public function __construct($hasil, $kriterias)
    {
        $this->hasil = $hasil;
        $this->kriterias = $kriterias;
    }

    public function collection()
    {
        $data = [];
        foreach ($this->hasil as $index => $item) {
            $data[] = [
                'ranking' => $index + 1,
                'kode_pengajuan' => $item['pengajuan']->kode_pengajuan,
                'nama_barang' => $item['pengajuan']->nama_barang,
                'pengaju' => $item['pengajuan']->user->name,
                'urgensi' => $item['pengajuan']->urgensi,
                'stok' => $item['pengajuan']->ketersediaan_stok,
                'dana' => $item['pengajuan']->ketersediaan_dana,
                'd_plus' => round($item['d_plus'], 4),
                'd_minus' => round($item['d_minus'], 4),
                'nilai_preferensi' => round($item['nilai_preferensi'], 4),
            ];
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Ranking',
            'Kode Pengajuan',
            'Nama Barang',
            'Pengaju',
            'Urgensi (K1)',
            'Stok (K2)',
            'Dana (K3)',
            'D+',
            'D-',
            'Nilai Preferensi',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D3D3D3']]],
        ];
    }
}
```

**Tambahkan Route:**
```php
// Di routes/web.php
Route::get('/analisis/export', [AnalisisTopsisController::class, 'export'])
    ->name('bendahara.analisis.export');
```

**Tambahkan Method di Controller:**
```php
public function export()
{
    $hasil = AnalisisTopsis::with('pengajuan.user')
        ->orderBy('ranking')
        ->get()
        ->map(function ($item) {
            return [
                'pengajuan' => $item->pengajuan,
                'nilai_preferensi' => $item->nilai_preferensi,
                'd_plus' => 0, // Perlu disimpan di database
                'd_minus' => 0 // Perlu disimpan di database
            ];
        })
        ->toArray();

    $kriterias = Kriteria::all();

    return Excel::download(
        new AnalisisTopsisExport($hasil, $kriterias),
        'hasil_analisis_topsis_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
    );
}
```

### 2.2 Tambahkan Fitur Riwayat Analisis

**Deskripsi:** Menyimpan riwayat setiap kali analisis TOPSIS dilakukan

**Migration Baru:**
```php
Schema::create('riwayat_analisis_topsis', function (Blueprint $table) {
    $table->id();
    $table->timestamp('tanggal_analisis');
    $table->integer('jumlah_pengajuan');
    $table->json('hasil_ranking'); // Simpan hasil ranking
    $table->unsignedBigInteger('user_id'); // Bendahara yang melakukan analisis
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users');
});
```

**Model Baru:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatAnalisisTopsis extends Model
{
    protected $table = 'riwayat_analisis_topsis';
    
    protected $fillable = [
        'tanggal_analisis',
        'jumlah_pengajuan',
        'hasil_ranking',
        'user_id'
    ];

    protected $casts = [
        'hasil_ranking' => 'array',
        'tanggal_analisis' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 2.3 Tambahkan Visualisasi Grafik Hasil TOPSIS

**Deskripsi:** Menampilkan grafik perbandingan nilai preferensi

**Tambahkan di View `bendahara.analisis.hasil`:**
```blade
<!-- Grafik Nilai Preferensi -->
<div class="flex flex-wrap -mx-3 mb-6">
    <div class="w-full max-w-full px-3">
        <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
            <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">
                <h6 class="mb-0 font-bold">Grafik Nilai Preferensi</h6>
            </div>
            <div class="flex-auto px-0 pt-0 pb-2">
                <div class="p-6">
                    <canvas id="preferensiChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('preferensiChart').getContext('2d');
    const labels = @json($hasil->pluck('pengajuan.nama_barang'));
    const data = @json($hasil->pluck('nilai_preferensi'));
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nilai Preferensi',
                data: data,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1
                }
            }
        }
    });
</script>
@endpush
```

---

## 3. PERBAIKAN DATABASE

### 3.1 Tambahkan Kolom di Tabel AnalisisTopsis

**Migration:**
```php
Schema::table('analisis_topsis', function (Blueprint $table) {
    $table->float('d_plus')->nullable()->after('nilai_preferensi');
    $table->float('d_minus')->nullable()->after('d_plus');
    $table->timestamp('tanggal_analisis')->nullable()->after('d_minus');
});
```

**Alasan:** Menyimpan nilai D+ dan D- untuk referensi dan audit

### 3.2 Tambahkan Index pada Tabel Pengajuan

**Migration:**
```php
Schema::table('pengajuan', function (Blueprint $table) {
    $table->index('status');
    $table->index('user_id');
    $table->index(['status', 'created_at']);
});
```

**Alasan:** Meningkatkan performa query saat mengambil pengajuan pending

---

## 4. PERBAIKAN KEAMANAN

### 4.1 Tambahkan Audit Log

**File Baru:** `app/Models/AuditLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

**Tambahkan di AnalisisTopsisController:**
```php
public function hasil()
{
    // ... existing code ...

    // Log aktivitas
    \App\Models\AuditLog::create([
        'user_id' => auth()->id(),
        'action' => 'analisis_topsis',
        'model' => 'Pengajuan',
        'model_id' => null,
        'old_values' => null,
        'new_values' => [
            'jumlah_pengajuan' => $pengajuans->count(),
            'tanggal_analisis' => now()
        ],
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);

    // ... rest of code ...
}
```

### 4.2 Tambahkan Rate Limiting

**File:** `app/Http/Controllers/Bendahara/AnalisisTopsisController.php`

```php
public function hasil()
{
    // Rate limiting: maksimal 10 analisis per jam
    $rateLimiter = \Illuminate\Support\Facades\RateLimiter::attempt(
        'analisis-topsis:' . auth()->id(),
        10,
        function () {
            // Proses analisis
        },
        60 * 60 // 1 jam
    );

    if (!$rateLimiter) {
        return redirect()->route('bendahara.analisis.index')
            ->with('error', 'Terlalu banyak permintaan analisis. Coba lagi dalam beberapa saat.');
    }

    // ... rest of code ...
}
```

---

## 5. PERBAIKAN TESTING

### 5.1 Unit Test untuk TOPSIS

**File Baru:** `tests/Unit/AnalisisTopsisTest.php`

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Pengajuan;
use App\Models\Kriteria;
use App\Http\Controllers\Bendahara\AnalisisTopsisController;

class AnalisisTopsisTest extends TestCase
{
    public function test_normalisasi_matriks()
    {
        // Test normalisasi matriks
        $controller = new AnalisisTopsisController();
        
        // Data test
        $pengajuans = collect([
            (object)['urgensi' => 8, 'ketersediaan_stok' => 4, 'ketersediaan_dana' => 10],
            (object)['urgensi' => 6, 'ketersediaan_stok' => 6, 'ketersediaan_dana' => 8],
            (object)['urgensi' => 9, 'ketersediaan_stok' => 2, 'ketersediaan_dana' => 6],
        ]);

        $kriterias = collect([
            (object)['bobot' => 0.30, 'tipe' => 'benefit'],
            (object)['bobot' => 0.25, 'tipe' => 'cost'],
            (object)['bobot' => 0.45, 'tipe' => 'benefit'],
        ]);

        // Panggil method (perlu di-refactor menjadi public atau protected)
        $result = $controller->hitungTopsis($pengajuans, $kriterias);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('hasil', $result);
        $this->assertArrayHasKey('matriksKeputusan', $result);
        $this->assertCount(3, $result['hasil']);
    }

    public function test_nilai_preferensi_range()
    {
        // Test bahwa nilai preferensi dalam range 0-1
        // ... test code ...
    }

    public function test_perankingan_descending()
    {
        // Test bahwa hasil diurutkan descending
        // ... test code ...
    }
}
```

---

## 6. CHECKLIST PERBAIKAN

- [ ] Tambahkan dokumentasi pada method `hitungTopsis()`
- [ ] Tambahkan validasi data sebelum perhitungan
- [ ] Tambahkan error handling di `hitungTopsis()`
- [ ] Implementasi fitur export ke Excel
- [ ] Implementasi fitur riwayat analisis
- [ ] Tambahkan visualisasi grafik
- [ ] Tambahkan kolom D+ dan D- di database
- [ ] Tambahkan index pada tabel
- [ ] Implementasi audit log
- [ ] Implementasi rate limiting
- [ ] Buat unit test untuk TOPSIS
- [ ] Update dokumentasi API
- [ ] Test dengan data real
- [ ] Deploy ke production

---

## 7. PRIORITAS PERBAIKAN

### Prioritas Tinggi (Segera)
1. Tambahkan dokumentasi kode
2. Tambahkan validasi data
3. Tambahkan error handling

### Prioritas Sedang (1-2 Minggu)
1. Implementasi fitur export
2. Tambahkan audit log
3. Buat unit test

### Prioritas Rendah (1 Bulan)
1. Visualisasi grafik
2. Riwayat analisis
3. Rate limiting

---

**Dokumen ini berisi rekomendasi perbaikan untuk meningkatkan kualitas sistem**
