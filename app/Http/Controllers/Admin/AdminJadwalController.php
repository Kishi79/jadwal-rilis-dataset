<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalRilis;
use App\Services\SatuDataApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\JadwalBaruDibuatOlehOPD;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PeringatanRilisDataset;

class AdminJadwalController extends Controller
{
    protected $apiService;

    public function __construct(SatuDataApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Menampilkan daftar jadwal.
     */
    // app/Http/Controllers/Admin/AdminJadwalController.php

    public function index(Request $request)
    {
        // 1. Base Query (Query Dasar)
        $query = JadwalRilis::query();
        $user = auth()->user();
        $notifikasiJadwal = [];

        // Filter OPD jika user bukan admin
        if ($user->isOpd()) {
            $query->where('opd_id', $user->opd_id);

            // Logic notifikasi (tetap sama)
            $notifikasiJadwal = JadwalRilis::where('opd_id', $user->opd_id)
                ->where('status', 'Belum Rilis')
                ->whereBetween('jadwal_rilis', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
                ->orderBy('jadwal_rilis', 'asc')
                ->get();
        }

        // 2. HITUNG STATISTIK (FIX COUNT ISSUE)
        // Kita clone query agar hitungan ini mengambil seluruh data di database
        // tanpa terpengaruh pagination atau filter yang nanti akan kita buat
        $statsQuery = clone $query;

        $totalJadwal = $statsQuery->count();
        $countTerlambat = (clone $statsQuery)->where('status', 'Terlambat')->count();
        $countBelumRilis = (clone $statsQuery)->where('status', 'Belum Rilis')->count();
        $countSudahRilis = (clone $statsQuery)->where('status', 'Sudah Rilis')->count();

        // 3. LOGIKA FILTER (INTERAKTIVITAS KARTU)
        // Jika ada parameter ?status=Terlambat di URL, maka filter tabelnya
        if ($request->has('status') && $request->status != 'total') {
            $query->where('status', $request->status);
        }

        // 4. Ambil data untuk tabel (Paginate)
        $jadwalRilis = $query->latest()->paginate(10);

        // Update status otomatis (tetap sama)
        foreach ($jadwalRilis as $jadwal) {
            $jadwal->updateStatusOtomatis();
        }

        // Kirim variabel statistik baru ke View
        return view('admin.jadwal.index', compact(
            'jadwalRilis',
            'notifikasiJadwal',
            'totalJadwal',
            'countTerlambat',
            'countBelumRilis',
            'countSudahRilis'
        ));
    }

    /**
     * Menampilkan form membuat jadwal baru.
     */
    public function create()
    {
        $opds = $this->apiService->getOpds();
        $sektoralList = $this->apiService->getSektoralList();
        $existingDatasetTitles = JadwalRilis::select('dataset_judul')->distinct()->orderBy('dataset_judul', 'asc')->pluck('dataset_judul');

        return view('admin.jadwal.create', compact('opds', 'sektoralList', 'existingDatasetTitles'));
    }

    /**
     * Menyimpan jadwal rilis baru.
     */
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

            $jadwalBaru = JadwalRilis::create([
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

            // --- TAMBAHAN LOGIKA NOTIFIKASI ---
            // Jika yang membuat adalah user OPD, kirim notifikasi ke semua admin
            if ($user->isOpd()) {
                $admins = User::where('role', 'admin')->get();
                Notification::send($admins, new JadwalBaruDibuatOlehOPD($jadwalBaru));
            }
            // --- BATAS TAMBAHAN ---

            DB::commit();
            return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal rilis berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating jadwal: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Edit jadwal.
     */
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

    /**
     * Update jadwal.
     */
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

    /**
     * Hapus jadwal.
     */
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
    public function sendReminder($id)
    {
        try {
            $jadwal = JadwalRilis::findOrFail($id);

            // --- PERBAIKAN DISINI ---
            // Jangan cari berdasarkan nama, tapi cari berdasarkan opd_id
            // Kita mencari User yang kolom 'opd_id'-nya sama dengan 'opd_id' di jadwal
            $opdUser = User::where('opd_id', $jadwal->opd_id)->first();

            if (!$opdUser) {
                // Debugging (Opsional): Cek di Log kenapa tidak ketemu
                // \Illuminate\Support\Facades\Log::error("User tidak ketemu untuk OPD ID: " . $jadwal->opd_id);

                return response()->json(['status' => 'error', 'message' => 'Akun User untuk OPD ini tidak ditemukan.'], 404);
            }

            // Kirim Notifikasi
            $opdUser->notify(new PeringatanRilisDataset($jadwal));

            return response()->json(['status' => 'success', 'message' => 'Peringatan berhasil dikirim ke OPD.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengirim peringatan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper untuk ambil nama OPD dari API.
     */
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
