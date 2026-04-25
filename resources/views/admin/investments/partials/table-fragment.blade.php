<div class="overflow-x-auto">
<table class="min-w-full text-xs sm:text-sm">
    <thead>
        <tr class="border-b border-gray-200 bg-gray-50 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs">
            <th class="py-2 pr-3">{{ __('Title') }}</th>
            <th class="py-2 pr-3">{{ __('Status') }}</th>
            <th class="py-2 pr-3">{{ __('Listed') }}</th>
            <th class="py-2 pr-3">{{ __('Created') }}</th>
            <th class="py-2 pr-3">{{ __('Rate %') }}</th>
            <th class="py-2 pr-3">{{ __('Users') }}</th>
            <th class="py-2 text-right">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($investments as $inv)
            <tr class="border-b border-gray-100 align-top">
                <td class="py-2.5 pr-3">
                    <p class="font-medium text-gray-900">{{ $inv->title }}</p>
                    @if ($inv->notes)
                        <p class="mt-0.5 line-clamp-1 text-xs text-gray-500">{{ $inv->notes }}</p>
                    @endif
                </td>
                <td class="py-2.5 pr-3">
                    <span class="inline-flex rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $inv->status }}</span>
                </td>
                <td class="py-2.5 pr-3">
                    @if ($inv->is_active)
                        <span class="inline-flex rounded-md bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">{{ __('Active') }}</span>
                    @else
                        <span class="inline-flex rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">{{ __('Inactive') }}</span>
                    @endif
                </td>
                <td class="py-2.5 pr-3 tabular-nums text-gray-600">{{ $inv->created_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="py-2.5 pr-3 tabular-nums">{{ $inv->default_monthly_rate_pct }}</td>
                <td class="py-2.5 pr-3 tabular-nums">{{ $inv->participants_count }}</td>
                <td class="py-2.5 text-right">
                    <div class="flex flex-wrap items-center justify-end gap-2">
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
</div>
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
