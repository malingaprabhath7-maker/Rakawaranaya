<x-app-layout :title="__('New policy')">
    <x-page-header :title="__('New policy')"/>
    @if ($clients->isEmpty())
        <div class="card"><x-empty icon="users" :title="__('Add a client first')" :text="__('Every policy belongs to a client.')"><a href="{{ route('clients.create') }}" class="btn-primary btn-sm mt-2">{{ __('New client') }}</a></x-empty></div>
    @else
        <form method="POST" action="{{ route('policies.store') }}">@include('policies._form')</form>
    @endif
</x-app-layout>
