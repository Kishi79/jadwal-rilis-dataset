<?php
namespace App\Notifications;

use App\Models\JadwalRilis;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JadwalBaruDibuatOlehOPD extends Notification
{
    use Queueable;

    public function __construct(public JadwalRilis $jadwal)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Kita akan simpan notifikasi ini ke database
    }

    public function toArray(object $notifiable): array
    {
        // Data ini yang akan disimpan di kolom 'data' pada tabel notifications
        return [
            'jadwal_id' => $this->jadwal->id,
            'judul_dataset' => $this->jadwal->dataset_judul,
            'opd_nama' => $this->jadwal->opd_nama,
            'message' => "{$this->jadwal->opd_nama} telah menambahkan jadwal baru: '{$this->jadwal->dataset_judul}'."
        ];
    }
}