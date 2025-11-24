<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';

    // PERBAIKAN: Tambahkan 'harga_satuan' dan hapus 'ketersediaan_dana'
    protected $fillable = [
        'kode_pengajuan',
        'nama_barang',
        'spesifikasi',
        'jumlah',
        'harga_satuan', // TAMBAHKAN INI
        'satuan',
        'alasan',
        'kebutuhan',
        'user_id',
        'status',
        'keterangan',
        'file_pengajuan',
        'urgensi', // K1 - Tingkat Urgensi Barang (Benefit)
        'ketersediaan_stok', // K2 - Ketersediaan Stok Barang (Cost)
        // 'ketersediaan_dana', -> HAPUS, TIDAK LAGI DIGUNAKAN
    ];

    protected $casts = [
        'kebutuhan' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function analisisTopsis()
    {
        return $this->hasOne(AnalisisTopsis::class);
    }

    /**
     * Generate kode pengajuan otomatis
     */
    public static function generateKode()
    {
        $prefix = 'PNG';
        $date = now()->format('Ymd');
        // Cari pengajuan terakhir pada hari ini
        $lastKode = self::where('kode_pengajuan', 'like', $prefix . $date . '%')
                        ->orderBy('kode_pengajuan', 'desc')
                        ->value('kode_pengajuan');

        if (!$lastKode) {
            // Jika tidak ada, mulai dari 001
            $number = 1;
        } else {
            // Jika ada, ambil 3 digit terakhir dan tambahkan 1
            $number = intval(substr($lastKode, -3)) + 1;
        }

        return $prefix . $date . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
