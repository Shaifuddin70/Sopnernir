<div class="overflow-x-auto">
<table class="ui-table min-w-full text-xs sm:text-sm">
    <thead>
        <tr>
            <th class="py-2 pr-3">{{ __('Title') }}</th>
            <th class="py-2 pr-3">{{ __('Status') }}</th>
            <th class="py-2 pr-3">{{ __('Listed') }}</th>
            <th class="py-2 pr-3">{{ __('Plan ends') }}</th>
            <th class="py-2 pr-3">{{ __('Created') }}</th>
            <th class="py-2 pr-3">{{ __('Rate %') }}</th>
            <th class="py-2 pr-3">{{ __('Users') }}</th>
            <th class="py-2 text-right">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($investments as $inv)
            <tr class="align-top">
                <td class="py-2.5 pr-3">
                    <p class="font-medium text-foreground">{{ $inv->title }}</p>
                    @if ($inv->notes)
                        <p class="mt-0.5 line-clamp-1 text-xs text-foreground-muted">{{ $inv->notes }}</p>
                    @endif
                </td>
                <td class="py-2.5 pr-3">
                    @php
                        $statusBadge = match ($inv->status) {
                            \App\Models\Investment::STATUS_ACTIVE => 'ui-badge-success',
                            \App\Models\Investment::STATUS_CLOSED => 'ui-badge-warning',
                            default => 'ui-badge-muted',
                        };
                    @endphp
                    <span class="{{ $statusBadge }}">{{ $inv->status }}</span>
                </td>
                <td class="py-2.5 pr-3">
                    @if ($inv->is_active)
                        <span class="ui-badge-success">{{ __('Active') }}</span>
                    @else
                        <span class="ui-badge-muted">{{ __('Inactive') }}</span>
                    @endif
                </td>
                <td class="py-2.5 pr-3 tabular-nums text-foreground-muted">
                    @if ($inv->deed_completion_deadline)
                        <span @class([
                            'font-medium' => $inv->hasPlanCompleted(),
                            'text-error' => $inv->hasPlanCompleted(),
                        ])>{{ $inv->deed_completion_deadline->format('Y-m-d') }}</span>
                    @else
                        —
                    @endif
                </td>
                <td class="py-2.5 pr-3 tabular-nums text-foreground-muted">{{ $inv->created_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="py-2.5 pr-3 tabular-nums">{{ $inv->default_monthly_rate_pct }}</td>
                <td class="py-2.5 pr-3 tabular-nums">{{ $inv->participants_count }}</td>
                <td class="py-2.5 text-right">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-action-button :href="route('admin.investments.show', $inv)">{{ __('Manage') }}</x-action-button>
                        <x-action-button :href="route('admin.investments.edit', $inv)" variant="secondary">{{ __('Edit') }}</x-action-button>
                        <form method="post" action="{{ route('admin.investments.active', $inv) }}" class="inline">
                            @csrf
                            @method('patch')
                            @if ($inv->is_active)
                                <x-action-button variant="secondary">{{ __('Deactivate') }}</x-action-button>
                            @else
                                <x-action-button>{{ __('Activate') }}</x-action-button>
                            @endif
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="py-4 text-sm text-foreground-muted">
                    @if (request()->filled('search'))
                        {{ __('No investments match your search.') }}
                    @else
                        {{ __('No investments yet.') }}
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>
<div class="ui-table-footer">
    <x-pagination-per-page
        :paginator="$investments"
        param="per_page"
        reset-page-key="page"
        :fetch-url="route('admin.investments.index')"
        target-id="investments-table-fragment"
    />
    <div class="ui-table-pagination">{{ $investments->links() }}</div>
</div>
