@if ($rows->isEmpty())
    <div class="rounded-xl border border-dashed border-line bg-surface-variant px-4 py-10 text-center">
        <p class="text-sm text-foreground-muted">
            @if (request()->filled('search'))
                {{ __('No rows match your search.') }}
            @else
                {{ __('No active users found.') }}
            @endif
        </p>
    </div>
@else
    @php
        $allPaid = $rows->every(fn (array $row) => $row['is_paid']);
        $somePaid = $rows->contains(fn (array $row) => $row['is_paid']);
        $paidLabel = __('Paid');
        $unpaidLabel = __('Unpaid');
        $markPaidLabel = __('Mark paid');
        $markUnpaidLabel = __('Mark unpaid');
    @endphp

    <div
        class="space-y-0"
        data-monthly-payments-checklist
        data-update-url="{{ route('admin.monthly-payments.update') }}"
        data-bulk-url="{{ route('admin.monthly-payments.bulk-update') }}"
        data-index-url="{{ route('admin.monthly-payments.index') }}"
        data-month="{{ $month->format('Y-m') }}"
        data-label-paid="{{ $paidLabel }}"
        data-label-unpaid="{{ $unpaidLabel }}"
        data-label-mark-paid="{{ $markPaidLabel }}"
        data-label-mark-unpaid="{{ $markUnpaidLabel }}"
    >
        <div class="ui-glass-table-wrap overflow-x-auto">
            <table class="ui-table min-w-full">
                <thead>
                    <tr>
                        <x-table-serial-header />
                        <th>{{ __('Investor') }}</th>
                        <th class="hidden sm:table-cell">{{ __('Phone') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="hidden md:table-cell">{{ __('Paid at') }}</th>
                        <th class="text-center">
                            <div class="inline-flex items-center justify-center gap-2">
                                <span>{{ __('Paid') }}</span>
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-line text-primary focus:ring-primary"
                                    data-check-all-paid
                                    @checked($allPaid)
                                    @if ($somePaid && ! $allPaid) data-indeterminate="true" @endif
                                    aria-label="{{ __('Check all') }}"
                                    title="{{ __('Check all') }}"
                                >
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr
                            data-payment-row
                            data-user-id="{{ $row['user_id'] }}"
                            data-paid="{{ $row['is_paid'] ? '1' : '0' }}"
                            @class(['bg-success-muted/30' => $row['is_paid']])
                        >
                            <x-table-serial-cell :index="$loop->index" />
                            <td>
                                <div class="font-medium text-foreground">{{ $row['name'] }}</div>
                                <div class="text-sm text-foreground-muted sm:hidden">{{ $row['email'] }}</div>
                            </td>
                            <td class="hidden tabular-nums text-foreground-muted sm:table-cell">{{ $row['phone'] ?? '—' }}</td>
                            <td data-status-cell>
                                @if ($row['is_paid'])
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-success/25 bg-success-muted px-3 py-1 text-sm font-semibold text-success">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success" aria-hidden="true"></span>
                                        {{ $paidLabel }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-warning/25 bg-warning-muted px-3 py-1 text-sm font-semibold text-warning">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning" aria-hidden="true"></span>
                                        {{ $unpaidLabel }}
                                    </span>
                                @endif
                            </td>
                            <td class="hidden tabular-nums text-foreground-muted md:table-cell" data-paid-at-cell>
                                {{ $row['paid_at'] ?? '—' }}
                            </td>
                            <td class="text-center">
                                <label class="inline-flex cursor-pointer items-center justify-center">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-line text-primary focus:ring-primary"
                                        data-paid-toggle
                                        value="{{ $row['user_id'] }}"
                                        @checked($row['is_paid'])
                                    >
                                    <span class="sr-only" data-toggle-label>
                                        {{ $row['is_paid'] ? $markUnpaidLabel : $markPaidLabel }}
                                    </span>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if (request()->filled('search') && ($summaryTotal ?? 0) > 0)
    <p class="mt-4 text-sm text-foreground-muted">
        {{ __('Showing :count of :total investors', ['count' => $filteredCount ?? $rows->count(), 'total' => $summaryTotal]) }}
    </p>
@endif
