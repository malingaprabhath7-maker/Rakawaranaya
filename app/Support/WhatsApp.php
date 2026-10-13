<?php

namespace App\Support;

use App\Models\Client;
use App\Models\InsurancePolicy;
use App\Models\User;

/**
 * Click-to-chat links with ready-made, bilingual messages. Nothing is sent by the app itself:
 * the advisor's own WhatsApp opens with the text filled in.
 */
final class WhatsApp
{
    public const DEFAULT_TEMPLATES = [
        'premium_due' => [
            'si' => "ආයුබෝවන් {name}, ඔබගේ {insurer} ප්‍රතිපත්තියේ ({policy}) {amount} වාරිකය {date} දිනට ගෙවිය යුතුයි. ඕනෑම උදව්වක් සඳහා මා අමතන්න. - {advisor}",
            'en' => "Hello {name}, a gentle reminder that your {insurer} policy ({policy}) premium of {amount} is due on {date}. Happy to help with anything. - {advisor}",
        ],
        'renewal' => [
            'si' => "ආයුබෝවන් {name}, ඔබගේ {insurer} {type} ප්‍රතිපත්තිය ({policy}) {date} දින අලුත් කළ යුතුයි. අලුත් කිරීම සඳහා මා අමතන්න. - {advisor}",
            'en' => "Hello {name}, your {insurer} {type} policy ({policy}) is due for renewal on {date}. Let me know when suits you to renew. - {advisor}",
        ],
        'birthday' => [
            'si' => "සුබ උපන්දිනයක් වේවා {name}! 🎉 ඔබට නිරෝගී, සතුටින් පිරි වසරක් ප්‍රාර්ථනා කරනවා. - {advisor}",
            'en' => "Happy birthday {name}! 🎉 Wishing you a healthy and happy year ahead. - {advisor}",
        ],
        'thank_you' => [
            'si' => "ස්තූතියි {name}! {insurer} ප්‍රතිපත්තියට ({policy}) ඔබගේ {amount} ගෙවීම ලැබුණා. - {advisor}",
            'en' => "Thank you {name}! We've received your payment of {amount} for your {insurer} policy ({policy}). - {advisor}",
        ],
        'follow_up' => [
            'si' => "ආයුබෝවන් {name}, අපි කතා කළ රක්ෂණ සැලැස්ම ගැන තවත් තොරතුරු ඕනෑද? ඔබට ගැළපෙන වෙලාවක කතා කරමු. - {advisor}",
            'en' => "Hello {name}, would you like more details about the insurance plan we discussed? Happy to chat at a time that suits you. - {advisor}",
        ],
    ];

    public static function template(User $user, string $key, ?string $lang = null): string
    {
        $lang = $lang ?: ($user->locale === 'en' ? 'en' : 'si');
        $custom = data_get($user->settings, "templates.$key.$lang");

        return $custom ?: self::DEFAULT_TEMPLATES[$key][$lang];
    }

    public static function link(User $user, ?Client $client, string $key, ?InsurancePolicy $policy = null, array $extra = []): ?string
    {
        $number = Phone::international($client?->whatsapp ?: $client?->phone);
        if (! $number) {
            return null;
        }
        $text = strtr(self::template($user, $key), array_merge([
            '{name}' => $client->first_name,
            '{advisor}' => $user->name,
            '{insurer}' => $policy?->insurer ?? '',
            '{policy}' => $policy?->policy_number ?: ($policy?->plan_name ?? ''),
            '{type}' => $policy ? __(Options::POLICY_TYPES[$policy->type] ?? $policy->type) : '',
            '{amount}' => $policy ? Money::format($policy->premium) : '',
            '{date}' => $policy?->next_due_date?->format('Y-m-d') ?? '',
        ], $extra));

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }
}
