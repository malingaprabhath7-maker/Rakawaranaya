<x-app-layout :title="$client->full_name">
    <x-page-header :title="__('Edit client')" :subtitle="$client->full_name"/>
    <form method="POST" action="{{ route('clients.update', $client) }}">@method('PUT') @include('clients._form')</form>
    <form method="POST" action="{{ route('clients.destroy', $client) }}" class="mt-8" x-data @submit="confirm(@js(__('Move this client and their policies to the trash?'))) || $event.preventDefault()">
        @csrf @method('DELETE')
        <button class="btn-ghost text-red-600"><x-icon name="trash" class="size-4"/> {{ __('Delete client') }}</button>
    </form>
</x-app-layout>
