@if ($users->isEmpty())
    <div class="rounded-xl border border-dashed border-line bg-surface-variant/50 px-3 py-10 text-center">
        <p class="text-sm text-foreground-muted">
            @if (request()->filled('search'))
                {{ __('No users match your search.') }}
            @else
                {{ __('No users yet.') }}
            @endif
        </p>
    </div>
@else
    <div class="ui-glass-table-wrap overflow-x-auto">
        <table class="ui-table min-w-full">
            <thead>
                <tr>
                    <x-table-serial-header />
                    <th>{{ __('User') }}</th>
                    <th class="text-right">{{ __('Total invested') }}</th>
                    <th class="text-right">{{ __('Total profit') }}</th>
                    <th>{{ __('Access') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th class="text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $u)
                    @php
                        $contact = collect([$u->phone, $u->created_at ? __('Joined :date', ['date' => $u->created_at->format('Y-m-d')]) : null])->filter()->implode(' · ');
                    @endphp
                    <tr>
                        <x-table-serial-cell :paginator="$users" :index="$loop->index" />
                        <td class="min-w-[16rem]">
                            <x-user-identity :user="$u" />
                            <div class="mt-1 space-y-0.5 ps-11 text-sm leading-snug text-foreground-muted">
                                <p class="truncate">{{ $u->email }}</p>
                                @if ($contact !== '')
                                    <p class="truncate">{{ $contact }}</p>
                                @endif
                                @if ($u->nominee?->name)
                                    <p class="truncate">{{ __('Nominee') }} · {{ $u->nominee->name }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="text-right tabular-nums font-medium">{{ number_format((float) ($u->total_invested ?? 0), 2, '.', '') }}</td>
                        <td class="text-right tabular-nums font-semibold text-success">{{ number_format((float) ($u->total_profit ?? 0), 2, '.', '') }}</td>
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
                        <td class="text-right">
                            <div class="table-row-actions flex flex-nowrap items-center justify-end gap-2">
                                <x-action-button :href="route('admin.users.edit', $u)" variant="secondary">{{ __('Edit') }}</x-action-button>
                                @if ($u->id !== auth()->id())
                                    <form method="post" action="{{ route('admin.users.active', $u) }}" class="inline-flex">
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
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="ui-table-footer mt-2">
        <x-pagination-per-page
            :paginator="$users"
            param="per_page"
            reset-page-key="page"
            :fetch-url="route('admin.users.index')"
            target-id="users-table-fragment"
        />
        <div class="ui-table-pagination">{{ $users->links() }}</div>
    </div>
@endif
