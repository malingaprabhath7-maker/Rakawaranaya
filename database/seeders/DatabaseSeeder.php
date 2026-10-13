<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (config('rakawarana.demo_enabled')) {
            app(DemoSeeder::class)->createDemo(fresh: true);
        }
    }
}
