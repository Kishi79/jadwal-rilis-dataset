@extends('layouts.app')

@section('title', 'Tambah Jadwal Rilis')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Tambah Jadwal Rilis Dataset</h2>
                    <a href="{{ route('admin.jadwal.index') }}"
                        class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>

                @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('admin.jadwal.store') }}" method="POST" id="form-jadwal">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="dataset_judul" class="block text-sm font-medium text-gray-700 mb-2">
                                Judul Dataset <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="dataset_judul"
                                   id="dataset_judul"
                                   list="dataset-list"
                                   value="{{ old('dataset_judul') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dataset_judul') border-red-500 @enderror"
                                   placeholder="Ketik untuk mencari atau masukkan judul baru"
                                   required>

                            <datalist id="dataset-list">
                                @foreach($existingDatasetTitles as $title)
                                    <option value="{{ $title }}">
                                @endforeach
                            </datalist>

                            @error('dataset_judul')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Pilih dari daftar yang ada atau masukkan judul baru jika belum tersedia.</p>
                        </div>

                        <div>
                            <label for="opd_id" class="block text-sm font-medium text-gray-700 mb-2">
                                OPD <span class="text-red-500">*</span>
                            </label>
                            <select name="opd_id" id="opd_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('opd_id') border-red-500 @enderror"
                                    required>
                                <option value="">-- Pilih OPD --</option>
                                @foreach($opds as $opd)
                                <option value="{{ $opd['id'] ?? '' }}"
                                        {{ old('opd_id') == ($opd['id'] ?? '') ? 'selected' : '' }}>
                                    {{ $opd['name'] ?? $opd['nama'] ?? 'Unknown OPD' }}
                                </option>
                                @endforeach
                            </select>
                            @error('opd_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sektoral" class="block text-sm font-medium text-gray-700 mb-2">
                                Sektoral
                            </label>
                            <select name="sektoral" id="sektoral"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Pilih Sektoral --</option>
                                @foreach($sektoralList as $sektoral)
                                <option value="{{ $sektoral }}"
                                        {{ old('sektoral') == $sektoral ? 'selected' : '' }}>
                                    {{ $sektoral }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="periode_waktu" class="block text-sm font-medium text-gray-700 mb-2">
                                Periode Waktu <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="periode_waktu"
                                   id="periode_waktu"
                                   value="{{ old('periode_waktu') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('periode_waktu') border-red-500 @enderror"
                                   placeholder="Contoh: Triwulan I 2024, Tahun 2024, Januari 2024"
                                   required>
                            @error('periode_waktu')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jadwal_rilis" class="block text-sm font-medium text-gray-700 mb-2">
                                Jadwal Rilis <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   name="jadwal_rilis"
                                   id="jadwal_rilis"
                                   value="{{ old('jadwal_rilis') }}"
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('jadwal_rilis') border-red-500 @enderror"
                                   required>
                            @error('jadwal_rilis')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                                    required>
                                <option value="Belum Rilis" {{ old('status') == 'Belum Rilis' ? 'selected' : '' }}>
                                    Belum Rilis
                                </option>
                                <option value="Sudah Rilis" {{ old('status') == 'Sudah Rilis' ? 'selected' : '' }}>
                                    Sudah Rilis
                                </option>
                                <option value="Terlambat" {{ old('status') == 'Terlambat' ? 'selected' : '' }}>
                                    Terlambat
                                </option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea name="catatan"
                                      id="catatan"
                                      rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Catatan tambahan (opsional)">{{ old('catatan') }}</textarea>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.jadwal.index') }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Jadwal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.getElementById('form-jadwal');
        form.addEventListener('submit', function(e) {
            const jadwalRilis = document.getElementById('jadwal_rilis').value;
            const today = new Date().toISOString().split('T')[0];

            if (jadwalRilis < today) {
                e.preventDefault();
                alert('Jadwal rilis tidak boleh kurang dari hari ini!');
                return false;
            }
        });
    });
</script>
@endsection