<x-app-layout :title="__('Edit policy')">
    <x-page-header :title="__('Edit policy')" :subtitle="$policy->label"/>
    <form method="POST" action="{{ route('policies.update', $policy) }}">@method('PUT') @include('policies._form')</form>
    <form method="POST" action="{{ route('policies.destroy', $policy) }}" class="mt-8" x-data @submit="confirm(@js(__('Delete this policy and its payment history?'))) || $event.preventDefault()">
        @csrf @method('DELETE')
        <button class="btn-ghost text-red-600"><x-icon name="trash" class="size-4"/> {{ __('Delete policy') }}</button>
    </form>
</x-app-layout>
