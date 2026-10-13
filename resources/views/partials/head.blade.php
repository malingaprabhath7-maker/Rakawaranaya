<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#0f6b60">
<meta name="description" content="{{ __('Rakawarana — a bilingual CRM for insurance advisors: clients, policies, premiums, renewals and reminders in one place.') }}">
<title>{{ isset($title) ? $title.' · ' : '' }}{{ __('Rakawarana') }}</title>
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|noto-sans-sinhala:400,500,600,700&display=swap">
{{-- set the theme before paint to avoid a flash --}}
<script src="{{ asset('theme-init.js') }}"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
