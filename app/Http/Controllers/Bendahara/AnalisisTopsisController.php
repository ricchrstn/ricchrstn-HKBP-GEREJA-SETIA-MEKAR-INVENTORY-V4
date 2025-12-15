<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use App\Models\Kriteria;
use App\Models\AnalisisTopsis;
use App\Models\Kas;
use Illuminate\Http\Request;

class AnalisisTopsisController extends Controller
{
    // Menampilkan halaman utama analisis (form input kriteria belum lengkap)
    public function index()
    {
        $kriterias = Kriteria::all(); // Ambil semua kriteria (harus ada 3: urgensi, ketersediaan stok, biaya)
        $pengajuans = Pengajuan::where('status', 'pending')
            ->whereNotNull('urgensi')
            ->whereNotNull('ketersediaan_stok')
            ->whereNotNull('harga_satuan')
            ->get(); // Ambil pengajuan yang sudah lengkap datanya

        $saldoKas = Kas::getSaldo(); // Ambil saldo kas terkini sebagai total anggaran

        return view('bendahara.analisis.index', compact('kriterias', 'pengajuans', 'saldoKas'));
    }

    /**
     * Tampilkan hasil analisis TOPSIS
     */
    public function hasil()
    {
        // Ambil pengajuan dengan status pending dan semua nilai kriteria sudah diisi
        $pengajuans = Pengajuan::where('status', 'pending')
            ->whereNotNull('urgensi')
            ->whereNotNull('ketersediaan_stok')
            ->whereNotNull('harga_satuan')
            ->get();

        // Ambil definisi kriteria (K1: urgensi, K2: ketersediaan stok, K3: persentase biaya)
        $kriterias = Kriteria::all();

        // Validasi: minimal ada data pengajuan dan tepat 3 kriteria
        if ($pengajuans->isEmpty() || $kriterias->count() !== 3) {
            return redirect()->route('bendahara.analisis.index')
                ->with('error', 'Tidak cukup data untuk dianalisis. Pastikan ada 3 kriteria dan pengajuan lengkap.');
        }

        $saldoKas = Kas::getSaldo(); // Total anggaran dari kas

        // Jalankan proses perhitungan TOPSIS
        $topsisData = $this->hitungTopsis($pengajuans, $kriterias);

        // Kirim semua data hasil ke view untuk ditampilkan
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
            'pengajuans' => $pengajuans,
            'saldoKas' => $saldoKas
        ]);
    }

    // Fungsi inti: melakukan perhitungan metode TOPSIS
    private function hitungTopsis($pengajuans, $kriterias)
    {
        // Ambil total anggaran (saldo kas) untuk menghitung persentase biaya (K3)
        $anggaran = Kas::getSaldo();
        if ($anggaran <= 0) {
            return redirect()->route('bendahara.analisis.index')
                ->with('error', 'Saldo kas tidak valid. Tidak dapat melakukan analisis.');
        }

        // === LANGKAH 1: MEMBENTUK MATRIKS KEPUTUSAN (X) ===
        $matriksKeputusan = [];
        foreach ($pengajuans as $pengajuan) {
            $totalHarga = $pengajuan->harga_satuan * $pengajuan->jumlah;
            $persentaseBiaya = ($totalHarga / $anggaran) * 100; // <-- INI ADALAH K3 (Cost)

            // Susun nilai kriteria per pengajuan:
            // - K1: Urgensi (Benefit → semakin tinggi, semakin baik)
            // - K2: Ketersediaan Stok (Cost → semakin rendah, semakin baik)
            // - K3: Persentase Biaya terhadap anggaran (Cost)
            $row = [
                $pengajuan->urgensi,           // K1
                $pengajuan->ketersediaan_stok, // K2
                $persentaseBiaya               // K3
            ];
            $matriksKeputusan[] = $row;
        }

        // === LANGKAH 2: NORMALISASI MATRIKS (R) ===
        $matriksNormalisasi = [];
        $jumlahKuadrat = [0, 0, 0]; // Untuk menyimpan akar jumlah kuadrat tiap kolom

        // Hitung akar jumlah kuadrat untuk setiap kriteria (kolom)
        for ($j = 0; $j < 3; $j++) {
            for ($i = 0; $i < count($pengajuans); $i++) {
                $jumlahKuadrat[$j] += pow($matriksKeputusan[$i][$j], 2);
            }
            $jumlahKuadrat[$j] = sqrt($jumlahKuadrat[$j]);
        }

        // Lakukan normalisasi: x_ij / sqrt(sum(x_kj^2))
        for ($i = 0; $i < count($pengajuans); $i++) {
            $row = [];
            for ($j = 0; $j < 3; $j++) {
                $row[] = ($jumlahKuadrat[$j] > 0) ? $matriksKeputusan[$i][$j] / $jumlahKuadrat[$j] : 0;
            }
            $matriksNormalisasi[] = $row;
        }

        // === LANGKAH 3: MATRIKS NORMALISASI DENGAN BOBOT (Y) ===
        $matriksTerbobot = [];
        for ($i = 0; $i < count($pengajuans); $i++) {
            $row = [];
            for ($j = 0; $j < 3; $j++) {
                // Kalikan nilai ternormalisasi dengan bobot kriteria
                $row[] = $matriksNormalisasi[$i][$j] * $kriterias[$j]->bobot;
            }
            $matriksTerbobot[] = $row;
        }

        // === LANGKAH 4: TENTUKAN SOLUSI IDEAL POSITIF & NEGATIF ===
        $solusiIdealPositif = [];
        $solusiIdealNegatif = [];

        for ($j = 0; $j < 3; $j++) {
            $kolom = array_column($matriksTerbobot, $j);

            // Untuk kriteria 'benefit': solusi ideal positif = nilai maksimum
            // Untuk kriteria 'cost': solusi ideal positif = nilai minimum
            if ($kriterias[$j]->tipe == 'benefit') {
                $solusiIdealPositif[] = max($kolom);
                $solusiIdealNegatif[] = min($kolom);
            } else { // tipe = 'cost'
                $solusiIdealPositif[] = min($kolom);
                $solusiIdealNegatif[] = max($kolom);
            }
        }

        // === LANGKAH 5: HITUNG JARAK KE SOLUSI IDEAL ===
        $jarakPositif = [];   // Jarak ke solusi ideal positif (A+)
        $jarakNegatif = [];   // Jarak ke solusi ideal negatif (A-)

        for ($i = 0; $i < count($pengajuans); $i++) {
            $dPlus = 0;
            $dMinus = 0;

            for ($j = 0; $j < 3; $j++) {
                // Hitung jarak Euclidean
                $dPlus += pow($matriksTerbobot[$i][$j] - $solusiIdealPositif[$j], 2);
                $dMinus += pow($matriksTerbobot[$i][$j] - $solusiIdealNegatif[$j], 2);
            }

            $jarakPositif[] = sqrt($dPlus);
            $jarakNegatif[] = sqrt($dMinus);
        }

        // === LANGKAH 6: HITUNG NILAI PREFERENSI (V) ===
        $preferensi = [];
        for ($i = 0; $i < count($pengajuans); $i++) {
            // Hindari pembagian dengan nol dengan menambahkan epsilon kecil
            $epsilon = 0.000001;
            $totalJarak = $jarakPositif[$i] + $jarakNegatif[$i] + $epsilon;
            $nilaiV = $jarakNegatif[$i] / $totalJarak; // Semakin besar V, semakin baik
            $preferensi[] = $nilaiV;
        }

        // === LANGKAH 7: SUSUN HASIL DAN RANKING ===
        $hasil = [];
        for ($i = 0; $i < count($pengajuans); $i++) {
            $hasil[] = [
                'pengajuan' => $pengajuans[$i],
                'nilai_preferensi' => $preferensi[$i],
                'd_plus' => $jarakPositif[$i],
                'd_minus' => $jarakNegatif[$i]
            ];
        }

        // Urutkan dari nilai preferensi tertinggi ke terendah (ranking terbaik di atas)
        usort($hasil, function ($a, $b) {
            return $b['nilai_preferensi'] <=> $a['nilai_preferensi'];
        });

        // === SIMPAN HASIL KE DATABASE ===
        foreach ($hasil as $index => $item) {
            AnalisisTopsis::updateOrCreate(
                ['pengajuan_id' => $item['pengajuan']->id],
                [
                    'nilai_preferensi' => $item['nilai_preferensi'],
                    'ranking' => $index + 1 // Ranking dimulai dari 1
                ]
            );
        }

        // Kembalikan semua data untuk ditampilkan di view
        return [
            'hasil' => $hasil,
            'matriksKeputusan' => $matriksKeputusan,
            'matriksNormalisasi' => $matriksNormalisasi,
            'matriksTerbobot' => $matriksTerbobot,
            'solusiIdealPositif' => $solusiIdealPositif,
            'solusiIdealNegatif' => $solusiIdealNegatif,
            'jarakPositif' => $jarakPositif,
            'jarakNegatif' => $jarakNegatif,
        ];
    }
}