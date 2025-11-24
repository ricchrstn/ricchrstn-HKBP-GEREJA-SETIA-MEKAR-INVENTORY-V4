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
    public function index()
    {
        $kriterias = Kriteria::all();
        $pengajuans = Pengajuan::where('status', 'pending')
            ->whereNotNull('urgensi')
            ->whereNotNull('ketersediaan_stok')
            ->whereNotNull('harga_satuan')
            ->get();

        $saldoKas = Kas::getSaldo();

        return view('bendahara.analisis.index', compact('kriterias', 'pengajuans', 'saldoKas'));
    }

    /**
     * Tampilkan hasil analisis TOPSIS
     */
    public function hasil()
    {
        // Ambil semua pengajuan dengan status pending yang sudah memiliki nilai kriteria
        $pengajuans = Pengajuan::where('status', 'pending')
            ->whereNotNull('urgensi')
            ->whereNotNull('ketersediaan_stok')
            ->whereNotNull('harga_satuan')
            ->get();

        // Ambil semua kriteria (seharusnya hanya 3)
        $kriterias = Kriteria::all();

        if ($pengajuans->isEmpty() || $kriterias->count() !== 3) {
            return redirect()->route('bendahara.analisis.index')
                ->with('error', 'Tidak cukup data untuk dianalisis. Pastikan ada 3 kriteria dan pengajuan lengkap.');
        }

        $saldoKas = Kas::getSaldo();

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
            'pengajuans' => $pengajuans,
            'saldoKas' => $saldoKas
        ]);
    }

    private function hitungTopsis($pengajuans, $kriterias)
    {
        // Dapatkan total anggaran (saldo kas)
        $anggaran = Kas::getSaldo();
        if ($anggaran <= 0) {
            return redirect()->route('bendahara.analisis.index')
                ->with('error', 'Saldo kas tidak valid. Tidak dapat melakukan analisis.');
        }

        // Langkah 1: Matriks Keputusan (X)
        $matriksKeputusan = [];
        foreach ($pengajuans as $pengajuan) {
            $totalHarga = $pengajuan->harga_satuan * $pengajuan->jumlah;
            $persentaseBiaya = ($totalHarga / $anggaran) * 100;

            $row = [
                $pengajuan->urgensi,           // K1 - Urgensi (Benefit)
                $pengajuan->ketersediaan_stok, // K2 - Ketersediaan Stok (Cost)
                $persentaseBiaya               // K3 - Persentase Biaya (Cost)
            ];
            $matriksKeputusan[] = $row;
        }

        // Langkah 2: Normalisasi Matriks (R)
        $matriksNormalisasi = [];
        $jumlahKuadrat = [0, 0, 0];

        // Hitung jumlah kuadrat setiap kriteria
        for ($j = 0; $j < 3; $j++) {
            for ($i = 0; $i < count($pengajuans); $i++) {
                $jumlahKuadrat[$j] += pow($matriksKeputusan[$i][$j], 2);
            }
            $jumlahKuadrat[$j] = sqrt($jumlahKuadrat[$j]);
        }

        // Normalisasi
        for ($i = 0; $i < count($pengajuans); $i++) {
            $row = [];
            for ($j = 0; $j < 3; $j++) {
                // Hindari pembagian dengan nol
                $row[] = ($jumlahKuadrat[$j] > 0) ? $matriksKeputusan[$i][$j] / $jumlahKuadrat[$j] : 0;
            }
            $matriksNormalisasi[] = $row;
        }

        // Langkah 3: Matriks Normalisasi Terbobot (Y)
        $matriksTerbobot = [];
        for ($i = 0; $i < count($pengajuans); $i++) {
            $row = [];
            for ($j = 0; $j < 3; $j++) {
                $row[] = $matriksNormalisasi[$i][$j] * $kriterias[$j]->bobot;
            }
            $matriksTerbobot[] = $row;
        }

        // Langkah 4: Solusi Ideal
        $solusiIdealPositif = [];
        $solusiIdealNegatif = [];

        for ($j = 0; $j < 3; $j++) {
            $kolom = array_column($matriksTerbobot, $j);

            if ($kriterias[$j]->tipe == 'benefit') {
                $solusiIdealPositif[] = max($kolom);
                $solusiIdealNegatif[] = min($kolom);
            } else { // cost
                $solusiIdealPositif[] = min($kolom);
                $solusiIdealNegatif[] = max($kolom);
            }
        }

        // Langkah 5: Menghitung Jarak
        $jarakPositif = [];
        $jarakNegatif = [];

        for ($i = 0; $i < count($pengajuans); $i++) {
            $dPlus = 0;
            $dMinus = 0;

            for ($j = 0; $j < 3; $j++) {
                $dPlus += pow($matriksTerbobot[$i][$j] - $solusiIdealPositif[$j], 2);
                $dMinus += pow($matriksTerbobot[$i][$j] - $solusiIdealNegatif[$j], 2);
            }

            $jarakPositif[] = sqrt($dPlus);
            $jarakNegatif[] = sqrt($dMinus);
        }

        // Langkah 6: Nilai Preferensi
        $preferensi = [];
        for ($i = 0; $i < count($pengajuans); $i++) {
            $epsilon = 0.000001; // Tambahkan epsilon kecil untuk menghindari pembagian dengan nol
            $totalJarak = $jarakPositif[$i] + $jarakNegatif[$i] + $epsilon;
            $nilaiV = $jarakNegatif[$i] / $totalJarak;
            $preferensi[] = $nilaiV;
        }

        // Langkah 7: Perankingan
        $hasil = [];
        for ($i = 0; $i < count($pengajuans); $i++) {
            $hasil[] = [
                'pengajuan' => $pengajuans[$i],
                'nilai_preferensi' => $preferensi[$i],
                'd_plus' => $jarakPositif[$i],
                'd_minus' => $jarakNegatif[$i]
            ];
        }

        // Urutkan berdasarkan nilai preferensi (descending)
        usort($hasil, function ($a, $b) {
            return $b['nilai_preferensi'] <=> $a['nilai_preferensi'];
        });

        // Simpan hasil ke database
        foreach ($hasil as $index => $item) {
            AnalisisTopsis::updateOrCreate(
                ['pengajuan_id' => $item['pengajuan']->id],
                [
                    'nilai_preferensi' => $item['nilai_preferensi'],
                    'ranking' => $index + 1
                ]
            );
        }

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
