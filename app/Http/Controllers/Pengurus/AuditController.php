<?php
namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Barang;
use App\Models\JadwalAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// use App\Helpers\NotificationHelper;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $queryMandiri = Audit::with(['barang', 'user'])->where('user_id', auth()->id());

        if ($request->filled('search')) {
            $queryMandiri->where('keterangan', 'like', '%' . $request->search . '%')
                ->orWhereHas('barang', function ($q) use ($request) {
                    $q->where('nama', 'like', '%' . $request->search . '%');
                });
        }

        if ($request->filled('kondisi')) {
            $queryMandiri->where('kondisi', $request->kondisi);
        }

        if ($request->filled('tanggal')) {
            $queryMandiri->whereDate('tanggal_audit', $request->tanggal);
        }

        $auditsMandiri = $queryMandiri->latest()->paginate(10, ['*'], 'mandiri_page');

        $queryJadwal = JadwalAudit::with(['barang', 'user'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['terjadwal', 'diproses']);

        if ($request->filled('search')) {
            $queryJadwal->where('judul', 'like', '%' . $request->search . '%')
                ->orWhereHas('barang', function ($q) use ($request) {
                    $q->where('nama', 'like', '%' . $request->search . '%');
                });
        }

        if ($request->filled('tanggal')) {
            $queryJadwal->whereDate('tanggal_audit', $request->tanggal);
        }

        $jadwalAudits = $queryJadwal->latest()->paginate(10, ['*'], 'jadwal_page');

        return view('pengurus.audit.index', compact('auditsMandiri', 'jadwalAudits'));
    }

    public function create()
    {
        $barangs = Barang::all();
        $categories = \App\Models\Kategori::all();
        return view('pengurus.audit.create', compact('barangs', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'tanggal_audit' => 'required|date',
            'kondisi' => 'required|in:baik,rusak,hilang,tidak_terpakai',
            'keterangan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $data = $request->except('foto');
        $data['user_id'] = auth()->id();
        $data['status'] = 'selesai';
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('audit_foto', 'public');
            $data['foto'] = $path;
        }
        Audit::create($data);

        $barang = Barang::find($request->barang_id);
        // NotificationHelper::create('admin',
        //     'Audit Barang Baru',
        //     'Audit barang baru telah dibuat: ' . $barang->nama . ' dengan kondisi ' . $request->kondisi . ' oleh ' . auth()->user()->name,
        //     'audit',
        //     'fa-search',
        //     'info'
        // );

        return redirect()->route('pengurus.audit.index')
            ->with('success', 'Audit barang berhasil ditambahkan');
    }

    public function selesaikanJadwal(Request $request, JadwalAudit $jadwalAudit)
    {
        if ($jadwalAudit->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menyelesaikan jadwal audit ini'
            ], 403);
        }

        $validated = $request->validate([
            'kondisi' => 'required|in:baik,rusak,hilang,tidak_terpakai',
            'keterangan' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $jadwalAudit->status = 'selesai';
        $jadwalAudit->save();

        $data = [
            'barang_id' => $jadwalAudit->barang_id,
            'user_id' => auth()->id(),
            'tanggal_audit' => $jadwalAudit->tanggal_audit,
            'kondisi' => $validated['kondisi'],
            'keterangan' => $validated['keterangan'],
            'status' => 'selesai',
        ];

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('audit_foto', 'public');
            $data['foto'] = $path;
        }

        Audit::create($data);

        // NotificationHelper::create('admin',
        //     'Jadwal Audit Selesai',
        //     'Jadwal audit untuk ' . $jadwalAudit->barang->nama . ' telah selesai oleh ' . auth()->user()->name,
        //     'audit',
        //     'fa-check-circle',
        //     'success'
        // );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal audit berhasil diselesaikan'
        ]);
    }

    public function show(Audit $audit)
    {
        if ($audit->user_id !== auth()->id()) {
            abort(403);
        }
        return view('pengurus.audit.show', compact('audit'));
    }

    public function showJadwal(JadwalAudit $jadwalAudit)
    {
        if ($jadwalAudit->user_id !== auth()->id()) {
            abort(403);
        }

        return view('pengurus.audit.show-jadwal', compact('jadwalAudit'));
    }

    public function edit(Audit $audit)
    {
        if ($audit->user_id !== auth()->id()) {
            abort(403);
        }
        $barangs = Barang::all();
        $categories = \App\Models\Kategori::all();
        return view('pengurus.audit.edit', compact('audit', 'barangs', 'categories'));
    }

    public function update(Request $request, Audit $audit)
    {
        if ($audit->user_id !== auth()->id()) {
            abort(403);
        }
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'tanggal_audit' => 'required|date',
            'kondisi' => 'required|in:baik,rusak,hilang,tidak_terpakai',
            'keterangan' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $data = $request->except('foto');
        if ($request->hasFile('foto')) {
            if ($audit->foto) {
                Storage::disk('public')->delete($audit->foto);
            }
            $path = $request->file('foto')->store('audit_foto', 'public');
            $data['foto'] = $path;
        }
        $audit->update($data);

        $barang = Barang::find($request->barang_id);
        // NotificationHelper::create('admin',
        //     'Audit Barang Diperbarui',
        //     'Audit barang untuk ' . $barang->nama . ' telah diperbarui oleh ' . auth()->user()->name,
        //     'audit',
        //     'fa-edit',
        //     'warning'
        // );

        return redirect()->route('pengurus.audit.index')
            ->with('success', 'Audit barang berhasil diperbarui');
    }

    public function destroy(Audit $audit)
    {
        if ($audit->user_id !== auth()->id()) {
            abort(403);
        }
        if ($audit->foto) {
            Storage::disk('public')->delete($audit->foto);
        }
        $audit->delete();
        return redirect()->route('pengurus.audit.index')
            ->with('success', 'Audit barang berhasil dihapus');
    }
}
