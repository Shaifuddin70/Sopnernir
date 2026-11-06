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
        $mixedPaid = $somePaid && ! $allPaid;
    @endphp

    <div
        class="space-y-0"
        data-monthly-payments-checklist
        data-update-url="{{ route('admin.monthly-payments.update') }}"
        data-bulk-url="{{ route('admin.monthly-payments.bulk-update') }}"
        data-index-url="{{ route('admin.monthly-payments.index') }}"
        data-month="{{ $month->format('Y-m') }}"
        data-label-paid="{{ __('Paid') }}"
        data-label-unpaid="{{ __('Unpaid') }}"
    >
        <div class="ui-glass-table-wrap overflow-x-auto">
            <table class="ui-table min-w-full">
                <thead>
                    <tr>
                        <x-table-serial-header />
                        <th>{{ __('Investor') }}</th>
                        <th class="hidden sm:table-cell">{{ __('Phone') }}</th>
                        <th class="hidden md:table-cell">{{ __('Paid at') }}</th>
                        <th class="text-center">
                            <div class="flex justify-center">
                                <x-paid-unpaid-toggle :paid="$allPaid" :mixed="$mixedPaid" bulk />
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
                            <td class="hidden tabular-nums text-foreground-muted md:table-cell" data-paid-at-cell>
                                {{ $row['paid_at'] ?? '—' }}
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center">
                                    <x-paid-unpaid-toggle :paid="$row['is_paid']" />
                                </div>
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
