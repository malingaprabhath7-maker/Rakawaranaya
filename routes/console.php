<?php

use Illuminate\Support\Facades\Schedule;

// Morning summary e-mail for every advisor who has something due
Schedule::command('rakawarana:daily-digest')->dailyAt('07:00')->timezone('Asia/Colombo')->withoutOverlapping();

// Put the public demo account back to its sample data every night
Schedule::command('rakawarana:reset-demo')->dailyAt('03:00')->timezone('Asia/Colombo');

// Mark policies lapsed when a premium is more than 90 days overdue
Schedule::command('rakawarana:lapse-overdue')->dailyAt('02:30')->timezone('Asia/Colombo');
