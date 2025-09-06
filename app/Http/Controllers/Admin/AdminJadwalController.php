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

    public function index()
    {
        $jadwalRilis = JadwalRilis::latest()->paginate(10);
        foreach ($jadwalRilis as $jadwal) {
            $jadwal->updateStatusOtomatis();
        }
        return view('admin.jadwal.index', compact('jadwalRilis'));
    }

    public function create()
    {
        $opds = $this->apiService->getOpds();
        $sektoralList = $this->apiService->getSektoralList();

        $existingDatasetTitles = JadwalRilis::select('dataset_judul')
            ->distinct()
            ->orderBy('dataset_judul', 'asc')
            ->pluck('dataset_judul');

        // PERBAIKAN: Tambahkan 'existingDatasetTitles' ke compact()
        return view('admin.jadwal.create', compact('opds', 'sektoralList', 'existingDatasetTitles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dataset_judul' => 'required|string|max:255',
            'opd_id' => 'required|string',
            'sektoral' => 'nullable|string',
            'periode_waktu' => 'required|string|max:255',
            'jadwal_rilis' => 'required|date|after_or_equal:today',
            'status' => 'required|in:Belum Rilis,Sudah Rilis,Terlambat',
            'catatan' => 'nullable|string'
        ], [
            'dataset_judul.required' => 'Judul Dataset harus diisi',
            'opd_id.required' => 'OPD harus dipilih',
        ]);

        try {
            DB::beginTransaction();
            $opdInfo = $this->getOpdInfo($request->opd_id);
            if (!$opdInfo) {
                throw new \Exception('OPD tidak ditemukan');
            }

            JadwalRilis::create([
                'dataset_id' => null, // Dibuat null
                'dataset_judul' => $request->dataset_judul, // Diambil dari input
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
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal rilis berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating jadwal rilis: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(JadwalRilis $jadwal)
    {
        // --- PENYESUAIAN UNTUK FUNGSI EDIT ---
        $opds = $this->apiService->getOpds();
        $sektoralList = $this->apiService->getSektoralList();

        $existingDatasetTitles = JadwalRilis::select('dataset_judul')
            ->distinct()
            ->orderBy('dataset_judul', 'asc')
            ->pluck('dataset_judul');
        
        return view('admin.jadwal.edit', compact('jadwal', 'opds', 'sektoralList', 'existingDatasetTitles'));
    }

    public function update(Request $request, JadwalRilis $jadwal)
    {
        // --- PENYESUAIAN UNTUK FUNGSI UPDATE ---
        $request->validate([
            'dataset_judul' => 'required|string|max:255', // Diubah
            'opd_id' => 'required|string',
            'sektoral' => 'nullable|string',
            'periode_waktu' => 'required|string|max:255',
            'jadwal_rilis' => 'required|date',
            'status' => 'required|in:Belum Rilis,Sudah Rilis,Terlambat',
            'catatan' => 'nullable|string'
        ], [
            'dataset_judul.required' => 'Judul Dataset harus diisi', // Diubah
            'opd_id.required' => 'OPD harus dipilih',
        ]);

        try {
            DB::beginTransaction();
            $opdInfo = $this->getOpdInfo($request->opd_id);
            if (!$opdInfo) {
                throw new \Exception('OPD tidak ditemukan');
            }

            $jadwal->update([
                'dataset_id' => null, // Diubah
                'dataset_judul' => $request->dataset_judul, // Diubah
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
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal rilis berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating jadwal rilis: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(JadwalRilis $jadwal)
    {
        try {
            $jadwal->delete();
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal rilis berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting jadwal rilis: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus jadwal');
        }
    }

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

    // Fungsi getDatasetInfo dan getDatasetDetails sudah tidak terlalu relevan dan bisa dihapus jika mau
}