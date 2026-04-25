<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Investments') }}</h2>
            <a href="{{ route('admin.investments.create') }}">
                <x-primary-button type="button">{{ __('New investment') }}</x-primary-button>
            </a>
        </div>
    </x-slot>


    <div class="mx-auto space-y-3 p-2 sm:p-6">
        @if (session('status'))
            <p class="mb-4 text-sm text-green-600">{{ session('status') }}</p>
        @endif

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Investments list') }}</p>
                <p class="text-xs text-gray-500">{{ __('Compact view with quick actions') }}</p>
            </div>
            <div class="p-3 text-gray-900 sm:p-4">
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
