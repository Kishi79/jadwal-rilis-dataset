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
        $query = JadwalRilis::query();
        $user = auth()->user();

        if ($user->isOpd()) {
            $query->where('opd_id', $user->opd_id);
        }

        $jadwalRilis = $query->latest()->paginate(10);

        foreach ($jadwalRilis as $jadwal) {
            $jadwal->updateStatusOtomatis();
        }

        return view('admin.jadwal.index', compact('jadwalRilis'));
    }

    public function create()
    {
        $opds = $this->apiService->getOpds();
        $sektoralList = $this->apiService->getSektoralList();
        $existingDatasetTitles = JadwalRilis::select('dataset_judul')->distinct()->orderBy('dataset_judul', 'asc')->pluck('dataset_judul');

        return view('admin.jadwal.create', compact('opds', 'sektoralList', 'existingDatasetTitles'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'dataset_judul' => 'required|string|max:255',
            'sektoral' => 'nullable|string',
            'periode_waktu' => 'required|string|max:255',
            'jadwal_rilis' => 'required|date|after_or_equal:today',
            'catatan' => 'nullable|string'
        ];
        
        if ($user->isAdmin()) {
            $rules['opd_id'] = 'required|string';
            $rules['status'] = 'required|in:Belum Rilis,Sudah Rilis,Terlambat';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $opdId = $user->isOpd() ? $user->opd_id : $request->opd_id;
            $opdNama = $user->isOpd() ? $user->opd_nama : $this->getOpdInfo($opdId)['nama'];

            JadwalRilis::create([
                'dataset_id' => null,
                'dataset_judul' => $request->dataset_judul,
                'opd_id' => $opdId,
                'opd_nama' => $opdNama,
                'sektoral' => $request->sektoral,
                'periode_waktu' => $request->periode_waktu,
                'jadwal_rilis' => $request->jadwal_rilis,
                'status' => $user->isAdmin() ? $request->status : 'Belum Rilis',
                'catatan' => $request->catatan,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);

            DB::commit();
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal rilis berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating jadwal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(JadwalRilis $jadwal)
    {
        $user = auth()->user();
        if ($user->isOpd() && $jadwal->opd_id !== $user->opd_id) {
            abort(403, 'AKSES DITOLAK');
        }

        $opds = $this->apiService->getOpds();
        $sektoralList = $this->apiService->getSektoralList();
        $existingDatasetTitles = JadwalRilis::select('dataset_judul')->distinct()->orderBy('dataset_judul', 'asc')->pluck('dataset_judul');

        return view('admin.jadwal.edit', compact('jadwal', 'opds', 'sektoralList', 'existingDatasetTitles'));
    }

    public function update(Request $request, JadwalRilis $jadwal)
    {
        $user = auth()->user();
        if ($user->isOpd() && $jadwal->opd_id !== $user->opd_id) {
            abort(403, 'AKSES DITOLAK');
        }

        $rules = [
            'dataset_judul' => 'required|string|max:255',
            'sektoral' => 'nullable|string',
            'periode_waktu' => 'required|string|max:255',
            'jadwal_rilis' => 'required|date',
            'catatan' => 'nullable|string'
        ];

        if ($user->isAdmin()) {
            $rules['opd_id'] = 'required|string';
            $rules['status'] = 'required|in:Belum Rilis,Sudah Rilis,Terlambat';
        }
        
        $request->validate($rules);
        
        try {
            DB::beginTransaction();

            $opdId = $user->isAdmin() ? $request->opd_id : $user->opd_id;
            $opdNama = $user->isAdmin() ? $this->getOpdInfo($opdId)['nama'] : $user->opd_nama;

            $updateData = [
                'dataset_judul' => $request->dataset_judul,
                'opd_id' => $opdId,
                'opd_nama' => $opdNama,
                'sektoral' => $request->sektoral,
                'periode_waktu' => $request->periode_waktu,
                'jadwal_rilis' => $request->jadwal_rilis,
                'catatan' => $request->catatan,
                'updated_by' => $user->id
            ];

            if ($user->isAdmin()) {
                $updateData['status'] = $request->status;
            }

            $jadwal->update($updateData);

            DB::commit();
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal rilis berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating jadwal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(JadwalRilis $jadwal)
    {
        $user = auth()->user();
        if ($user->isOpd() && $jadwal->opd_id !== $user->opd_id) {
            abort(403, 'AKSES DITOLAK');
        }

        try {
            $jadwal->delete();
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal rilis berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting jadwal: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus jadwal.');
        }
    }

    private function getOpdInfo($opdId)
    {
        $opds = $this->apiService->getOpds();
        foreach ($opds as $opd) {
            if (isset($opd['id']) && $opd['id'] == $opdId) {
                return ['nama' => $opd['name'] ?? $opd['nama'] ?? 'Unknown OPD'];
            }
        }
        return null;
    }
}