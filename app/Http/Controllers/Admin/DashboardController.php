<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\User;
use App\Models\Peminjaman;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\Audit;
use App\Models\JadwalAudit;

class DashboardController extends Controller
{
    public function adminDashboard()
    {
        // Hitung total barang aktif
        $totalBarang = Barang::where('status', 'Aktif')->count();

        // Hitung stok kritis (stok < 5)
        $stokKritis = Barang::where('stok', '<', 5)->where('status', 'Aktif')->count();

        // Hitung barang rusak
        $barangRusak = Audit::where('kondisi', 'rusak')
            ->distinct('barang_id')
            ->count('barang_id');

        // Hitung barang hilang
        $barangHilang = Audit::where('kondisi', 'hilang')
            ->distinct('barang_id')
            ->count('barang_id');

        // Total barang rusak + hilang
        $totalRusakHilang = $barangRusak + $barangHilang;

        // Hitung barang dalam perawatan
        $barangPerawatan = Barang::where('status', 'Perawatan')->count();

        // Hitung total user aktif
        $totalUser = User::where('is_active', true)->count();

        // Data untuk grafik 6 bulan terakhir
        $bulanIndo = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $enamBulanTerakhir = [];
        $barangMasukValues = [];
        $barangKeluarValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = date('n', strtotime("-$i months"));
            $tahun = date('Y', strtotime("-$i months"));
            $enamBulanTerakhir[] = $bulanIndo[$bulan - 1];

            $masuk = BarangMasuk::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->sum('jumlah');
            $barangMasukValues[] = $masuk;

            $keluar = BarangKeluar::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->sum('jumlah');
            $barangKeluarValues[] = $keluar;
        }

        // Hitung peminjaman aktif
        $peminjamanAktif = Peminjaman::where('status', 'Dipinjam')->count();

        // Ambil 5 peminjaman terbaru
        $listPeminjaman = Peminjaman::where('status', 'Dipinjam')
            ->with('barang')
            ->orderBy('tanggal_pinjam', 'desc')
            ->take(5)
            ->get();

        // Ambil 5 barang dengan stok kritis
        $barangStokKritis = Barang::where('stok', '<', 5)
            ->where('status', 'Aktif')
            ->with('kategori')
            ->take(5)
            ->get();

        // Ambil 5 jadwal audit terbaru
        $jadwalAuditTerbaru = JadwalAudit::with(['barang', 'user'])
            ->orderBy('tanggal_audit', 'desc')
            ->take(5)
            ->get();

        // Kirim semua variabel ke view
        return view('admin.dashboard.main', compact(
            'totalBarang',
            'stokKritis',
            'totalRusakHilang',
            'totalUser',
            'barangPerawatan',
            'enamBulanTerakhir',
            'barangMasukValues',
            'barangKeluarValues',
            'peminjamanAktif',
            'listPeminjaman',
            'barangStokKritis',
            'jadwalAuditTerbaru',
        ));
    }
}
