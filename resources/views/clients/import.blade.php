<x-app-layout :title="__('Import clients')">
    <x-page-header :title="__('Import clients')" :subtitle="__('Bring your existing list from Excel or Google Sheets.')"/>
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card card-pad">
            <ol class="space-y-4 text-sm">
                <li><b>1.</b> {{ __('Download the template and fill in your clients (one per row).') }}
                    <a href="{{ route('clients.import.template') }}" class="btn-secondary btn-sm mt-2"><x-icon name="download" class="size-4"/> {{ __('Download template') }}</a></li>
                <li><b>2.</b> {{ __('In Excel choose File → Save as → "CSV UTF-8". Sinhala names are kept.') }}</li>
                <li><b>3.</b> {{ __('Upload the file. Rows without a name or with an invalid NIC are skipped.') }}</li>
            </ol>
            <p class="mt-5 text-xs text-muted">{{ __('Columns:') }} <code class="rounded bg-surface-2 px-1">{{ implode(', ', $columns) }}</code></p>
        </section>
        <form method="POST" action="{{ route('clients.import.store') }}" enctype="multipart/form-data" class="card card-pad space-y-4">
            @csrf
            <label class="label" for="file">{{ __('CSV file') }}</label>
            <input id="file" type="file" name="file" accept=".csv,text/csv" required class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:font-semibold file:text-brand-700">
            @error('file')<p class="error">{{ $message }}</p>@enderror
            <button class="btn-primary w-full"><x-icon name="upload" class="size-4"/> {{ __('Import') }}</button>
            <p class="hint">{{ __('Up to 2,000 rows at a time.') }}</p>
        </form>
    </div>
</x-app-layout>
