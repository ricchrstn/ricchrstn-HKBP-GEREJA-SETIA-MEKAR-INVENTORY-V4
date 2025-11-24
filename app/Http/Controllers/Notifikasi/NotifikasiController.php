<?php

namespace App\Http\Controllers\Notifikasi;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Barang;
use App\Models\User;
use App\Models\Audit;
use App\Models\Peminjaman;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\JadwalAudit;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $notifikasis = Notifikasi::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        // Variabel untuk navbar notifikasi
        $notifikasiTerbaru = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        $jumlahNotifikasiBelumDibaca = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        // Variabel untuk dashboard (jika menggunakan layout dashboard)
        $totalBarang = Barang::where('status', 'Aktif')->count();
        $stokKritis = Barang::where('stok', '<', 5)->where('status', 'Aktif')->count();

        $barangRusak = Audit::where('kondisi', 'rusak')
            ->distinct('barang_id')
            ->count('barang_id');
        $barangHilang = Audit::where('kondisi', 'hilang')
            ->distinct('barang_id')
            ->count('barang_id');
        $totalRusakHilang = $barangRusak + $barangHilang;

        $barangPerawatan = Barang::where('status', 'Perawatan')->count();
        $totalUser = User::where('is_active', true)->count();
        $peminjamanAktif = Peminjaman::where('status', 'Dipinjam')->count();

        // Data untuk grafik (kosong untuk halaman notifikasi)
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

        $listPeminjaman = Peminjaman::where('status', 'Dipinjam')
            ->with('barang')
            ->orderBy('tanggal_pinjam', 'desc')
            ->take(5)
            ->get();

        $barangStokKritis = Barang::where('stok', '<', 5)
            ->where('status', 'Aktif')
            ->with('kategori')
            ->take(5)
            ->get();

        $jadwalAuditTerbaru = JadwalAudit::with(['barang', 'user'])
            ->orderBy('tanggal_audit', 'desc')
            ->take(5)
            ->get();

        return view('notifikasi.index', compact(
            'notifikasis',
            'notifikasiTerbaru',
            'jumlahNotifikasiBelumDibaca',
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
            'jadwalAuditTerbaru'
        ));
    }

    /**
     * Display the specified notification.
     */
    public function show($id)
    {
        $notifikasi = Notifikasi::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Mark as read when viewed
        if (!$notifikasi->is_read) {
            $notifikasi->is_read = true;
            $notifikasi->save();
        }

        return redirect()->away($notifikasi->url ?? route('notifikasi.index'));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($id)
    {
        $notifikasi = Notifikasi::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notifikasi->is_read = true;
        $notifikasi->save();

        return back()->with('success', 'Notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        $count = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Get latest notifications.
     */
    public function latest()
    {
        $notifikasis = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'notifikasis' => $notifikasis
        ]);
    }
}
