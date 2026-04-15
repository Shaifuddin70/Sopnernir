<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b text-left">
                <th class="py-2 pr-4">{{ __('Name') }}</th>
                <th class="py-2 pr-4">{{ __('Tagged date') }}</th>
                <th class="py-2 pr-4">{{ __('Contribution') }}</th>
                <th class="py-2">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($participants as $p)
                @php($formId = 'participant-update-'.$p->id)
                <tr class="border-b border-gray-100">
                    <td class="py-2 pr-4 align-middle font-medium text-gray-900">{{ $p->user->name }}</td>
                    <td class="py-2 pr-4 align-middle tabular-nums">{{ $p->created_at?->format('Y-m-d') ?? '—' }}</td>
                    <td class="py-2 pr-4 align-middle">
                        <x-text-input
                            :form="$formId"
                            name="contribution_amount"
                            type="text"
                            class="block w-full min-w-[6.5rem]"
                            :value="$p->contribution_amount"
                            required
                        />
                    </td>
                    <td class="py-2 align-middle">
                        <form id="{{ $formId }}" method="post" action="{{ route('admin.investments.participants.update', [$investment, $p]) }}" class="inline">
                            @csrf
                            @method('patch')
                        </form>
                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                type="submit"
                                form="{{ $formId }}"
                                class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Save') }}
                            </button>
                            <form method="post" action="{{ route('admin.investments.participants.destroy', [$investment, $p]) }}" class="inline" onsubmit="return confirm('{{ __('Remove participant?') }}');">
                                @csrf
                                @method('delete')
                                <x-action-button variant="danger">{{ __('Remove') }}</x-action-button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-4 text-sm text-gray-500">
                        @if (request()->filled('participants_search'))
                            {{ __('No participants match your search.') }}
                        @else
                            {{ __('No participants yet.') }}
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
    <x-pagination-per-page
        :paginator="$participants"
        param="participants_per_page"
        reset-page-key="participants_page"
        :fetch-url="route('admin.investments.show', $investment)"
        target-id="participants-table-fragment"
        ajax-fragment="participants"
    />
    <div class="min-w-0 overflow-x-auto">{{ $participants->links() }}</div>
</div>
