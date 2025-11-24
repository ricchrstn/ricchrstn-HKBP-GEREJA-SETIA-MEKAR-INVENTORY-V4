<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
// use App\Helpers\NotificationHelper;

class BarangKeluarController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangKeluar::with(['barang.kategori', 'user'])
            ->whereHas('barang');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('barang', function ($query) use ($search) {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_barang', 'like', "%{$search}%");
                })
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        $barangKeluars = $query->latest()->paginate(15)->withQueryString();

        return view('pengurus.barang.keluar.index', compact('barangKeluars'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama', 'asc')->get();
        $barangs = collect([]);
        return view('pengurus.barang.keluar.create', compact('kategoris', 'barangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'barang_id'  => 'required|exists:barang,id',
            'tanggal'    => 'required|date',
            'jumlah'     => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255'
        ], [
            'kategori_id.required' => 'Kategori harus dipilih',
            'barang_id.required' => 'Barang harus dipilih',
            'jumlah.min'        => 'Jumlah harus minimal 1',
        ]);

        try {
            DB::beginTransaction();

            $barang = Barang::find($validated['barang_id']);
            if ($barang->stok < $validated['jumlah']) {
                return back()->withInput()
                    ->with('error', 'Stok barang tidak mencukupi. Stok tersedia: ' . $barang->stok . ' ' . $barang->satuan);
            }

            $validated['user_id'] = auth()->id();

            if (is_string($validated['tanggal'])) {
                $validated['tanggal'] = Carbon::parse($validated['tanggal'])->format('Y-m-d');
            }

            $validated['keterangan'] = $validated['keterangan'] ?? '';

            $barangKeluar = BarangKeluar::create($validated);

            $barang->stok -= $validated['jumlah'];
            $barang->save();

            DB::commit();

            return redirect()->route('pengurus.barang.keluar')
                ->with('success', 'Barang keluar berhasil dicatat');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating barang keluar: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(BarangKeluar $barangKeluar)
    {
        $barangKeluar->load(['barang.kategori', 'user']);
        return view('pengurus.barang.keluar.show', compact('barangKeluar'));
    }

    public function edit(BarangKeluar $barangKeluar)
    {
        // Load barang dan kategori untuk ditampilkan sebagai readonly
        $barangKeluar->load(['barang.kategori']);

        return view('pengurus.barang.keluar.edit', compact('barangKeluar'));
    }

    public function update(Request $request, BarangKeluar $barangKeluar)
    {
        // Hanya validasi field yang bisa diubah
        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'jumlah'     => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $selisih = $validated['jumlah'] - $barangKeluar->jumlah;

            if (is_string($validated['tanggal'])) {
                $validated['tanggal'] = Carbon::parse($validated['tanggal'])->format('Y-m-d');
            }

            $validated['keterangan'] = $validated['keterangan'] ?? '';

            // Ambil barang yang terkait dengan transaksi ini
            $barang = Barang::find($barangKeluar->barang_id);

            if ($selisih > 0) {
                // Jika jumlah bertambah, periksa stok
                if ($barang->stok < $selisih) {
                    return back()->withInput()
                        ->with('error', 'Stok barang tidak mencukupi. Stok tersedia: ' . $barang->stok . ' ' . $barang->satuan);
                }
            }

            // Update data barang keluar
            $barangKeluar->update($validated);

            // Update stok barang
            $barang->stok -= $selisih;
            $barang->save();

            DB::commit();

            return redirect()->route('pengurus.barang.keluar')
                ->with('success', 'Data barang keluar berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating barang keluar: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(BarangKeluar $barangKeluar)
    {
        try {
            DB::beginTransaction();

            $barang = Barang::find($barangKeluar->barang_id);
            $barang->stok += $barangKeluar->jumlah;
            $barang->save();

            $barangKeluar->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data barang keluar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting barang keluar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBarangDetails($id)
    {
        try {
            $barang = Barang::with('kategori')->find($id);

            if (!$barang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $barang->id,
                    'nama' => $barang->nama,
                    'kode_barang' => $barang->kode_barang,
                    'stok' => $barang->stok,
                    'satuan' => $barang->satuan,
                    'harga' => $barang->harga,
                    'kategori' => $barang->kategori->nama ?? '-'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting barang details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBarangByKategori($kategoriId)
    {
        try {
            $barangs = Barang::with('kategori')
                ->where('kategori_id', $kategoriId)
                ->where('status', 'aktif')
                ->where('stok', '>', 0)
                ->orderBy('nama', 'asc')
                ->get()
                ->map(function ($barang) {
                    return [
                        'id' => $barang->id,
                        'nama' => $barang->nama,
                        'kode_barang' => $barang->kode_barang,
                        'stok' => $barang->stok,
                        'satuan' => $barang->satuan,
                        'harga' => $barang->harga,
                        'gambar' => $barang->gambar,
                        'kategori_nama' => $barang->kategori->nama ?? '-'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $barangs
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting barang by kategori: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
