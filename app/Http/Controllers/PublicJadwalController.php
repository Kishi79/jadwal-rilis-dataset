<?php

namespace App\Http\Controllers;

use App\Models\JadwalRilis;
use App\Services\SatuDataApiService;
use Illuminate\Http\Request;

class PublicJadwalController extends Controller
{
    protected $apiService;

    public function __construct(SatuDataApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display public jadwal rilis page
     */
    public function index()
    {
        // Get filter options
        $opds = $this->apiService->getOpds();
        $sektoralList = $this->apiService->getSektoralList();
        $years = JadwalRilis::selectRaw('YEAR(jadwal_rilis) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('public.jadwal', compact('opds', 'sektoralList', 'years'));
    }

    /**
     * Get jadwal data for DataTables (AJAX)
     */
    public function getData(Request $request)
    {
        $query = JadwalRilis::query();

        // Apply filters
        $query->filterByOpd($request->opd_id)
            ->filterBySektoral($request->sektoral)
            ->filterByStatus($request->status)
            ->filterByYear($request->year)
            ->filterByMonth($request->month);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('dataset_judul', 'like', "%{$search}%")
                    ->orWhere('opd_nama', 'like', "%{$search}%")
                    ->orWhere('sektoral', 'like', "%{$search}%")
                    ->orWhere('periode_waktu', 'like', "%{$search}%");
            });
        }

        // Get total count before filtering
        $totalData = JadwalRilis::count();
        $totalFiltered = $query->count();

        // Sorting
        $columns = ['id', 'dataset_judul', 'opd_nama', 'sektoral', 'periode_waktu', 'jadwal_rilis', 'status'];
        $orderColumn = $columns[$request->order[0]['column'] ?? 0];
        $orderDir = $request->order[0]['dir'] ?? 'asc';
        
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $data = $query->get();

        // Format data for DataTables
        $formattedData = $data->map(function ($item, $index) use ($start) {
            // Update status otomatis
            $item->updateStatusOtomatis();
            
            return [
                'no' => $start + $index + 1,
                'dataset_judul' => $item->dataset_judul,
                'opd_nama' => $item->opd_nama,
                'sektoral' => $item->sektoral ?? '-',
                'periode_waktu' => $item->periode_waktu,
                'jadwal_rilis' => $item->jadwal_rilis->format('d/m/Y'),
                'status' => $this->formatStatus($item->status),
                'catatan' => $item->catatan
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $formattedData
        ]);
    }

    /**
     * Format status dengan badge
     */
    private function formatStatus($status)
    {
        $badges = [
            'Belum Rilis' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum Rilis</span>',
            'Sudah Rilis' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Sudah Rilis</span>',
            'Terlambat' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Terlambat</span>',
        ];

        return $badges[$status] ?? $status;
    }
}
