<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Tambahkan ini

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        try {
            $notifications = Notifikasi::forRole('admin')
                ->latest()
                ->paginate(20);

            Notifikasi::forRole('admin')->unread()->update(['is_read' => true]);

            return view('admin.notifikasi.index', compact('notifications'));
        } catch (\Exception $e) {
            Log::error('Admin Notification Index Error: ' . $e->getMessage());
            // Kembalikan view error atau redirect dengan pesan
            return back()->with('error', 'Gagal memuat notifikasi.');
        }
    }

    public function latest()
    {
        try {
            $notifications = Notifikasi::forRole('admin')
                ->unread()
                ->latest()
                ->take(5)
                ->get();

            return response()->json(['notifikasis' => $notifications]);
        } catch (\Exception $e) {
            Log::error('Admin Notification Latest Error: ' . $e->getMessage());
            // Kembalikan response error JSON
            return response()->json(['error' => 'Gagal mengambil notifikasi terbaru.'], 500);
        }
    }

    public function unreadCount()
    {
        try {
            $count = Notifikasi::forRole('admin')->unread()->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Admin Notification Unread Count Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghitung notifikasi.'], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            Notifikasi::forRole('admin')->unread()->update(['is_read' => true]);
            return response()->json(['success' => true, 'message' => 'Semua notifikasi ditandai sebagai dibaca.']);
        } catch (\Exception $e) {
            Log::error('Admin Notification Mark All Read Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menandai notifikasi.'], 500);
        }
    }

    public function show(Notifikasi $notifikasi)
    {
        try {
            if ($notifikasi->role !== 'admin') {
                abort(403);
            }

            $notifikasi->markAsRead();
            return redirect($notifikasi->url ?: route('admin.dashboard'));
        } catch (\Exception $e) {
            Log::error('Admin Notification Show Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuka notifikasi.');
        }
    }
}
