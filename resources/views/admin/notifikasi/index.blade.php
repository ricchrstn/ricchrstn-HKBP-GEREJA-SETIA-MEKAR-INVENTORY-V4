@extends('admin.dashboard.layouts.app')

@section('title', 'Notifikasi - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Notifikasi</h5>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notif)
                                @php
                                    $iconData = [
                                        'barang_masuk' => ['icon' => 'fa-arrow-down', 'bg' => 'bg-green-100 text-green-600'],
                                        'barang_keluar' => ['icon' => 'fa-arrow-up', 'bg' => 'bg-red-100 text-red-600'],
                                        'barang_baru' => ['icon' => 'fa-box', 'bg' => 'bg-blue-100 text-blue-600'],
                                        'audit_selesai' => ['icon' => 'fa-check-circle', 'bg' => 'bg-emerald-100 text-emerald-600'],
                                        'audit_baru' => ['icon' => 'fa-clipboard-check', 'bg' => 'bg-indigo-100 text-indigo-600'],
                                        'pengajuan_pengadaan' => ['icon' => 'fa-shopping-cart', 'bg' => 'bg-purple-100 text-purple-600'],
                                        'verifikasi_pengadaan' => ['icon' => 'fa-file-invoice-dollar', 'bg' => 'bg-yellow-100 text-yellow-600'],
                                        'perawatan_barang' => ['icon' => 'fa-tools', 'bg' => 'bg-orange-100 text-orange-600'],
                                        'jadwal_audit_baru' => ['icon' => 'fa-calendar-check', 'bg' => 'bg-cyan-100 text-cyan-600'],
                                    ];
                                    $currentIcon = $iconData[$notif->type] ?? ['icon' => 'fa-bell', 'bg' => 'bg-gray-100 text-gray-600'];
                                @endphp
                                <a href="{{ route('admin.notifikasi.show', $notif->id) }}" class="list-group-item list-group-item-action border-0 {{ !$notif->is_read ? 'bg-light' : '' }}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <i class="fas {{ $currentIcon['icon'] }} {{ $currentIcon['bg'] }} p-2 rounded-circle"></i>
                                            {{ $notif->title }}
                                        </h6>
                                        <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 text-sm">{{ $notif->message }}</p>
                                </a>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <p class="text-center text-muted my-5">Tidak ada notifikasi.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
