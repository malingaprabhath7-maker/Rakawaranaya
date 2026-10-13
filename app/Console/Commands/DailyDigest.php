<?php

namespace App\Console\Commands;

use App\Mail\DailyDigestMail;
use App\Models\User;
use App\Services\Insights;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class DailyDigest extends Command
{
    protected $signature = 'rakawarana:daily-digest';

    protected $description = 'E-mail each advisor today\'s premiums, renewals, birthdays and tasks';

    public function handle(): int
    {
        $sent = 0;
        User::where('is_demo', false)
            ->chunkById(100, function ($users) use (&$sent) {
                foreach ($users as $user) {
                    if (data_get($user->settings, 'digest') === false) {
                        continue;
                    }
                    $in = Insights::for($user);
                    $data = [
                        'due' => $in->duePremiums(3),
                        'renewals' => $in->renewals(14),
                        'birthdays' => $in->birthdays(0),
                        'tasks' => $in->openTasks(0),
                    ];
                    if (collect($data)->sum(fn ($c) => $c->count()) === 0) {
                        continue;
                    }
                    Mail::to($user)->locale($user->locale ?: 'si')->send(new DailyDigestMail($user, $data));
                    $sent++;
                }
            });
        $this->info("Digest sent to $sent advisor(s).");

        return self::SUCCESS;
    }
}
