<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JadwalRilis extends Model
{
    use HasFactory;

    protected $table = 'jadwal_rilis';

    protected $fillable = [
        'dataset_id',
        'dataset_judul',
        'opd_id',
        'opd_nama',
        'sektoral',
        'periode_waktu',
        'jadwal_rilis',
        'status',
        'catatan',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'jadwal_rilis' => 'date',
    ];

    /**
     * Scope untuk filter berdasarkan OPD
     */
    public function scopeFilterByOpd($query, $opdId)
    {
        return $query->when($opdId, function ($q) use ($opdId) {
            return $q->where('opd_id', $opdId);
        });
    }

    /**
     * Scope untuk filter berdasarkan sektoral
     */
    public function scopeFilterBySektoral($query, $sektoral)
    {
        return $query->when($sektoral, function ($q) use ($sektoral) {
            return $q->where('sektoral', $sektoral);
        });
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeFilterByStatus($query, $status)
    {
        return $query->when($status, function ($q) use ($status) {
            return $q->where('status', $status);
        });
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeFilterByYear($query, $year)
    {
        return $query->when($year, function ($q) use ($year) {
            return $q->whereYear('jadwal_rilis', $year);
        });
    }

    /**
     * Scope untuk filter berdasarkan bulan
     */
    public function scopeFilterByMonth($query, $month)
    {
        return $query->when($month, function ($q) use ($month) {
            return $q->whereMonth('jadwal_rilis', $month);
        });
    }

    /**
     * Update status otomatis berdasarkan tanggal
     */
    public function updateStatusOtomatis()
    {
        $today = Carbon::today();
        
        if ($this->status !== 'Sudah Rilis' && $this->jadwal_rilis < $today) {
            $this->status = 'Terlambat';
            $this->save();
        }
    }

    /**
     * Relasi ke User yang membuat
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User yang mengupdate
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
