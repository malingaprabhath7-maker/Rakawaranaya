<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\InsurancePolicy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Sample data for the public demo. Every name, number and NIC here is made up.
 */
class DemoSeeder extends Seeder
{
    private const FIRST = ['Nimal', 'Kamala', 'Sunil', 'Dilani', 'Chaminda', 'Nadeesha', 'Ruwan', 'Sachini', 'Kasun', 'Iresha', 'Tharindu', 'Malini',
        'Pradeep', 'Hiruni', 'Asanka', 'Shanika', 'Lahiru', 'Nirosha', 'Dinesh', 'Gayani', 'Mahesh', 'Anusha', 'Sampath', 'Chathuri', 'Roshan',
        'Thilini', 'Janaka', 'Upeksha', 'Isuru', 'Madhavi', 'Farhan', 'Fathima', 'Kumar', 'Priya'];

    private const LAST = ['Perera', 'Fernando', 'Silva', 'Jayasinghe', 'Wickramasinghe', 'Bandara', 'Rajapaksha', 'Gunawardena', 'Herath',
        'Dissanayake', 'Senanayake', 'Wijesinghe', 'Amarasinghe', 'Ranasinghe', 'Kumara', 'Weerasinghe', 'Mendis', 'Rathnayake', 'Nawaz', 'Raj'];

    private const CITIES = ['Matara', 'Galle', 'Weligama', 'Akuressa', 'Hambantota', 'Colombo', 'Dehiwala', 'Kandy', 'Kurunegala', 'Tangalle'];

    private const JOBS = ['Teacher', 'Nurse', 'Bank officer', 'Shop owner', 'Engineer', 'Driver', 'Farmer', 'Accountant', 'Government officer', 'Fisherman', 'IT professional', 'Tailor'];

    private const PLANS = [
        'life' => ['Life Protector', 'Family Shield', 'Wealth Builder'],
        'education' => ['Child Education Plan', 'Future Scholar'],
        'retirement' => ['Pension Plus', 'Golden Years'],
        'health' => ['Family Medical Cover', 'Critical Illness Cover'],
        'motor' => ['Comprehensive Motor', 'Third Party Motor'],
        'property' => ['Home Fire Cover'],
    ];

    public function run(): void
    {
        $this->createDemo(fresh: true);
    }

    public function createDemo(bool $fresh = false): User
    {
        return DB::transaction(function () use ($fresh) {
            $email = config('rakawarana.demo_email');
            if ($fresh) {
                User::where('email', $email)->get()->each->delete();
            } elseif ($u = User::where('email', $email)->first()) {
                return $u;
            }

            $user = User::create([
                'name' => 'Malinga (Demo)',
                'email' => $email,
                'password' => Hash::make(Str::random(40)),   // nobody logs in with a password; the demo button signs in
                'company' => 'Arpico Insurance',
                'agent_code' => 'DEMO-001',
                'phone' => '0771234567',
                'monthly_target' => 250000,
                'locale' => 'si',
                'is_demo' => true,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            mt_srand(20261013); // the same sample every night
            $today = today();
            $insurers = ['Arpico Insurance', 'Arpico Insurance', 'Arpico Insurance', 'Ceylinco Life', 'Union Assurance', 'AIA Insurance', 'Sri Lanka Insurance'];

            for ($i = 0; $i < 42; $i++) {
                $first = self::FIRST[$i % count(self::FIRST)];
                $stage = $i < 30 ? 'client' : ['lead', 'lead', 'contacted', 'contacted', 'proposal', 'proposal', 'lead', 'contacted', 'proposal', 'inactive', 'lead', 'proposal'][$i - 30];
                $dob = $today->copy()->subYears(mt_rand(23, 62))->subDays(mt_rand(0, 364));
                if ($i % 9 === 0) {   // a few birthdays this week
                    $dob = $today->copy()->subYears(mt_rand(25, 55))->addDays($i % 5);
                }
                $phone = '07'.mt_rand(0, 8).mt_rand(1000000, 9999999);
                $client = $user->clients()->create([
                    'full_name' => $first.' '.self::LAST[($i * 7) % count(self::LAST)],
                    'nic' => $dob->format('Y').str_pad((string) mt_rand(1, 365), 3, '0', STR_PAD_LEFT).str_pad((string) mt_rand(0, 99999), 5, '0', STR_PAD_LEFT),
                    'date_of_birth' => $dob->toDateString(),
                    'gender' => in_array($first, ['Kamala', 'Dilani', 'Nadeesha', 'Sachini', 'Iresha', 'Malini', 'Hiruni', 'Shanika', 'Nirosha', 'Gayani', 'Anusha', 'Chathuri', 'Thilini', 'Upeksha', 'Madhavi', 'Fathima', 'Priya']) ? 'female' : 'male',
                    'marital_status' => mt_rand(0, 3) ? 'married' : 'single',
                    'dependents' => mt_rand(0, 3),
                    'phone' => $phone,
                    'whatsapp' => $phone,
                    'city' => self::CITIES[mt_rand(0, count(self::CITIES) - 1)],
                    'occupation' => self::JOBS[mt_rand(0, count(self::JOBS) - 1)],
                    'monthly_income' => mt_rand(6, 40) * 5000,
                    'stage' => $stage,
                    'source' => ['referral', 'family', 'walk_in', 'social', 'event', 'existing'][mt_rand(0, 5)],
                    'tags' => $i % 4 === 0 ? ['VIP'] : ($i % 5 === 0 ? ['Family plan'] : []),
                    'last_contacted_at' => now()->subDays(mt_rand(0, 60)),
                    'notes' => $stage === 'proposal' ? 'Interested in an education plan for two children. Follow up after salary day.' : null,
                ]);

                if ($stage !== 'client') {
                    if (in_array($stage, ['contacted', 'proposal'])) {
                        $user->tasks()->create([
                            'client_id' => $client->id,
                            'title' => $stage === 'proposal' ? 'Follow up on proposal' : 'Call to explain plans',
                            'type' => $stage === 'proposal' ? 'follow_up' : 'call',
                            'due_on' => $today->copy()->addDays(mt_rand(-1, 5))->toDateString(),
                            'due_time' => sprintf('%02d:00', mt_rand(9, 17)),
                        ]);
                    }

                    continue;
                }

                $count = mt_rand(1, 3);
                for ($k = 0; $k < $count; $k++) {
                    $type = ['life', 'life', 'motor', 'health', 'education', 'retirement', 'life', 'motor', 'property'][mt_rand(0, 8)];
                    $freq = $type === 'motor' || $type === 'property' ? 'yearly' : ['monthly', 'monthly', 'monthly', 'quarterly', 'yearly'][mt_rand(0, 4)];
                    $start = $today->copy()->subMonths(mt_rand(1, 40))->day(mt_rand(1, 28));
                    if ($k === 0 && $i % 5 === 2) {   // some new business this month, so the target bar has progress
                        $start = $today->copy()->startOfMonth()->addDays(mt_rand(0, max(0, $today->day - 1)));
                    }
                    $premium = match ($type) {
                        'motor' => mt_rand(25, 120) * 1000,
                        'health' => mt_rand(3, 12) * 1000,
                        'property' => mt_rand(8, 30) * 1000,
                        default => mt_rand(2, 15) * 1000,
                    } * ($freq === 'quarterly' ? 3 : ($freq === 'yearly' && in_array($type, ['life', 'education', 'retirement', 'health']) ? 11 : 1));

                    $policy = new InsurancePolicy([
                        'client_id' => $client->id,
                        'insurer' => $type === 'motor' ? ['Arpico Insurance', 'Sri Lanka Insurance', 'Ceylinco General Insurance'][mt_rand(0, 2)] : $insurers[mt_rand(0, count($insurers) - 1)],
                        'type' => $type,
                        'policy_number' => strtoupper(substr($type, 0, 2)).'-'.mt_rand(1000000, 9999999),
                        'plan_name' => self::PLANS[$type][mt_rand(0, count(self::PLANS[$type]) - 1)] ?? null,
                        'sum_assured' => $type === 'motor' ? mt_rand(20, 120) * 100000 : mt_rand(5, 50) * 100000,
                        'premium' => $premium,
                        'frequency' => $freq,
                        'start_date' => $start->toDateString(),
                        'status' => 'active',
                        'commission_rate' => $type === 'motor' ? 10 : ($type === 'health' ? 15 : 25),
                        'vehicle_number' => $type === 'motor' ? ['CAB', 'KX', 'BFG', 'WP CAR', 'SP BBK'][mt_rand(0, 4)].'-'.mt_rand(1000, 9999) : null,
                        'end_date' => in_array($type, ['motor', 'health', 'property']) ? null : $start->copy()->addYears(mt_rand(10, 25))->toDateString(),
                    ]);
                    $policy->user_id = $user->id;

                    // every due date from the start up to today; most are paid, a few policies fall behind
                    $dues = [];
                    $due = $start->copy();
                    for ($guard = 0; $due->lte($today) && $guard < 60; $guard++) {
                        $dues[] = $due->copy();
                        $due = $policy->dueAfter($due);
                    }
                    $behind = mt_rand(0, 9) === 0 ? min(count($dues), mt_rand(1, 2)) : 0;
                    // keep overdue premiums realistic: at most about two months late
                    while ($behind > 0 && $dues[count($dues) - $behind]->lt($today->copy()->subDays(70))) {
                        $behind--;
                    }
                    $paidDues = array_slice($dues, 0, count($dues) - $behind);
                    $nextDue = $behind ? $dues[count($dues) - $behind] : $due;

                    if (in_array($type, ['motor', 'health', 'property'])) {
                        $policy->end_date = $nextDue->copy()->subDay()->toDateString(); // yearly renewal
                    }
                    $policy->next_due_date = $nextDue->toDateString();
                    $payments = array_map(fn (Carbon $d) => ['due' => $d, 'paid' => $d->copy()->addDays(mt_rand(0, 6))->min($today)], $paidDues);
                    $policy->save();

                    foreach (array_slice($payments, -14) as $p) {
                        $user->payments()->create([
                            'insurance_policy_id' => $policy->id,
                            'paid_on' => $p['paid']->toDateString(),
                            'amount' => $premium,
                            'commission' => round($premium * $policy->commission_rate / 100, 2),
                            'method' => ['cash', 'bank', 'standing_order', 'online'][mt_rand(0, 3)],
                            'covers_due_date' => $p['due']->toDateString(),
                        ]);
                    }
                }
            }

            foreach ([['Submit motor claim documents', 'document', 0], ['Branch meeting', 'meeting', 1], ['Collect premium cheques', 'other', 2]] as [$title, $type, $d]) {
                $user->tasks()->create(['title' => $title, 'type' => $type, 'due_on' => $today->copy()->addDays($d)->toDateString(), 'due_time' => '10:00']);
            }

            return $user;
        });
    }
}
