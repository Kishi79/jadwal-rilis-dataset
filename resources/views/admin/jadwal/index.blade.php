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
                $warna='red' ;
                $pesanHari=$jadwal->jadwal_rilis->isToday() ? "HARI INI" : "BESOK";
                }
                @endphp

                {{-- Banner Notifikasi --}}
                <div id="notif-{{ $jadwal->id }}" class="notification-item relative p-4 border-l-4 bg-{{$warna}}-100 border-{{$warna}}-500 text-{{$warna}}-700 rounded-b shadow-md" data-id="{{ $jadwal->id }}">
                    <div class="flex">
                        <div class="py-1">
                            <svg class="w-6 h-6 text-{{$warna}}-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
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
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Tambah Jadwal
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

                    {{-- 1. TOTAL JADWAL (Reset Filter) --}}
                    <a href="{{ route('admin.jadwal.index') }}"
                        class="block transform transition duration-200 hover:scale-105 cursor-pointer">
                        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500 shadow-sm hover:shadow-md {{ request('status') ? '' : 'ring-2 ring-blue-300' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-blue-600 text-sm font-medium uppercase tracking-wider">Total Jadwal</div>
                                    <div class="text-3xl font-bold text-blue-900 mt-1">{{ $totalJadwal }}</div>
                                </div>
                                <div class="p-2 bg-blue-200 rounded-full text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>

                    {{-- 2. TERLAMBAT (Prioritas Tertinggi - Merah) --}}
                    <a href="{{ route('admin.jadwal.index', ['status' => 'Terlambat']) }}"
                        class="block transform transition duration-200 hover:scale-105 cursor-pointer">
                        <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500 shadow-sm hover:shadow-md {{ request('status') == 'Terlambat' ? 'ring-2 ring-red-300 bg-red-100' : '' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-red-600 text-sm font-medium uppercase tracking-wider">Terlambat</div>
                                    <div class="text-3xl font-bold text-red-900 mt-1">{{ $countTerlambat }}</div>
                                </div>
                                <div class="p-2 bg-red-200 rounded-full text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>

                    {{-- 3. BELUM RILIS (Prioritas Menengah - Kuning) --}}
                    <a href="{{ route('admin.jadwal.index', ['status' => 'Belum Rilis']) }}"
                        class="block transform transition duration-200 hover:scale-105 cursor-pointer">
                        <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500 shadow-sm hover:shadow-md {{ request('status') == 'Belum Rilis' ? 'ring-2 ring-yellow-300 bg-yellow-100' : '' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-yellow-700 text-sm font-medium uppercase tracking-wider">Belum Rilis</div>
                                    <div class="text-3xl font-bold text-yellow-900 mt-1">{{ $countBelumRilis }}</div>
                                </div>
                                <div class="p-2 bg-yellow-200 rounded-full text-yellow-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>

                    {{-- 4. SUDAH RILIS (Aman - Hijau) --}}
                    <a href="{{ route('admin.jadwal.index', ['status' => 'Sudah Rilis']) }}"
                        class="block transform transition duration-200 hover:scale-105 cursor-pointer">
                        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500 shadow-sm hover:shadow-md {{ request('status') == 'Sudah Rilis' ? 'ring-2 ring-green-300 bg-green-100' : '' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-green-600 text-sm font-medium uppercase tracking-wider">Sudah Rilis</div>
                                    <div class="text-3xl font-bold text-green-900 mt-1">{{ $countSudahRilis }}</div>
                                </div>
                                <div class="p-2 bg-green-200 rounded-full text-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>

                </div>

                {{-- Indikator Filter Aktif (Opsional, agar user sadar sedang memfilter) --}}
                @if(request('status'))
                <div class="mb-4 flex items-center justify-between bg-gray-50 p-3 rounded border border-gray-200">
                    <span class="text-gray-600">
                        Menampilkan jadwal dengan status:
                        <span class="font-bold px-2 py-1 rounded 
            {{ request('status') == 'Terlambat' ? 'bg-red-100 text-red-700' : '' }}
            {{ request('status') == 'Belum Rilis' ? 'bg-yellow-100 text-yellow-800' : '' }}
            {{ request('status') == 'Sudah Rilis' ? 'bg-green-100 text-green-700' : '' }}
        ">
                            {{ request('status') }}
                        </span>
                    </span>
                    <a href="{{ route('admin.jadwal.index') }}" class="text-sm text-blue-600 hover:underline flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset Filter
                    </a>
                </div>
                @endif

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
                                        @if(auth()->user()->isAdmin())
                                        <button onclick="sendReminder({{ $jadwal->id }})"
                                            class="text-yellow-500 hover:text-yellow-700 mx-1"
                                            title="Kirim Peringatan Rilis">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                            </svg>
                                        </button>
                                        @endif
                                        <a href="{{ route('admin.jadwal.edit', $jadwal) }}"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
                    {{ $jadwalRilis->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cari semua tombol 'x' untuk menutup notifikasi
        const dismissButtons = document.querySelectorAll('.dismiss-btn');

        // Tambahkan event listener untuk setiap tombol
        dismissButtons.forEach(button => {
            button.addEventListener('click', function() {
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
<script>
    function sendReminder(id) {
        if (!confirm('Apakah Anda yakin ingin mengirim peringatan ke OPD terkait dataset ini?')) return;

        // Tampilkan loading (opsional)

        fetch(`/admin/jadwal/${id}/remind`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message); // Atau pakai SweetAlert jika ada
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>
@endpush