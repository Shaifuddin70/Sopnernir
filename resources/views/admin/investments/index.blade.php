<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Investments') }}</h2>
            <a href="{{ route('admin.investments.create') }}">
                <x-primary-button type="button">{{ __('New investment') }}</x-primary-button>
            </a>
        </div>
    </x-slot>


    <div class="mx-auto p-2 sm:p-8">
        @if (session('status'))
            <p class="mb-4 text-sm text-green-600">{{ session('status') }}</p>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 overflow-x-auto">
                <x-ajax-table-region :fetch-url="route('admin.investments.index')" target-id="investments-table-fragment">
                    <x-table-search :fetch-url="route('admin.investments.index')" target-id="investments-table-fragment" :placeholder="__('Search by title, notes, or status…')" />
                    <div id="investments-table-fragment">
                        @include('admin.investments.partials.table-fragment', compact('investments'))
                    </div>
                </x-ajax-table-region>
            </div>
        </div>
    </div>

</x-app-layout>
