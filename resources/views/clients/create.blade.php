<x-app-layout :title="__('New client')">
    <x-page-header :title="__('New client')" :subtitle="__('Only the name is required — add the rest whenever you have it.')"/>
    <form method="POST" action="{{ route('clients.store') }}">@include('clients._form')</form>
</x-app-layout>
