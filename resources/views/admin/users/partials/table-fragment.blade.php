<div class="overflow-x-auto">
<table class="ui-table min-w-full">
    <thead>
        <tr>
            <x-table-serial-header />
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Created') }}</th>
            <th class="text-right">{{ __('Total invested') }}</th>
            <th class="text-right">{{ __('Total profit') }}</th>
            <th>{{ __('Access') }}</th>
            <th>{{ __('Account') }}</th>
            <th>{{ __('Nominee') }}</th>
            <th class="text-right">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users as $u)
            <tr>
                <x-table-serial-cell :paginator="$users" :index="$loop->index" />
                <td>
                    <x-user-identity :user="$u" />
                </td>
                <td class="text-foreground-muted">{{ $u->email }}</td>
                <td class="text-foreground-muted">{{ $u->phone ?? '—' }}</td>
                <td class="tabular-nums text-foreground-muted">{{ $u->created_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="text-right tabular-nums">{{ number_format((float) ($u->total_invested ?? 0), 2, '.', '') }}</td>
                <td class="text-right tabular-nums text-success">{{ number_format((float) ($u->total_profit ?? 0), 2, '.', '') }}</td>
                <td>
                    @if ($u->isAdmin())
                        <span class="ui-badge-brand">{{ __('Admin') }}</span>
                    @else
                        <span class="ui-badge-muted">{{ __('Investor') }}</span>
                    @endif
                </td>
                <td>
                    @if ($u->is_active)
                        <span class="ui-badge-success">{{ __('Active') }}</span>
                    @else
                        <span class="ui-badge-muted">{{ __('Inactive') }}</span>
                    @endif
                </td>
                <td class="text-foreground-muted">{{ $u->nominee?->name ?? '—' }}</td>
                <td class="text-right">
                    <div class="flex flex-wrap items-center justify-end gap-2">
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
                <td colspan="11" class="py-4 text-sm text-foreground-muted">
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
</div>
<div class="ui-table-footer">
    <x-pagination-per-page
        :paginator="$users"
        param="per_page"
        reset-page-key="page"
        :fetch-url="route('admin.users.index')"
        target-id="users-table-fragment"
    />
    <div class="ui-table-pagination">{{ $users->links() }}</div>
</div>
