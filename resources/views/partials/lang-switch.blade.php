<div class="inline-flex rounded-full border border-line bg-surface p-0.5 text-xs font-bold" role="group" aria-label="{{ __('Language') }}">
    <a href="{{ route('locale', 'si') }}" class="rounded-full px-2.5 py-1 {{ app()->getLocale() === 'si' ? 'bg-ink text-page' : 'text-muted hover:text-ink' }}">සිං</a>
    <a href="{{ route('locale', 'en') }}" class="rounded-full px-2.5 py-1 {{ app()->getLocale() === 'en' ? 'bg-ink text-page' : 'text-muted hover:text-ink' }}">EN</a>
</div>
