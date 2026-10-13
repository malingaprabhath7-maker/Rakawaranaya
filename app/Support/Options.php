<?php

namespace App\Support;

/**
 * Fixed lists used in forms, filters and validation. Labels are English keys translated with __().
 */
final class Options
{
    public const STAGES = [
        'lead' => 'Lead',
        'contacted' => 'Contacted',
        'proposal' => 'Proposal sent',
        'client' => 'Client',
        'inactive' => 'Inactive',
    ];

    public const POLICY_TYPES = [
        'life' => 'Life',
        'motor' => 'Motor',
        'health' => 'Health / Medical',
        'property' => 'Fire / Property',
        'education' => 'Education',
        'retirement' => 'Retirement / Pension',
        'travel' => 'Travel',
        'other' => 'Other',
    ];

    public const FREQUENCIES = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'half_yearly' => 'Half-yearly',
        'yearly' => 'Yearly',
        'single' => 'Single premium',
    ];

    /** months between premiums */
    public const FREQUENCY_MONTHS = [
        'monthly' => 1,
        'quarterly' => 3,
        'half_yearly' => 6,
        'yearly' => 12,
        'single' => 0,
    ];

    public const POLICY_STATUSES = [
        'proposal' => 'Proposal',
        'active' => 'Active',
        'lapsed' => 'Lapsed',
        'matured' => 'Matured',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank' => 'Bank transfer',
        'card' => 'Card',
        'online' => 'Online',
        'cheque' => 'Cheque',
        'standing_order' => 'Standing order',
    ];

    public const TASK_TYPES = [
        'call' => 'Call',
        'meeting' => 'Meeting',
        'follow_up' => 'Follow up',
        'document' => 'Documents',
        'other' => 'Other',
    ];

    public const SOURCES = [
        'referral' => 'Referral',
        'family' => 'Family & friends',
        'walk_in' => 'Walk-in',
        'social' => 'Social media',
        'event' => 'Event / Campaign',
        'existing' => 'Existing client',
        'other' => 'Other',
    ];

    public const GENDERS = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];

    public const MARITAL = ['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'divorced' => 'Divorced'];

    /** Insurers licensed in Sri Lanka (advisors can type any other name too) */
    public const INSURERS = [
        'AIA Insurance',
        'Allianz Insurance Lanka',
        'Amana Takaful',
        'Arpico Insurance',
        'Ceylinco General Insurance',
        'Ceylinco Life',
        'Continental Insurance',
        'Cooperative Insurance',
        'Fairfirst Insurance',
        'HNB Assurance',
        'HNB General Insurance',
        'Janashakthi Insurance',
        'LOLC General Insurance',
        'LOLC Life Assurance',
        'National Insurance Trust Fund',
        'Orient Insurance',
        "People's Insurance",
        'Sanasa General Insurance',
        'Sanasa Life Insurance',
        'Softlogic Life',
        'Sri Lanka Insurance',
        'Union Assurance',
    ];

    /** @return array<string,string> translated */
    public static function t(array $options): array
    {
        return array_map(fn ($label) => __($label), $options);
    }
}
