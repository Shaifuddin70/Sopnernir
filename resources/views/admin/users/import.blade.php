<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import users') }}</h2>
            <x-action-button :href="route('admin.users.index')" variant="secondary"
                class="text-sm">{{ __('Back to list') }}</x-action-button>
        </div>
    </x-slot>

    <div class="mx-auto space-y-6 p-1 sm:p-6">
        @php($importErrors = session('import_errors', []))
        @if (count($importErrors))
            <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-medium">{{ __('Some rows were skipped') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach (array_slice($importErrors, 0, 25) as $err)
                        <li>{{ __('Line :line: :message', ['line' => $err['line'], 'message' => $err['message']]) }}
                        </li>
                    @endforeach
                </ul>
                @if (count($importErrors) > 25)
                    <p class="mt-2 text-xs">{{ __('Showing first 25 errors.') }}</p>
                @endif
            </div>
        @endif

        <div class="bg-white shadow sm:rounded-lg p-4 sm:p-8 space-y-6 max-w-2xl">
            <div class="prose prose-sm max-w-none text-gray-600">
                <p>{{ __('Upload a UTF-8 CSV with a header row. Required columns match the template. Each row creates one user and nominee. Passwords must meet the same rules as registration. Emails and NID numbers must be unique.') }}
                </p>
                <p class="mt-2">
                    <x-action-button :href="route('admin.users.import.template')"
                        class="font-medium">{{ __('Download CSV template') }}</x-action-button>
                </p>
            </div>

            <form method="post" action="{{ route('admin.users.import.store') }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="file" :value="__('CSV file')" />
                    <input id="file" name="file" type="file" accept=".csv,.txt,text/csv"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                        required />
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>
                <x-primary-button type="submit">{{ __('Import') }}</x-primary-button>
            </form>
        </div>
    </div>

</x-app-layout>
