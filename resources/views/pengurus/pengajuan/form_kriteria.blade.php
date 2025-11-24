<div class="mb-5">
    <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Satuan (Rp)</label>
    <input type="number" name="harga_satuan" value="{{ old('harga_satuan', $pengajuan->harga_satuan ?? '') }}" min="1"
           class="w-full px-4 py-2 text-sm border rounded-lg shadow-sm
                  @error('harga_satuan') border-red-500 @else border-gray-300 @enderror
                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
           placeholder="Contoh: 5000000">
    @error('harga_satuan')
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="mb-5">
    <label class="block text-sm font-semibold text-slate-700 mb-1">Tingkat Urgensi Barang (K1 - Benefit)</label>
    <select name="urgensi" class="w-full px-4 py-2 text-sm border rounded-lg shadow-sm border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
        <option value="">Pilih tingkat urgensi</option>
        <option value="5" {{ old('urgensi', $pengajuan->urgensi ?? '') == '5' ? 'selected' : '' }}>5 - Sangat Penting</option>
        <option value="4" {{ old('urgensi', $pengajuan->urgensi ?? '') == '4' ? 'selected' : '' }}>4 - Penting</option>
        <option value="3" {{ old('urgensi', $pengajuan->urgensi ?? '') == '3' ? 'selected' : '' }}>3 - Sedang</option>
        <option value="2" {{ old('urgensi', $pengajuan->urgensi ?? '') == '2' ? 'selected' : '' }}>2 - Rendah</option>
        <option value="1" {{ old('urgensi', $pengajuan->urgensi ?? '') == '1' ? 'selected' : '' }}>1 - Tidak Mendesak</option>
    </select>
    @error('urgensi')
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
</div>

<div class="mb-5">
    <label class="block text-sm font-semibold text-slate-700 mb-1">Ketersediaan Stok Barang (K2 - Cost)</label>
    <select name="ketersediaan_stok" class="w-full px-4 py-2 text-sm border rounded-lg shadow-sm border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out" required>
        <option value="">Pilih ketersediaan stok</option>
        <option value="1" {{ old('ketersediaan_stok', $pengajuan->ketersediaan_stok ?? '') == '1' ? 'selected' : '' }}>1 - Stok Habis (Prioritas Tinggi)</option>
        <option value="2" {{ old('ketersediaan_stok', $pengajuan->ketersediaan_stok ?? '') == '2' ? 'selected' : '' }}>2 - Sangat Sedikit</option>
        <option value="3" {{ old('ketersediaan_stok', $pengajuan->ketersediaan_stok ?? '') == '3' ? 'selected' : '' }}>3 - Sedikit</option>
        <option value="4" {{ old('ketersediaan_stok', $pengajuan->ketersediaan_stok ?? '') == '4' ? 'selected' : '' }}>4 - Cukup</option>
        <option value="5" {{ old('ketersediaan_stok', $pengajuan->ketersediaan_stok ?? '') == '5' ? 'selected' : '' }}>5 - Sangat Banyak</option>
    </select>
    @error('ketersediaan_stok')
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
</div>
