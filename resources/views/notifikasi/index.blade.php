@extends('notifikasi.layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Daftar Notifikasi</h6>
                        <form method="POST" action="{{ route('notifikasi.mark-all-as-read') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipe</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Judul</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pesan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifikasis as $notifikasi)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                @if ($notifikasi->is_read)
                                                    <span class="badge badge-sm bg-gradient-success">Dibaca</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-warning">Belum Dibaca</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                @if ($notifikasi->tipe == 'barang_masuk')
                                                    <span class="badge badge-sm bg-gradient-info">Barang Masuk</span>
                                                @elseif($notifikasi->tipe == 'barang_keluar')
                                                    <span class="badge badge-sm bg-gradient-danger">Barang Keluar</span>
                                                @elseif($notifikasi->tipe == 'audit')
                                                    <span class="badge badge-sm bg-gradient-primary">Audit</span>
                                                @elseif($notifikasi->tipe == 'pengadaan')
                                                    <span class="badge badge-sm bg-gradient-secondary">Pengadaan</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <h6 class="mb-0 text-sm">{{ $notifikasi->judul }}</h6>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs text-secondary mb-0">{{ Str::limit($notifikasi->pesan, 50) }}</p>
                                        </td>
                                        <td>
                                            <span class="text-secondary text-xs">{{ $notifikasi->created_at->format('d M Y, H:i') }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('notifikasi.show', $notifikasi->id) }}"
                                                   class="btn btn-sm btn-primary me-2">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (!$notifikasi->is_read)
                                                    <form method="POST" action="{{ route('notifikasi.mark-as-read', $notifikasi->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <p class="text-secondary">Tidak ada notifikasi</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-3">
                    {{ $notifikasis->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
