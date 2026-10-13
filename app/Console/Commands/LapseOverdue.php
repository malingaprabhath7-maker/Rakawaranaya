<?php

namespace App\Console\Commands;

use App\Models\InsurancePolicy;
use Illuminate\Console\Command;

class LapseOverdue extends Command
{
    protected $signature = 'rakawarana:lapse-overdue {--days=90}';

    protected $description = 'Mark active policies as lapsed when a premium is overdue for too long';

    public function handle(): int
    {
        $n = InsurancePolicy::where('status', 'active')
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<', today()->subDays((int) $this->option('days')))
            ->update(['status' => 'lapsed']);
        $this->info("$n policies marked lapsed.");

        return self::SUCCESS;
    }
}
