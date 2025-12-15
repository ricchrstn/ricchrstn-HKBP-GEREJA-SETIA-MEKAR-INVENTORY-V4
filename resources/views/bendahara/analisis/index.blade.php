@extends('bendahara.dashboard.layouts.app')
@section('title', 'Analisis TOPSIS - Bendahara')
@section('content')
    <div id="topsis-index" class="w-full px-6 py-6 mx-auto">
        <style>
            /* Fallback table styles for Analisis index page */
            #topsis-index table { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; }
            #topsis-index thead th { background: #f8fafc; color: #111827; border-bottom: 2px solid #e5e7eb; padding: 12px; text-align: left; font-weight: 600; }
            #topsis-index tbody td { border-bottom: 1px solid #e5e7eb; padding: 12px; color: #374151; vertical-align: middle; }
            #topsis-index .bg-white { background-color: #ffffff !important; }
            #topsis-index .shadow-soft-xl { box-shadow: 0 6px 18px rgba(15,23,42,0.06) !important; }
            #topsis-index .rounded-2xl { border-radius: 12px !important; }
            #topsis-index .inline-flex { display: inline-flex; align-items: center; justify-content: center; }
            @media (max-width: 640px) { #topsis-index thead th, #topsis-index tbody td { padding: 8px; } }
        </style>
        <!-- Header Section -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full max-w-full px-3">
                <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="p-6 pb-5 mb-0 bg-white rounded-t-2xl">
                        <div class="flex flex-wrap items-center justify-between">
                            <div>
                                <h6 class="mb-0 text-lg font-bold text-slate-700">Analisis TOPSIS</h6>
                                <p class="mb-0 text-sm leading-normal text-slate-400">Analisis prioritas pengadaan barang menggunakan metode TOPSIS</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('bendahara.analisis.hasil') }}" class="inline-block px-6 py-3 text-xs font-bold text-center text-white uppercase bg-gradient-to-tl from-blue-600 to-cyan-400 rounded-lg shadow-md hover:scale-102 transition-all">
                                    <i class="fas fa-calculator mr-2"></i>
                                    Lihat Hasil Analisis
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Pengajuan -->
        <div class="flex flex-wrap -mx-3">
            <div class="flex-none w-full max-w-full px-3">
                <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">
                        <div class="flex flex-wrap items-center justify-between">
                            <div>
                                <h6 class="mb-0 font-bold">Data Pengajuan</h6>
                                <p class="mb-0 text-sm text-slate-400 mt-1">
                                    <span class="font-medium text-blue-600">{{ $pengajuans->count() }}</span> pengajuan pending
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-auto px-0 pt-0 pb-2">
                        <div class="p-0 overflow-x-auto">
                            <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xxs font-bold uppercase text-slate-400">Kode & Barang</th>
                                        <th class="px-6 py-3 text-left text-xxs font-bold uppercase text-slate-400">Pengaju</th>
                                        <th class="px-6 py-3 text-left text-xxs font-bold uppercase text-slate-400">Kriteria K1 & K2</th>
                                        <th class="px-6 py-3 text-left text-xxs font-bold uppercase text-slate-400">
                                            <div class="flex flex-col">
                                                <span>Kriteria K3 (Persentase Biaya)</span>
                                                <span class="mt-1 text-xs font-normal text-blue-600 flex items-center">
                                                    <i class="fas fa-wallet mr-1"></i>
                                                    Saldo Kas (Anggaran):
                                                    <span class="font-semibold ml-1">Rp {{ number_format($saldoKas, 0, ',', '.') }}</span>
                                                </span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pengajuans as $pengajuan)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="p-2">
                                                <div class="flex px-2 py-1">
                                                    <div>
                                                        <div class="inline-flex items-center justify-center mr-4 h-12 w-12 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500">
                                                            <i class="fas fa-file-alt text-white text-lg"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col justify-center">
                                                        <h6 class="mb-0 text-sm font-semibold">{{ $pengajuan->kode_pengajuan }}</h6>
                                                        <p class="mb-0 text-xs text-slate-400">{{ $pengajuan->nama_barang }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-2">
                                                <p class="mb-0 text-xs font-semibold">{{ $pengajuan->user->name }}</p>
                                                <p class="mb-0 text-xs text-slate-400">{{ $pengajuan->created_at->format('d M Y') }}</p>
                                            </td>
                                            <td class="p-2">
                                                <div class="text-xs space-y-0.5">
                                                    <div><span class="text-slate-500">K1 (Urgensi):</span> <span class="font-semibold">{{ $pengajuan->urgensi }}</span></div>
                                                    <div><span class="text-slate-500">K2 (Stok):</span> <span class="font-semibold">{{ $pengajuan->ketersediaan_stok }}</span></div>
                                                </div>
                                            </td>
                                            <td class="p-2">
                                                @php
                                                    // PERBAIKAN: Hitung total harga dan persentase biaya di sini
                                                    $totalHarga = $pengajuan->harga_satuan * $pengajuan->jumlah;
                                                    $persentaseBiaya = ($saldoKas > 0) ? ($totalHarga / $saldoKas) * 100 : 0;
                                                @endphp
                                                <div class="text-xs">
                                                    <span class="font-semibold">{{ number_format($persentaseBiaya, 2) }}%</span>
                                                    <span class="text-gray-500"> (Total: Rp {{ number_format($totalHarga, 0, ',', '.') }})</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-8 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="mb-4 text-gray-400">
                                                        <i class="fas fa-file-invoice-dollar text-6xl"></i>
                                                    </div>
                                                    <h6 class="text-lg font-semibold text-gray-500 mb-2">Belum ada data pengajuan pengadaan</h6>
                                                    <p class="text-sm text-gray-400 mb-4">Belum ada pengajuan pengadaan yang diajukan oleh pengurus</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kriteria Info -->
        <div class="flex flex-wrap -mx-3 mt-6">
            <div class="w-full max-w-full px-3">
                <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">
                        <h6 class="mb-0">Kriteria yang Digunakan</h6>
                    </div>
                    <div class="flex-auto px-0 pt-0 pb-2">
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach ($kriterias as $kriteria)
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <h6 class="text-sm font-semibold text-slate-700">{{ $kriteria->nama }}</h6>
                                        <span class="px-2 py-1 text-xs rounded-full font-medium {{ $kriteria->tipe == 'benefit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $kriteria->tipe == 'benefit' ? 'Benefit' : 'Cost' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500">Bobot: {{ $kriteria->bobot }}</p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        @if($kriteria->tipe == 'benefit')
                                            Semakin tinggi nilai, semakin baik
                                        @else
                                            Semakin rendah nilai, semakin baik
                                        @endif
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
