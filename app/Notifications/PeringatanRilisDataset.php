<?php

namespace App\Notifications;

use App\Models\JadwalRilis;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PeringatanRilisDataset extends Notification
{
    use Queueable;

    public function __construct(public JadwalRilis $jadwal)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'jadwal_id' => $this->jadwal->id,
            'judul_dataset' => $this->jadwal->dataset_judul,
            'type' => 'warning', // Kita tandai ini sebagai tipe peringatan
            'message' => "PERINGATAN: Harap segera merilis dataset '{$this->jadwal->dataset_judul}'. Jadwal sudah terlewat atau mendekati batas waktu."
        ];
    }
}