@extends('pengurus.dashboard.layouts.app')

@section('title', 'Tambah Pengajuan - Pengurus')

@section('content')
    <div id="pengajuan-create" class="w-full px-6 py-6 mx-auto">
        <style>
            /* Localized styling to make the form, tips and stats look polished even
               if the main stylesheet is not available. Scoped to #pengajuan-create. */
            #pengajuan-create .panel {
                background: #ffffff;
                border: 1px solid #e6e9ef;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
                overflow: hidden;
            }
            #pengajuan-create .panel .panel-header {
                padding: 18px 24px;
                border-bottom: 1px solid #eef2f6;
                background: linear-gradient(90deg, rgba(248,250,252,1) 0%, rgba(255,255,255,1) 100%);
            }
            #pengajuan-create .panel .panel-body { padding: 20px 24px; }
            #pengajuan-create .tip-item { padding: 12px 0; border-bottom: 1px dashed #f1f5f9; }
            #pengajuan-create .tip-item:last-child { border-bottom: none; }
            #pengajuan-create .tip-item h6 { margin: 0 0 6px 0; font-weight:700; color:#0f172a; }
            #pengajuan-create .tip-item p { margin:0; color:#6b7280; font-size:0.92rem; }
            #pengajuan-create .stat-box { padding: 18px; border-radius: 8px; background: #fbfdff; }
            #pengajuan-create .stat-box h4 { margin:0; font-size:1.4rem; color:#0f172a; }
            #pengajuan-create .stat-box small { color:#6b7280; display:block; margin-top:6px; }
            #pengajuan-create .form-submit-row { padding: 18px 24px; border-top: 1px solid #eef2f6; background:#ffffff; }
            @media (max-width: 1024px) { #pengajuan-create .panel .panel-header, #pengajuan-create .panel .panel-body { padding-left:16px; padding-right:16px; } }
        </style>
        <!-- Header -->
        <div class="flex flex-wrap -mx-3">
            <div class="flex-none w-full max-w-full px-3">
                <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="p-6 pb-3 mb-0 bg-white rounded-t-2xl">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="mb-0">Tambah Pengajuan Pengadaan Baru</h6>
                                <p class="text-sm leading-normal text-slate-500">
                                    Ajukan barang baru yang dibutuhkan untuk kegiatan gereja
                                </p>
                            </div>
                            <a href="{{ route('pengurus.pengajuan.index') }}" class="inline-block px-6 py-3 font-bold text-center text-black uppercase align-middle transition-all rounded-lg cursor-pointer bg-gradient-to-tl from-red-400 to-red-600 leading-pro text-xs ease-soft-in tracking-tight-soft shadow-soft-md hover:scale-102 active:opacity-85 hover:shadow-soft-xs">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex flex-wrap -mx-3">
            <!-- Main Form -->
            <div class="flex-none w-full max-w-full px-3 lg:w-8/12">
                <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">
                        <h6 class="mb-0">Informasi Pengajuan</h6>
                    </div>
                    <div class="flex-auto px-0 pt-0 pb-2">
                        <div class="p-6">
                            <form method="POST" action="{{ route('pengurus.pengajuan.store') }}" enctype="multipart/form-data">
                                @csrf
                                @include('pengurus.pengajuan.form')
                                <div class="flex flex-wrap mt-6 -mx-3">
                                    <div class="flex-none w-full max-w-full px-3">
                                        <button type="submit" class="inline-block px-6 py-3 mr-3 font-bold text-center text-white uppercase align-middle transition-all rounded-lg cursor-pointer bg-gradient-to-tl from-blue-600 to-cyan-400 leading-pro text-xs ease-soft-in tracking-tight-soft shadow-soft-md hover:scale-102 active:opacity-85 hover:shadow-soft-xs">
                                            <i class="fas fa-save mr-2"></i>Simpan Pengajuan
                                        </button>
                                        <a href="{{ route('pengurus.pengajuan.index') }}" class="inline-block px-6 py-3 font-bold text-center text-white uppercase align-middle transition-all rounded-lg cursor-pointer bg-gradient-to-tl from-gray-400 to-gray-600 leading-pro text-xs ease-soft-in tracking-tight-soft shadow-soft-md hover:scale-102 active:opacity-85 hover:shadow-soft-xs">
                                            <i class="fas fa-times mr-2"></i>Batal
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="flex-none w-full max-w-full px-3 lg:w-4/12">
                <!-- Tips -->
                <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">
                        <h6 class="mb-0">Tips Pengajuan</h6>
                    </div>
                    <div class="flex-auto px-0 pt-0 pb-2">
                        <div class="p-6">
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-box mr-2"></i>Nama Barang</h6>
                                <p class="text-sm text-slate-400">Gunakan nama barang yang jelas dan spesifik (contoh: "Proyektor Epson X123").</p>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-list-ul mr-2"></i>Spesifikasi</h6>
                                <p class="text-sm text-slate-400">Sertakan spesifikasi lengkap: merk, model, ukuran, warna, dan kebutuhan teknis jika perlu.</p>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-sort-numeric-up mr-2"></i>Jumlah & Satuan</h6>
                                <p class="text-sm text-slate-400">Masukkan jumlah barang (minimal 1) dan satuan yang sesuai (Unit, Buah, Paket, dll).</p>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-money-bill-wave mr-2"></i>Harga Satuan</h6>
                                <p class="text-sm text-slate-400">Isi perkiraan harga satuan (angka tanpa tanda titik). Sistem akan menghitung total biaya dan K3 sebagai persentase dari saldo kas untuk analisis.</p>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-bell mr-2"></i>Tingkat Urgensi (K1)</h6>
                                <p class="text-sm text-slate-400">Pilih tingkat urgensi 1 (Tidak Mendesak) sampai 5 (Sangat Penting). Nilai ini adalah kriteria Benefit dalam analisis.</p>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-boxes mr-2"></i>Ketersediaan Stok (K2)</h6>
                                <p class="text-sm text-slate-400">Pilih ketersediaan stok di pasaran: 1 = Stok Habis (prioritas tinggi) hingga 5 = Sangat Banyak. Nilai ini adalah kriteria Cost.</p>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-calendar-alt mr-2"></i>Tanggal Kebutuhan</h6>
                                <p class="text-sm text-slate-400">Tentukan tanggal paling lambat barang dibutuhkan. Tanggal tidak boleh sebelum hari ini.</p>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-comment-alt mr-2"></i>Alasan</h6>
                                <p class="text-sm text-slate-400">Jelaskan alasan dan konteks penggunaan barang (kegiatan, unit yang membutuhkan, estimasi durasi penggunaan).</p>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-blue-600"><i class="fas fa-file-alt mr-2"></i>Dokumen Pendukung</h6>
                                <p class="text-sm text-slate-400">Unggah dokumen pendukung (PDF/DOC/DOCX, max 2MB) seperti proposal atau penawaran harga untuk memperkuat pengajuan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl bg-clip-border">
                    <div class="p-6 pb-0 mb-0 bg-white rounded-t-2xl">
                        <h6 class="mb-0">Statistik Pengajuan</h6>
                    </div>
                    <div class="flex-auto px-0 pt-0 pb-2">
                        <div class="p-6">
                            <div class="flex flex-wrap -mx-3">
                                <div class="flex-none w-1/2 max-w-full px-3">
                                    <div class="text-center border-r border-gray-200">
                                        <h4 class="font-bold text-blue-600">{{ \App\Models\Pengajuan::where('user_id', auth()->id())->count() }}</h4>
                                        <small class="text-slate-400">Total Pengajuan</small>
                                    </div>
                                </div>
                                <div class="flex-none w-1/2 max-w-full px-3">
                                    <div class="text-center">
                                        <h4 class="font-bold text-yellow-600">{{ \App\Models\Pengajuan::where('user_id', auth()->id())->where('status', 'pending')->count() }}</h4>
                                        <small class="text-slate-400">Pending</small>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap -mx-3 mt-4">
                                <div class="flex-none w-1/2 max-w-full px-3">
                                    <div class="text-center border-r border-gray-200">
                                        <h4 class="font-bold text-green-600">{{ \App\Models\Pengajuan::where('user_id', auth()->id())->where('status', 'disetujui')->count() }}</h4>
                                        <small class="text-slate-400">Disetujui</small>
                                    </div>
                                </div>
                                <div class="flex-none w-1/2 max-w-full px-3">
                                    <div class="text-center">
                                        <h4 class="font-bold text-red-600">{{ \App\Models\Pengajuan::where('user_id', auth()->id())->where('status', 'ditolak')->count() }}</h4>
                                        <small class="text-slate-400">Ditolak</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

