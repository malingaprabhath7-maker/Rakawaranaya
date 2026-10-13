<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

class ResetDemo extends Command
{
    protected $signature = 'rakawarana:reset-demo';

    protected $description = 'Recreate the public demo account with fresh sample data';

    public function handle(DemoSeeder $seeder): int
    {
        if (! config('rakawarana.demo_enabled')) {
            $this->warn('Demo is disabled (DEMO_ENABLED=false).');

            return self::SUCCESS;
        }
        $seeder->createDemo(fresh: true);
        $this->info('Demo account reset.');

        return self::SUCCESS;
    }
}
