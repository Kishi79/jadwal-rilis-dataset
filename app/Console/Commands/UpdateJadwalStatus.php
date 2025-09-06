<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JadwalRilis;
use Carbon\Carbon;

class UpdateJadwalStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jadwal:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status jadwal rilis dataset secara otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memperbarui status jadwal rilis...');
        
        $today = Carbon::today();
        
        // Update jadwal yang sudah lewat tapi belum dirilis
        $updated = JadwalRilis::where('status', 'Belum Rilis')
            ->where('jadwal_rilis', '<', $today)
            ->update(['status' => 'Terlambat']);
        
        if ($updated > 0) {
            $this->info("Berhasil memperbarui {$updated} jadwal menjadi 'Terlambat'");
        } else {
            $this->info('Tidak ada jadwal yang perlu diperbarui');
        }
        
        return Command::SUCCESS;
    }
}