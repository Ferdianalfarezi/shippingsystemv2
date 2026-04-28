<?php
// app/Console/Commands/PurgeExpiredKanbanHpm.php

namespace App\Console\Commands;

use App\Models\KanbanHpm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeExpiredKanbanHpm extends Command
{
    protected $signature   = 'kanbanhpm:purge-expired';
    protected $description = 'Hapus data KanbanHpm yang sudah melewati expires_at';

    public function handle(): int
    {
        $deleted = KanbanHpm::where('expires_at', '<=', now())->delete();

        Log::info("KanbanHpm purge: {$deleted} rows deleted.");
        $this->info("Deleted {$deleted} expired KanbanHpm rows.");

        return Command::SUCCESS;
    }
}