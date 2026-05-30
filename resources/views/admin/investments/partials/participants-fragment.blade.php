<div class="overflow-x-auto">
    <table class="ui-table min-w-full text-sm">
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Tagged date') }}</th>
                <th>{{ __('Contribution') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($participants as $p)
                @php($formId = 'participant-update-'.$p->id)
                <tr>
                    <td class="py-2 pr-4 align-middle">
                        <x-user-identity :user="$p->user" />
                    </td>
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
                            <x-action-button type="submit" :form="$formId">
                                {{ __('Save') }}
                            </x-action-button>
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
                    <td colspan="4" class="py-4 text-sm text-foreground-muted">
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
<div class="ui-table-footer">
    <x-pagination-per-page
        :paginator="$participants"
        param="participants_per_page"
        reset-page-key="participants_page"
        :fetch-url="route('admin.investments.show', $investment)"
        target-id="participants-table-fragment"
        ajax-fragment="participants"
    />
    <div class="ui-table-pagination">{{ $participants->links() }}</div>
</div>
