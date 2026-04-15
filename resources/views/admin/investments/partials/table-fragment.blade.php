<table class="min-w-full text-sm">
    <thead>
        <tr class="border-b text-left">
            <th class="py-2 pr-4">{{ __('Title') }}</th>
            <th class="py-2 pr-4">{{ __('Status') }}</th>
            <th class="py-2 pr-4">{{ __('Listed') }}</th>
            <th class="py-2 pr-4">{{ __('Created') }}</th>
            <th class="py-2 pr-4">{{ __('Default rate %') }}</th>
            <th class="py-2 pr-4">{{ __('Participants') }}</th>
            <th class="py-2"></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($investments as $inv)
            <tr class="border-b border-gray-100">
                <td class="py-2 pr-4">{{ $inv->title }}</td>
                <td class="py-2 pr-4">{{ $inv->status }}</td>
                <td class="py-2 pr-4">
                    @if ($inv->is_active)
                        <span class="text-green-700">{{ __('Active') }}</span>
                    @else
                        <span class="text-gray-500">{{ __('Inactive') }}</span>
                    @endif
                </td>
                <td class="py-2 pr-4 tabular-nums">{{ $inv->created_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="py-2 pr-4">{{ $inv->default_monthly_rate_pct }}</td>
                <td class="py-2 pr-4">{{ $inv->participants_count }}</td>
                <td class="py-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-action-button :href="route('admin.investments.show', $inv)">{{ __('Manage') }}</x-action-button>
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
                <td colspan="7" class="py-4 text-sm text-gray-500">
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
<div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
    <x-pagination-per-page
        :paginator="$investments"
        param="per_page"
        reset-page-key="page"
        :fetch-url="route('admin.investments.index')"
        target-id="investments-table-fragment"
    />
    <div class="min-w-0 overflow-x-auto">{{ $investments->links() }}</div>
</div>
