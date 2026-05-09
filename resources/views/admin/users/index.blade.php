<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Users') }}</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">{{ __('Add user') }}</a>
                <a href="{{ route('admin.users.import') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Import users') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto p-2 sm:p-8">
        @if ($errors->has('active'))
            <p class="mb-4 text-sm text-red-600">{{ $errors->first('active') }}</p>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 overflow-x-auto">
                <x-ajax-table-region :fetch-url="route('admin.users.index')" target-id="users-table-fragment">
                    <x-table-search :fetch-url="route('admin.users.index')" target-id="users-table-fragment" :placeholder="__('Search by name, email, phone, or nominee…')" />
                    <div id="users-table-fragment">
                        @include('admin.users.partials.table-fragment', compact('users'))
                    </div>
                </x-ajax-table-region>
            </div>
        </div>
    </div>

</x-app-layout>
