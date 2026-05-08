<?php

namespace App\Console\Commands;

use App\Actions\ResetDemoData;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupDemoInstancesCommand extends Command
{
    protected $signature = 'demo:cleanup {--days=7 : Delete demo instances older than this many days}';

    protected $description = 'Clean up old demo team instances and their associated data';

    public function handle(): void
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $teams = Team::where('created_at', '<', $cutoff)->get();

        $count = 0;

        foreach ($teams as $team) {
            ResetDemoData::run($team);
            $team->delete();
            $count++;
        }

        $this->info("Cleaned up {$count} demo instances older than {$days} days.");
    }
}
