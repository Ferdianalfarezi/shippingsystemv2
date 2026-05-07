<?php
// app/Console/Commands/PurgeExpiredSlipHpm.php

namespace App\Console\Commands;

use App\Models\SlipHpm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeExpiredSlipHpm extends Command
{
    protected $signature   = 'sliphpm:purge-expired';
    protected $description = 'Hapus data SlipHpm yang sudah melewati expires_at';

    public function handle(): int
    {
        $deleted = SlipHpm::where('expires_at', '<=', now())->delete();

        Log::info("SlipHpm purge: {$deleted} rows deleted.");
        $this->info("Deleted {$deleted} expired SlipHpm rows.");

        return Command::SUCCESS;
    }
}