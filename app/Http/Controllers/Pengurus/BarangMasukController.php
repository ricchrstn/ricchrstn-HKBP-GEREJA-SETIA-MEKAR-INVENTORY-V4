<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
// use App\Helpers\NotificationHelper;

class BarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangMasuk::with(['barang.kategori', 'user'])
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

        $barangMasuks = $query->latest()->paginate(15)->withQueryString();

        return view('pengurus.barang.masuk.index', compact('barangMasuks'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama', 'asc')->get();
        return view('pengurus.barang.masuk.create', compact('kategoris'));
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
            'jumlah.min'        => 'Jumlah harus minimal 1'
        ]);

        try {
            DB::beginTransaction();

            $validated['user_id'] = auth()->id();

            if (is_string($validated['tanggal'])) {
                $validated['tanggal'] = Carbon::parse($validated['tanggal'])->format('Y-m-d');
            }

            $barangMasuk = BarangMasuk::create($validated);

            $barang = Barang::find($validated['barang_id']);
            $barang->stok += $validated['jumlah'];
            $barang->save();

            DB::commit();

            // NotificationHelper::create('admin',
            //     'Barang Masuk Baru',
            //     'Barang masuk baru telah dicatat: ' . $barang->nama . ' (' . $validated['jumlah'] . ' ' . $barang->satuan . ') oleh ' . auth()->user()->name,
            //     'barang_masuk',
            //     'fa-arrow-down',
            //     'success'
            // );

            // NotificationHelper::create('bendahara',
            //     'Barang Masuk Baru',
            //     'Barang masuk baru telah dicatat: ' . $barang->nama . ' (' . $validated['jumlah'] . ' ' . $barang->satuan . ') oleh ' . auth()->user()->name,
            //     'barang_masuk',
            //     'fa-arrow-down',
            //     'success'
            // );

            return redirect()->route('pengurus.barang.masuk')
                ->with('success', 'Barang masuk berhasil dicatat');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating barang masuk: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(BarangMasuk $barangMasuk)
    {
        $barangMasuk->load(['barang.kategori', 'user']);
        return view('pengurus.barang.masuk.show', compact('barangMasuk'));
    }

    public function edit(BarangMasuk $barangMasuk)
    {
        $barangMasuk->load(['barang.kategori']);
        return view('pengurus.barang.masuk.edit', compact('barangMasuk'));
    }

public function update(Request $request, BarangMasuk $barangMasuk)
{
    $validated = $request->validate([
        'tanggal'    => 'required|date',
        'jumlah'     => 'required|integer|min:1',
        'keterangan' => 'nullable|string|max:255'
    ]);

    try {
        DB::beginTransaction();

        $selisih = $validated['jumlah'] - $barangMasuk->jumlah;
        if (is_string($validated['tanggal'])) {
            $validated['tanggal'] = Carbon::parse($validated['tanggal'])->format('Y-m-d');
        }

        $barangMasuk->update($validated);

        $barang = Barang::find($barangMasuk->barang_id);
        // if ($barang) {
        //     $barang->stok += $selisih;
        //     $barang->save();

        //     // 💡 Kirim notifikasi HANYA jika barang ditemukan
        //     $notifMessage = 'Data barang masuk untuk ' . $barang->nama . ' (' . $validated['jumlah'] . ' ' . $barang->satuan . ') telah diperbarui oleh ' . auth()->user()->name;

        //     NotificationHelper::create('admin', 'Barang Masuk Diperbarui', $notifMessage, 'barang_masuk', 'fa-edit', 'warning');
        //     NotificationHelper::create('bendahara', 'Barang Masuk Diperbarui', $notifMessage, 'barang_masuk', 'fa-edit', 'warning');
        // } else {
        //     // Opsional: log peringatan
        //     Log::warning('Barang tidak ditemukan saat update barang masuk ID: ' . $barangMasuk->id);
        // }

        DB::commit();

        return redirect()->route('pengurus.barang.masuk')
            ->with('success', 'Data barang masuk berhasil diperbarui');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating barang masuk: ' . $e->getMessage());
        return back()->withInput()
            ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
    }
}
    public function destroy(BarangMasuk $barangMasuk)
    {
        try {
            DB::beginTransaction();

            $barang = Barang::find($barangMasuk->barang_id);

            if (!$barang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang tidak ditemukan'
                ], 404);
            }

            if ($barang->stok < $barangMasuk->jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus karena stok barang tidak mencukupi'
                ], 400);
            }

            $barang->stok -= $barangMasuk->jumlah;
            $barang->save();

            $barangMasuk->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data barang masuk berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting barang masuk: ' . $e->getMessage());
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
