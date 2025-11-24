<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PengajuanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengajuan::with('user')->where('user_id', auth()->id());

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $query->where('nama_barang', 'like', '%' . $request->search . '%')
                ->orWhere('kode_pengajuan', 'like', '%' . $request->search . '%')
                ->orWhere('alasan', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $pengajuans = $query->latest()->paginate(10);

        return view('pengurus.pengajuan.index', compact('pengajuans'));
    }

    public function create()
    {
        return view('pengurus.pengajuan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'spesifikasi' => 'nullable|string',
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'alasan' => 'required|string',
            'kebutuhan' => 'required|date|after_or_equal:today',
            'file_pengajuan' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'urgensi' => 'required|integer|min:1|max:5',
            'ketersediaan_stok' => 'required|integer|min:1|max:5',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();
        $data['kode_pengajuan'] = Pengajuan::generateKode();
        $data['status'] = 'pending';

        if ($request->hasFile('file_pengajuan')) {
            $path = $request->file('file_pengajuan')->store('pengajuan_files', 'public');
            $data['file_pengajuan'] = $path;
        }

        Pengajuan::create($data);

        return redirect()->route('pengurus.pengajuan.index')
            ->with('success', 'Pengajuan pengadaan berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pengajuan $pengajuan)
    {
        if ($pengajuan->user_id !== auth()->id() || $pengajuan->status !== 'pending') {
            abort(403);
        }

        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'spesifikasi' => 'nullable|string',
            'jumlah' => 'required|integer|min:1',
            'harga_satuan' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'alasan' => 'required|string',
            'kebutuhan' => 'required|date|after_or_equal:today',
            'file_pengajuan' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'urgensi' => 'required|integer|min:1|max:5',
            'ketersediaan_stok' => 'required|integer|min:1|max:5',
        ]);

        try {
            $data = $request->except('file_pengajuan');

            if ($request->hasFile('file_pengajuan')) {
                if ($pengajuan->file_pengajuan) {
                    Storage::disk('public')->delete($pengajuan->file_pengajuan);
                }
                $path = $request->file('file_pengajuan')->store('pengajuan_files', 'public');
                $data['file_pengajuan'] = $path;
            }

            $pengajuan->update($data);

            return redirect()->route('pengurus.pengajuan.index')
                ->with('success', 'Pengajuan pengadaan berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating pengajuan: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui pengajuan.');
        }
    }

    public function show(Pengajuan $pengajuan)
    {
        // Pastikan hanya pemilik pengajuan yang bisa melihat
        if ($pengajuan->user_id !== auth()->id()) {
            abort(403);
        }

        return view('pengurus.pengajuan.show', compact('pengajuan'));
    }

    public function edit(Pengajuan $pengajuan)
    {
        // Pastikan hanya pemilik pengajuan yang bisa mengedit dan status masih pending
        if ($pengajuan->user_id !== auth()->id() || $pengajuan->status !== 'pending') {
            abort(403);
        }

        return view('pengurus.pengajuan.edit', compact('pengajuan'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pengajuan $pengajuan)
    {
        // Pastikan hanya pemilik yang bisa menghapus dan status masih pending
        if ($pengajuan->user_id !== auth()->id() || $pengajuan->status !== 'pending') {
            // Jika request AJAX, kembalikan error JSON
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Aksi tidak diizinkan.'], 403);
            }
            abort(403);
        }

        try {
            // Hapus file jika ada
            if ($pengajuan->file_pengajuan) {
                Storage::disk('public')->delete($pengajuan->file_pengajuan);
            }

            $pengajuan->delete();

            // Respon untuk request AJAX
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengajuan pengadaan berhasil dihapus'
                ]);
            }

            // Respon untuk request form biasa
            return redirect()->route('pengurus.pengajuan.index')
                ->with('success', 'Pengajuan pengadaan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting pengajuan: ' . $e->getMessage());

            // Respon error untuk request AJAX
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus pengajuan.'
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus pengajuan.');
        }
    }
}
