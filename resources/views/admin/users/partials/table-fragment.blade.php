<table class="min-w-full text-sm">
    <thead>
        <tr class="border-b text-left">
            <th class="py-2 pr-4">{{ __('Name') }}</th>
            <th class="py-2 pr-4">{{ __('Email') }}</th>
            <th class="py-2 pr-4">{{ __('Phone') }}</th>
            <th class="py-2 pr-4">{{ __('Created') }}</th>
            <th class="py-2 pr-4 text-right">{{ __('Total invested') }}</th>
            <th class="py-2 pr-4 text-right">{{ __('Total profit') }}</th>
            <th class="py-2 pr-4">{{ __('Access') }}</th>
            <th class="py-2 pr-4">{{ __('Account') }}</th>
            <th class="py-2 pr-4">{{ __('Nominee') }}</th>
            <th class="py-2"></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users as $u)
            <tr class="border-b border-gray-100">
                <td class="py-2 pr-4">{{ $u->name }}</td>
                <td class="py-2 pr-4">{{ $u->email }}</td>
                <td class="py-2 pr-4">{{ $u->phone ?? '—' }}</td>
                <td class="py-2 pr-4 tabular-nums">{{ $u->created_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="py-2 pr-4 text-right tabular-nums">{{ number_format((float) ($u->total_invested ?? 0), 2, '.', '') }}</td>
                <td class="py-2 pr-4 text-right tabular-nums">{{ number_format((float) ($u->total_profit ?? 0), 2, '.', '') }}</td>
                <td class="py-2 pr-4">{{ $u->isAdmin() ? __('Admin') : __('Investor') }}</td>
                <td class="py-2 pr-4">
                    @if ($u->is_active)
                        <span class="text-green-700">{{ __('Active') }}</span>
                    @else
                        <span class="text-gray-500">{{ __('Inactive') }}</span>
                    @endif
                </td>
                <td class="py-2 pr-4">{{ $u->nominee?->name ?? '—' }}</td>
                <td class="py-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-action-button :href="route('admin.users.edit', $u)">{{ __('Edit') }}</x-action-button>
                        @if ($u->id !== auth()->id())
                            <form method="post" action="{{ route('admin.users.active', $u) }}" class="inline">
                                @csrf
                                @method('patch')
                                @if ($u->is_active)
                                    <x-action-button variant="secondary">{{ __('Deactivate') }}</x-action-button>
                                @else
                                    <x-action-button>{{ __('Activate') }}</x-action-button>
                                @endif
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="py-4 text-sm text-gray-500">
                    @if (request()->filled('search'))
                        {{ __('No users match your search.') }}
                    @else
                        {{ __('No users yet.') }}
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
    <x-pagination-per-page
        :paginator="$users"
        param="per_page"
        reset-page-key="page"
        :fetch-url="route('admin.users.index')"
        target-id="users-table-fragment"
    />
    <div class="min-w-0 overflow-x-auto">{{ $users->links() }}</div>
</div>
