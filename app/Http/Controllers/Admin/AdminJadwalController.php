<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalRilis;
use App\Services\SatuDataApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminJadwalController extends Controller
{
    protected $apiService;

    public function __construct(SatuDataApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display admin dashboard with jadwal list
     */
    public function index()
    {
        $jadwalRilis = JadwalRilis::latest()->paginate(10);
        
        // Update status otomatis untuk setiap jadwal
        foreach ($jadwalRilis as $jadwal) {
            $jadwal->updateStatusOtomatis();
        }
        
        return view('admin.jadwal.index', compact('jadwalRilis'));
    }

    /**
     * Show form for creating new jadwal
     */
    public function create()
    {
        $datasets = $this->apiService->getDatasets();
        $opds = $this->apiService->getOpds();
        $sektoralList = $this->apiService->getSektoralList();
        
        return view('admin.jadwal.create', compact('datasets', 'opds', 'sektoralList'));
    }

    /**
     * Store new jadwal rilis
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dataset_id' => 'required|string',
            'opd_id' => 'required|string',
            'sektoral' => 'nullable|string',
            'periode_waktu' => 'required|string|max:255',
            'jadwal_rilis' => 'required|date|after_or_equal:today',
            'status' => 'required|in:Belum Rilis,Sudah Rilis,Terlambat',
            'catatan' => 'nullable|string'
        ], [
            'dataset_id.required' => 'Dataset harus dipilih',
            'opd_id.required' => 'OPD harus dipilih',
            'periode_waktu.required' => 'Periode waktu harus diisi',
            'jadwal_rilis.required' => 'Jadwal rilis harus diisi',
            'jadwal_rilis.after_or_equal' => 'Jadwal rilis tidak boleh kurang dari hari ini',
            'status.required' => 'Status harus dipilih'
        ]);

        try {
            DB::beginTransaction();

            // Get dataset and OPD details from selected IDs
            $datasetInfo = $this->getDatasetInfo($request->dataset_id);
            $opdInfo = $this->getOpdInfo($request->opd_id);

            if (!$datasetInfo || !$opdInfo) {
                throw new \Exception('Dataset atau OPD tidak ditemukan');
            }

            $jadwalRilis = JadwalRilis::create([
                'dataset_id' => $request->dataset_id,
                'dataset_judul' => $datasetInfo['judul'],
                'opd_id' => $request->opd_id,
                'opd_nama' => $opdInfo['nama'],
                'sektoral' => $request->sektoral,
                'periode_waktu' => $request->periode_waktu,
                'jadwal_rilis' => $request->jadwal_rilis,
                'status' => $request->status,
                'catatan' => $request->catatan,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('admin.jadwal.index')
                ->with('success', 'Jadwal rilis berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating jadwal rilis: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing jadwal
     */
    public function edit(JadwalRilis $jadwal)
    {
        $datasets = $this->apiService->getDatasets();
        $opds = $this->apiService->getOpds();
        $sektoralList = $this->apiService->getSektoralList();
        
        return view('admin.jadwal.edit', compact('jadwal', 'datasets', 'opds', 'sektoralList'));
    }

    /**
     * Update jadwal rilis
     */
    public function update(Request $request, JadwalRilis $jadwal)
    {
        $validated = $request->validate([
            'dataset_id' => 'required|string',
            'opd_id' => 'required|string',
            'sektoral' => 'nullable|string',
            'periode_waktu' => 'required|string|max:255',
            'jadwal_rilis' => 'required|date',
            'status' => 'required|in:Belum Rilis,Sudah Rilis,Terlambat',
            'catatan' => 'nullable|string'
        ], [
            'dataset_id.required' => 'Dataset harus dipilih',
            'opd_id.required' => 'OPD harus dipilih',
            'periode_waktu.required' => 'Periode waktu harus diisi',
            'jadwal_rilis.required' => 'Jadwal rilis harus diisi',
            'status.required' => 'Status harus dipilih'
        ]);

        try {
            DB::beginTransaction();

            // Get dataset and OPD details if changed
            $datasetInfo = $this->getDatasetInfo($request->dataset_id);
            $opdInfo = $this->getOpdInfo($request->opd_id);

            if (!$datasetInfo || !$opdInfo) {
                throw new \Exception('Dataset atau OPD tidak ditemukan');
            }

            $jadwal->update([
                'dataset_id' => $request->dataset_id,
                'dataset_judul' => $datasetInfo['judul'],
                'opd_id' => $request->opd_id,
                'opd_nama' => $opdInfo['nama'],
                'sektoral' => $request->sektoral,
                'periode_waktu' => $request->periode_waktu,
                'jadwal_rilis' => $request->jadwal_rilis,
                'status' => $request->status,
                'catatan' => $request->catatan,
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('admin.jadwal.index')
                ->with('success', 'Jadwal rilis berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating jadwal rilis: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete jadwal rilis
     */
    public function destroy(JadwalRilis $jadwal)
    {
        try {
            $jadwal->delete();
            
            return redirect()->route('admin.jadwal.index')
                ->with('success', 'Jadwal rilis berhasil dihapus');
                
        } catch (\Exception $e) {
            Log::error('Error deleting jadwal rilis: ' . $e->getMessage());
            
            return back()->with('error', 'Terjadi kesalahan saat menghapus jadwal');
        }
    }

    /**
     * Get dataset info from API or extract from datasets array
     */
    private function getDatasetInfo($datasetId)
    {
        $datasets = $this->apiService->getDatasets();
        
        foreach ($datasets as $dataset) {
            if (isset($dataset['id']) && $dataset['id'] == $datasetId) {
                return [
                    'id' => $dataset['id'],
                    'judul' => $dataset['title'] ?? $dataset['judul'] ?? 'Unknown Dataset'
                ];
            }
        }
        
        return null;
    }

    /**
     * Get OPD info from API or extract from OPDs array
     */
    private function getOpdInfo($opdId)
    {
        $opds = $this->apiService->getOpds();
        
        foreach ($opds as $opd) {
            if (isset($opd['id']) && $opd['id'] == $opdId) {
                return [
                    'id' => $opd['id'],
                    'nama' => $opd['name'] ?? $opd['nama'] ?? 'Unknown OPD'
                ];
            }
        }
        
        return null;
    }

    /**
     * Get dataset details via AJAX
     */
    public function getDatasetDetails(Request $request)
    {
        $datasetId = $request->dataset_id;
        
        if (!$datasetId) {
            return response()->json(['error' => 'Dataset ID required'], 400);
        }

        $datasets = $this->apiService->getDatasets();
        
        foreach ($datasets as $dataset) {
            if (isset($dataset['id']) && $dataset['id'] == $datasetId) {
                // Extract OPD info from dataset if available
                $opdId = $dataset['organization']['id'] ?? $dataset['opd_id'] ?? null;
                $sektoral = $dataset['groups'][0]['title'] ?? $dataset['sektoral'] ?? null;
                
                return response()->json([
                    'opd_id' => $opdId,
                    'sektoral' => $sektoral
                ]);
            }
        }
        
        return response()->json(['error' => 'Dataset not found'], 404);
    }
}
