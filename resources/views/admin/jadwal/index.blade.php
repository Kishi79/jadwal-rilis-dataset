@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    <!--notiff-->

            <div id="notification-container" class="mb-6 space-y-3">
            @if(auth()->user()->isOpd() && !$notifikasiJadwal->isEmpty())
                @foreach($notifikasiJadwal as $jadwal)
                    @php
                        $selisihHari = now()->startOfDay()->diffInDays($jadwal->jadwal_rilis->startOfDay(), false);
                        $warna = 'yellow';
                        $pesanHari = "dalam {$selisihHari} hari";

                        if ($selisihHari <= 1) {
                            $warna = 'red';
                            $pesanHari = $jadwal->jadwal_rilis->isToday() ? "HARI INI" : "BESOK";
                        }
                    @endphp

                    {{-- Banner Notifikasi --}}
                    <div id="notif-{{ $jadwal->id }}" class="notification-item relative p-4 border-l-4 bg-{{$warna}}-100 border-{{$warna}}-500 text-{{$warna}}-700 rounded-b shadow-md" data-id="{{ $jadwal->id }}">
                        <div class="flex">
                            <div class="py-1">
                                <svg class="w-6 h-6 text-{{$warna}}-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="font-bold">Peringatan Jadwal Rilis</p>
                                <a href="{{ route('admin.jadwal.edit', $jadwal) }}" class="text-sm hover:underline">
                                    Dataset "{{ Str::limit($jadwal->dataset_judul, 40) }}" akan jatuh tempo **{{ $pesanHari }}** ({{ $jadwal->jadwal_rilis->format('d/m/Y') }}).
                                </a>
                            </div>
                        </div>
                        <button class="dismiss-btn absolute top-2 right-2 text-{{$warna}}-500 hover:text-{{$warna}}-700">&times;</button>
                    </div>
                @endforeach
            @endif
        </div>
        <!--end notiff-->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Manajemen Jadwal Rilis Dataset</h2>
                    <a href="{{ route('admin.jadwal.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Tambah Jadwal
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-blue-600 text-sm font-medium">Total Jadwal</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $jadwalRilis->total() }}</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-yellow-600 text-sm font-medium">Belum Rilis</div>
                        <div class="text-2xl font-bold text-yellow-900">
                            {{ $jadwalRilis->where('status', 'Belum Rilis')->count() }}
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-green-600 text-sm font-medium">Sudah Rilis</div>
                        <div class="text-2xl font-bold text-green-900">
                            {{ $jadwalRilis->where('status', 'Sudah Rilis')->count() }}
                        </div>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="text-red-600 text-sm font-medium">Terlambat</div>
                        <div class="text-2xl font-bold text-red-900">
                            {{ $jadwalRilis->where('status', 'Terlambat')->count() }}
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Dataset</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OPD</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal Rilis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($jadwalRilis as $index => $jadwal)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $jadwalRilis->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ Str::limit($jadwal->dataset_judul, 50) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $jadwal->opd_nama }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $jadwal->jadwal_rilis->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($jadwal->status == 'Belum Rilis')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Belum Rilis
                                        </span>
                                    @elseif($jadwal->status == 'Sudah Rilis')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Sudah Rilis
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Terlambat
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.jadwal.edit', $jadwal) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.jadwal.destroy', $jadwal) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Belum ada jadwal rilis yang ditambahkan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $jadwalRilis->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Cari semua tombol 'x' untuk menutup notifikasi
    const dismissButtons = document.querySelectorAll('.dismiss-btn');
    
    // Tambahkan event listener untuk setiap tombol
    dismissButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Cari elemen banner notifikasi terdekat
            const notificationItem = this.closest('.notification-item');

            // Sembunyikan banner dengan efek fade out
            notificationItem.style.transition = 'opacity 0.5s';
            notificationItem.style.opacity = '0';
            setTimeout(() => {
                notificationItem.style.display = 'none';
            }, 500);
        });
    });
});
</script>
@endpush
