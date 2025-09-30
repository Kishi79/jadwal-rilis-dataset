@extends('layouts.app')

@section('title', 'Jadwal Rilis Dataset')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-6">Jadwal Rilis Dataset Satu Data Garut</h2>
                
                <!-- Filter Section -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3">Filter Data</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <!-- OPD Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">OPD</label>
                            <select id="filter-opd" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua OPD</option>
                                @foreach($opds as $opd)
                                    <option value="{{ $opd['id'] ?? '' }}">
                                        {{ $opd['name'] ?? $opd['nama'] ?? 'Unknown' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sektoral Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sektoral</label>
                            <select id="filter-sektoral" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Sektoral</option>
                                @foreach($sektoralList as $sektoral)
                                    <option value="{{ $sektoral }}">{{ $sektoral }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="filter-status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Status</option>
                                <option value="Belum Rilis">Belum Rilis</option>
                                <option value="Sudah Rilis">Sudah Rilis</option>
                                <option value="Terlambat">Terlambat</option>
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <select id="filter-year" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Month Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                            <select id="filter-month" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Bulan</option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex gap-2">
                        <button id="btn-filter" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Terapkan Filter
                        </button>
                        <button id="btn-reset" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Reset Filter
                        </button>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="overflow-x-auto">
                    <table id="jadwal-table" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Dataset</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OPD</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sektoral</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal Rilis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#jadwal-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('public.jadwal.data') }}",
            data: function(d) {
                d.opd_id = $('#filter-opd').val();
                d.sektoral = $('#filter-sektoral').val();
                d.status = $('#filter-status').val();
                d.year = $('#filter-year').val();
                d.month = $('#filter-month').val();
            }
        },
        columns: [
            { data: 'no', name: 'no', orderable: false, searchable: false },
            { data: 'dataset_judul', name: 'dataset_judul' },
            { data: 'opd_nama', name: 'opd_nama' },
            { data: 'sektoral', name: 'sektoral' },
            { data: 'periode_waktu', name: 'periode_waktu' },
            { data: 'jadwal_rilis', name: 'jadwal_rilis' },
            { data: 'status', name: 'status' }
        ],
        order: [[5, 'asc']],
        language: {
            processing: "Memproses...",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan halaman _PAGE_ dari _PAGES_",
            infoEmpty: "Tidak ada data tersedia",
            infoFiltered: "(difilter dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Apply filters
    $('#btn-filter').click(function() {
        table.draw();
    });

    // Reset filters
    $('#btn-reset').click(function() {
        $('#filter-opd').val('');
        $('#filter-sektoral').val('');
        $('#filter-status').val('');
        $('#filter-year').val('');
        $('#filter-month').val('');
        table.draw();
    });

    // Apply filter on Enter key
    $('.filter-input').keypress(function(e) {
        if(e.which == 13) {
            table.draw();
        }
    });
});
</script>
@endpush
