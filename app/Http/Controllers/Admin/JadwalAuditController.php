<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalAudit;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Http\Request;

class JadwalAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalAudit::with(['barang', 'user']);
        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%')
                ->orWhereHas('barang', function ($q) use ($request) {
                    $q->where('nama', 'like', '%' . $request->search . '%');
                });
        }
        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Filter berdasarkan tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_audit', $request->tanggal);
        }
        $jadwalAudits = $query->latest()->paginate(10);
        return view('admin.jadwal-audit.index', compact('jadwalAudits'));
    }

    public function create()
    {
        // Ambil semua kategori untuk dropdown pertama
        $kategoris = Kategori::orderBy('nama', 'asc')->get();
        // $barangs tidak lagi diambil di sini, akan diambil via AJAX
        $users = User::all();
        // Kirim $kategoris ke view
        return view('admin.jadwal-audit.create', compact('kategoris', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_audit' => 'required|date',
            'barang_id' => 'required|exists:barang,id',
            'user_id' => 'required|exists:users,id',
        ]);
        JadwalAudit::create($request->all());
        return redirect()->route('admin.jadwal-audit.index')
            ->with('success', 'Jadwal audit berhasil ditambahkan');
    }

    public function edit(JadwalAudit $jadwalAudit)
    {
        // Ambil semua kategori dan barang untuk form edit
        $kategoris = Kategori::orderBy('nama', 'asc')->get();
        $barangs = Barang::all();
        $users = User::all();
        return view('admin.jadwal-audit.edit', compact('jadwalAudit', 'kategoris', 'barangs', 'users'));
    }

    public function update(Request $request, JadwalAudit $jadwalAudit)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_audit' => 'required|date',
            'barang_id' => 'required|exists:barang,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:terjadwal,diproses,selesai,ditunda',
        ]);
        $jadwalAudit->update($request->all());
        return redirect()->route('admin.jadwal-audit.index')
            ->with('success', 'Jadwal audit berhasil diperbarui');
    }

    public function destroy(JadwalAudit $jadwalAudit)
    {
        $jadwalAudit->delete();
        return redirect()->route('admin.jadwal-audit.index')
            ->with('success', 'Jadwal audit berhasil dihapus');
    }

    /**
     * Method untuk mengambil barang berdasarkan kategori via AJAX
     */
    public function getBarangByKategori($kategoriId)
    {
        try {
            $barangs = Barang::where('kategori_id', $kategoriId)
                ->where('status', 'aktif')
                ->orderBy('nama', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $barangs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
